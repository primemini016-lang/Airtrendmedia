<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class TrackUserPresence {
  public function handle(Request $request, Closure $next) {
    $response=$next($request);
    try { $u=auth('web')->user(); if($u){ $u->forceFill(['last_seen_at'=>now(),'online_at'=>now()])->saveQuietly(); } } catch(\Throwable $e) {}
    return $response;
  }
}
