#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Airtrendmedia — Shared Hosting Helper (Python 3)

A small, dependency-free Python helper designed to run on shared hosting
where SSH access may be limited. It improves the user/ops experience by:

  1. Clearing Laravel caches (config, route, view, event, compiled).
  2. Re-linking the public/storage symlink (for uploaded media).
  3. Setting safe directory permissions (755 dirs, 644 files,
     775 storage & bootstrap/cache).
  4. Running database migrations (uses artisan, non-interactive).
  5. Seeding demo data (optional).
  6. Running a lightweight HTTP health check of public routes.
  7. Generating a deployment report.

USAGE
-----
From a terminal / cron / cPanel "Run Python Script":
    python3 airtrendmedia_helper.py --action=all
    python3 airtrendmedia_helper.py --action=clear-cache
    python3 airtrendmedia_helper.py --action=permissions
    python3 airtrendmedia_helper.py --action=migrate
    python3 airtrendmedia_helper.py --action=health
    python3 airtrendmedia_helper.py --action=report

It auto-detects the Laravel project root (the directory containing
`artisan`).  It works with PHP 8.3 through the latest version.

Author: Airtrendmedia
License: Proprietary
"""

import argparse
import json
import os
import shutil
import socket
import subprocess
import sys
import time
from http.client import HTTPConnection
from pathlib import Path
from urllib.parse import urlparse

# ---------------------------------------------------------------------------
# Configuration
# ---------------------------------------------------------------------------

DEFAULT_TIMEOUT = 30  # seconds for subprocess calls
HEALTH_ROUTES = [
    "/",
    "/login",
    "/register",
    "/blog",
    "/ptc",
    "/marketplace",
    "/gigs",
    "/faqs",
    "/contact",
]

# Directories that must be writable by the web server
WRITABLE_DIRS = [
    "storage",
    "storage/app",
    "storage/app/public",
    "storage/framework",
    "storage/framework/cache",
    "storage/framework/cache/data",
    "storage/framework/sessions",
    "storage/framework/views",
    "storage/logs",
    "bootstrap/cache",
]

REQUIRED_PHP = "8.3.0"


# ---------------------------------------------------------------------------
# Helpers
# ---------------------------------------------------------------------------

def find_project_root(start: Path = None) -> Path:
    """Walk upward from `start` until a directory containing `artisan` is found."""
    start = start or Path(__file__).resolve().parent
    for candidate in [start] + list(start.parents):
        if (candidate / "artisan").is_file():
            return candidate
    return start


def run(cmd, cwd=None, timeout=DEFAULT_TIMEOUT, check=False):
    """Run a shell command and return (returncode, stdout, stderr)."""
    try:
        p = subprocess.run(
            cmd,
            cwd=str(cwd) if cwd else None,
            shell=True,
            capture_output=True,
            text=True,
            timeout=timeout,
        )
        if check and p.returncode != 0:
            raise RuntimeError(
                f"Command failed ({cmd}): {p.stderr.strip() or p.stdout.strip()}"
            )
        return p.returncode, p.stdout.strip(), p.stderr.strip()
    except subprocess.TimeoutExpired:
        return 124, "", f"Command timed out after {timeout}s: {cmd}"
    except FileNotFoundError as exc:
        return 127, "", str(exc)


def php_bin() -> str:
    """Locate a PHP binary, preferring one that meets the version requirement."""
    candidates = os.environ.get("AIRTRENDMEDIA_PHP", "").split(os.pathsep)
    candidates = [c for c in candidates if c] + ["php", "php83", "php84", "php85", "php8.3", "php8.4", "php8.5"]
    for c in candidates:
        rc, out, _ = run(f'"{c}" -v', timeout=10)
        if rc != 0 or not out:
            continue
        first = out.splitlines()[0]
        # "PHP 8.4.3 (cli) ..."
        ver = first.split()[1] if len(first.split()) > 1 else "0.0.0"
        if _ver_gte(ver, REQUIRED_PHP):
            return c
    # Fallback to plain php even if version is borderline
    rc, _, _ = run("php -v", timeout=10)
    if rc == 0:
        return "php"
    return None


def _ver_gte(actual: str, required: str) -> bool:
    try:
        a = tuple(int(x) for x in actual.split(".")[:3])
        r = tuple(int(x) for x in required.split(".")[:3])
        while len(a) < 3:
            a = a + (0,)
        while len(r) < 3:
            r = r + (0,)
        return a >= r
    except Exception:
        return False


def artisan(args: str, root: Path, timeout=120) -> tuple:
    """Run a php artisan command."""
    php = php_bin()
    if not php:
        return 1, "", "No suitable PHP binary found (need PHP >= %s)." % REQUIRED_PHP
    cmd = f'"{php}" artisan {args}'
    return run(cmd, cwd=root, timeout=timeout)


def info(msg: str):
    print("[info] " + msg)


def ok(msg: str):
    print("[  ok] " + msg)


def warn(msg: str):
    print("[warn] " + msg)


def err(msg: str):
    print("[ err] " + msg, file=sys.stderr)


# ---------------------------------------------------------------------------
# Actions
# ---------------------------------------------------------------------------

def action_clear_cache(root: Path) -> bool:
    info("Clearing Laravel caches ...")
    all_ok = True
    for sub in ["cache:clear", "route:clear", "view:clear", "config:clear", "event:clear", "optimize:clear"]:
        rc, out, e = artisan(sub, root, timeout=60)
        if rc == 0:
            ok(sub)
        else:
            warn(f"{sub} -> {e or out or 'failed'}")
            all_ok = False
    # Remove compiled files manually as a safety net
    for f in (root / "bootstrap" / "cache").glob("*.php"):
        try:
            f.unlink()
            ok(f"removed compiled {f.name}")
        except Exception as exc:
            warn(f"could not remove {f.name}: {exc}")
    return all_ok


def action_symlink(root: Path) -> bool:
    info("Ensuring public/storage symlink ...")
    target = root / "storage" / "app" / "public"
    link = root / "public" / "storage"
    if not target.exists():
        warn(f"storage target dir missing: {target}")
        os.makedirs(target, exist_ok=True)
    try:
        if link.is_symlink() or link.exists():
            if link.is_symlink():
                link.unlink()
            elif link.is_dir():
                shutil.rmtree(link)
            else:
                link.unlink()
        os.symlink(str(target), str(link))
        ok(f"symlink {link} -> {target}")
        return True
    except Exception as exc:
        warn(f"symlink failed ({exc}); copying as fallback")
        try:
            shutil.copytree(target, link, dirs_exist_ok=True)
            ok("copied storage/app/public -> public/storage as fallback")
            return True
        except Exception as exc2:
            err(f"fallback copy also failed: {exc2}")
            return False


def action_permissions(root: Path) -> bool:
    info("Setting safe directory permissions ...")
    all_ok = True
    # Directories: 755 (775 for writable)
    for dirpath, dirnames, filenames in os.walk(root):
        # Skip vendor and node_modules and .git for speed
        dirnames[:] = [d for d in dirnames if d not in (".git", "node_modules", "vendor")]
        for d in dirnames:
            full = Path(dirpath) / d
            try:
                os.chmod(full, 0o755)
            except Exception:
                pass
    # Writable dirs get 775
    for w in WRITABLE_DIRS:
        wd = root / w
        if wd.exists():
            try:
                _chmod_recursive(wd, 0o775, 0o664)
                ok(f"writable {w}")
            except Exception as exc:
                warn(f"chmod {w}: {exc}")
                all_ok = False
        else:
            try:
                os.makedirs(wd, exist_ok=True)
                _chmod_recursive(wd, 0o775, 0o664)
                ok(f"created+writable {w}")
            except Exception as exc:
                warn(f"mkdir {w}: {exc}")
                all_ok = False
    return all_ok


def _chmod_recursive(path: Path, dir_mode: int, file_mode: int):
    os.chmod(path, dir_mode)
    for dirpath, dirnames, filenames in os.walk(path):
        for d in dirnames:
            os.chmod(Path(dirpath) / d, dir_mode)
        for f in filenames:
            os.chmod(Path(dirpath) / f, file_mode)


def action_migrate(root: Path, seed: bool = False) -> bool:
    info("Running migrations ...")
    rc, out, e = artisan("migrate --force", root, timeout=300)
    if rc == 0:
        ok("migrate --force")
        if out:
            for line in out.splitlines()[:6]:
                print("       " + line)
    else:
        err(f"migrate failed: {e or out}")
        return False
    if seed:
        info("Seeding database ...")
        rc, out, e = artisan("db:seed --force", root, timeout=300)
        if rc == 0:
            ok("db:seed --force")
        else:
            warn(f"seed failed: {e or out}")
            return False
    return True


def action_optimize(root: Path) -> bool:
    info("Optimizing for production ...")
    rc, out, e = artisan("config:cache", root, timeout=60)
    if rc == 0:
        ok("config:cache")
    else:
        warn(f"config:cache -> {e or out}")
    rc, out, e = artisan("route:cache", root, timeout=60)
    if rc == 0:
        ok("route:cache")
    else:
        warn(f"route:cache -> {e or out}")
    rc, out, e = artisan("view:cache", root, timeout=120)
    if rc == 0:
        ok("view:cache")
    else:
        warn(f"view:cache -> {e or out}")
    return True


def action_health(root: Path, base_url: str = None) -> dict:
    """Probe a configured base URL; if none, probe localhost on common ports."""
    info("Running health checks ...")
    if not base_url:
        base_url = os.environ.get("APP_URL", "").rstrip("/")
    results = {}
    targets = []
    if base_url:
        targets.append(base_url)
    else:
        # Try to read APP_URL from .env
        env_file = root / ".env"
        if env_file.exists():
            for line in env_file.read_text(errors="ignore").splitlines():
                if line.startswith("APP_URL="):
                    base_url = line.split("=", 1)[1].strip().strip('"').rstrip("/")
                    targets.append(base_url)
                    break
        if not targets:
            for port in [80, 8000, 8080]:
                targets.append(f"http://127.0.0.1:{port}")
    overall = True
    for target in targets:
        host_port = urlparse(target)
        host = host_port.hostname or "127.0.0.1"
        port = host_port.port or (443 if host_port.scheme == "https" else 80)
        per_target = {}
        try:
            # quick connectivity check
            with socket.create_connection((host, port), timeout=5):
                pass
        except Exception as exc:
            warn(f"cannot connect to {host}:{port} ({exc}); skipping HTTP probes")
            results[target] = {"reachable": False, "error": str(exc)}
            overall = False
            continue
        reachable_routes = 0
        for route in HEALTH_ROUTES:
            code = _http_status(host, port, route, host_port.scheme == "https", host_port.path.rstrip("/"))
            status = "PASS" if code and code < 500 else "FAIL"
            if status == "FAIL":
                overall = False
            else:
                reachable_routes += 1
            per_target[route] = {"status_code": code, "result": status}
            print(f"       {route:20s} {code} {status}")
        per_target["reachable"] = True
        per_target["routes_ok"] = f"{reachable_routes}/{len(HEALTH_ROUTES)}"
        results[target] = per_target
    return {"ok": overall, "targets": results}


def _http_status(host: str, port: int, path: str, use_tls: bool, prefix: str = "") -> int:
    try:
        conn = HTTPConnection(host, port, timeout=10)
        full_path = (prefix + path) if prefix else path
        conn.request("GET", full_path, headers={"Host": host, "User-Agent": "Airtrendmedia-Helper/1.0"})
        resp = conn.getresponse()
        code = resp.status
        resp.read()
        conn.close()
        return code
    except Exception:
        return 0


def action_report(root: Path) -> dict:
    info("Generating deployment report ...")
    report = {
        "generated_at": time.strftime("%Y-%m-%d %H:%M:%S"),
        "project_root": str(root),
    }
    php = php_bin()
    if php:
        rc, out, _ = run(f'"{php}" -v', timeout=10)
        report["php_binary"] = php
        report["php_version"] = out.splitlines()[0] if out else "unknown"
        report["php_ok"] = _ver_gte(
            (out.split()[1] if out and len(out.split()) > 1 else "0.0.0"), REQUIRED_PHP
        )
    else:
        report["php_binary"] = None
        report["php_ok"] = False

    report["artisan_exists"] = (root / "artisan").is_file()
    report["env_exists"] = (root / ".env").is_file()
    report["vendor_exists"] = (root / "vendor").is_dir()
    report["storage_symlink"] = (root / "public" / "storage").is_symlink() or (root / "public" / "storage").exists()

    # writable check
    writable = {}
    for w in WRITABLE_DIRS:
        writable[w] = os.access(root / w, os.W_OK)
    report["writable"] = writable

    # migration status
    rc, out, e = artisan("migrate:status", root, timeout=60)
    report["migrate_status_ok"] = rc == 0
    if rc == 0 and out:
        pending = [l for l in out.splitlines() if "Pending" in l or "| N " in l]
        report["pending_migrations"] = len(pending)
    else:
        report["pending_migrations"] = None

    return report


# ---------------------------------------------------------------------------
# Main
# ---------------------------------------------------------------------------

def main():
    parser = argparse.ArgumentParser(
        description="Airtrendmedia shared-hosting helper (PHP 8.3+)."
    )
    parser.add_argument(
        "--action",
        default="all",
        choices=[
            "all",
            "clear-cache",
            "symlink",
            "permissions",
            "migrate",
            "migrate-seed",
            "optimize",
            "health",
            "report",
        ],
        help="Action to perform (default: all).",
    )
    parser.add_argument(
        "--root",
        default=None,
        help="Laravel project root (auto-detected if omitted).",
    )
    parser.add_argument(
        "--base-url",
        default=None,
        help="Base URL for health checks (defaults to APP_URL).",
    )
    args = parser.parse_args()

    root = Path(args.root).resolve() if args.root else find_project_root()
    if not (root / "artisan").is_file():
        err(f"Could not locate Laravel project (artisan missing) at {root}")
        sys.exit(2)

    info(f"Project root: {root}")
    php = php_bin()
    if php:
        ok(f"PHP binary: {php}")
    else:
        warn("No PHP binary detected; PHP-only actions will be skipped.")

    success = True

    if args.action in ("all", "clear-cache"):
        success &= action_clear_cache(root)

    if args.action in ("all", "symlink"):
        success &= action_symlink(root)

    if args.action in ("all", "permissions"):
        success &= action_permissions(root)

    if args.action in ("all", "migrate", "migrate-seed"):
        success &= action_migrate(root, seed=(args.action == "migrate-seed"))

    if args.action in ("all", "optimize"):
        if args.action == "all":
            info("Skipping optimize (run manually on production: --action=optimize)")
        else:
            action_optimize(root)

    if args.action in ("all", "health"):
        h = action_health(root, args.base_url)
        if not h["ok"]:
            success = False
        info(f"Health overall: {'OK' if h['ok'] else 'ISSUES'}")

    if args.action in ("all", "report"):
        rep = action_report(root)
        print(json.dumps(rep, indent=2))
        report_file = root / "storage" / "logs" / "deployment_report.json"
        try:
            report_file.parent.mkdir(parents=True, exist_ok=True)
            report_file.write_text(json.dumps(rep, indent=2))
            ok(f"report saved -> {report_file}")
        except Exception as exc:
            warn(f"could not save report: {exc}")

    print()
    if success:
        ok("Airtrendmedia helper finished successfully.")
        sys.exit(0)
    else:
        err("Airtrendmedia helper finished with warnings/errors.")
        sys.exit(1)


if __name__ == "__main__":
    main()
