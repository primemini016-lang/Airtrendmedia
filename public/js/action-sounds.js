/**
 * Airtrendmedia — Action Sounds Player
 * Plays subtle sounds for like, comment, message, and notification actions.
 * Uses the Web Audio API to synthesize pleasant tones on the fly (no external
 * files required) and optionally plays MP3 files from /sounds/ when present.
 *
 * Sound list:
 *   like         — short rising pop (reaction)
 *   comment      — soft double-beep
 *   message      — gentle bubble
 *   notification — ascending chime
 *   send         — whoosh-pop (message sent)
 *   error        — low buzz
 *
 * Sounds are disabled automatically when:
 *   - the user has not interacted with the page yet (autoplay policy)
 *   - window.AIRTREND_SOUNDS_ENABLED === false
 *   - localStorage['airtrend_sounds_off'] === '1'
 */
(function () {
    'use strict';

    const SOUND_BASE = (window.AIRTREND_SOUND_BASE || '/sounds/') ;
    const SOUND_FILES = {
        like: 'like.wav',
        comment: 'comment.wav',
        message: 'message.wav',
        notification: 'notification.wav',
        send: 'send.wav',
        error: 'error.wav',
    };

    // Synthesised tone definitions (freq, duration, type, gain)
    const TONES = {
        like: [
            { f: 880, d: 0.06, type: 'sine', g: 0.15 },
            { f: 1320, d: 0.08, type: 'sine', g: 0.12 },
        ],
        comment: [
            { f: 660, d: 0.05, type: 'triangle', g: 0.12 },
            { f: 990, d: 0.06, type: 'triangle', g: 0.12 },
        ],
        message: [
            { f: 523, d: 0.07, type: 'sine', g: 0.13 },
            { f: 784, d: 0.09, type: 'sine', g: 0.10 },
        ],
        notification: [
            { f: 1047, d: 0.07, type: 'sine', g: 0.13 },
            { f: 1319, d: 0.07, type: 'sine', g: 0.12 },
            { f: 1568, d: 0.10, type: 'sine', g: 0.10 },
        ],
        send: [
            { f: 392, d: 0.05, type: 'sine', g: 0.12 },
            { f: 880, d: 0.08, type: 'sine', g: 0.13 },
        ],
        error: [
            { f: 220, d: 0.12, type: 'square', g: 0.08 },
            { f: 180, d: 0.14, type: 'square', g: 0.08 },
        ],
    };

    let ctx = null;
    let enabled =
        window.AIRTREND_SOUNDS_ENABLED !== false &&
        localStorage.getItem('airtrend_sounds_off') !== '1';

    function audioCtx() {
        if (!ctx) {
            const AC = window.AudioContext || window.webkitAudioContext;
            if (!AC) return null;
            try { ctx = new AC(); } catch (e) { return null; }
        }
        if (ctx.state === 'suspended') { ctx.resume().catch(function () {}); }
        return ctx;
    }

    function playTone(note, startAt) {
        const c = audioCtx();
        if (!c) return;
        const osc = c.createOscillator();
        const gain = c.createGain();
        osc.type = note.type || 'sine';
        osc.frequency.value = note.f;
        const t = c.currentTime + (startAt || 0);
        gain.gain.setValueAtTime(0.0001, t);
        gain.gain.exponentialRampToValueAtTime(note.g || 0.12, t + 0.01);
        gain.gain.exponentialRampToValueAtTime(0.0001, t + (note.d || 0.1));
        osc.connect(gain);
        gain.connect(c.destination);
        osc.start(t);
        osc.stop(t + (note.d || 0.1) + 0.02);
    }

    function playFile(name) {
        if (!name) return false;
        try {
            const a = new Audio(SOUND_BASE + name);
            a.volume = 0.4;
            a.play().catch(function () {});
            return true;
        } catch (e) {
            return false;
        }
    }

    function play(name) {
        if (!enabled || !name) return;
        if (!TONES[name]) return;
        // Prefer real MP3 files if available, fall back to synthesised tones.
        if (playFile(SOUND_FILES[name])) return;
        let offset = 0;
        TONES[name].forEach(function (note) {
            playTone(note, offset);
            offset += note.d;
        });
    }

    // Public API
    window.AirtrendSounds = {
        play: play,
        like: function () { play('like'); },
        comment: function () { play('comment'); },
        message: function () { play('message'); },
        notification: function () { play('notification'); },
        send: function () { play('send'); },
        error: function () { play('error'); },
        enable: function () {
            enabled = true;
            localStorage.removeItem('airtrend_sounds_off');
        },
        disable: function () {
            enabled = false;
            localStorage.setItem('airtrend_sounds_off', '1');
        },
        toggle: function () {
            if (enabled) { this.disable(); } else { this.enable(); }
            return enabled;
        },
        isEnabled: function () { return enabled; },
    };

    // Convenience global that auto-degrades silently.
    window.airtrendSound = play;

    // Auto-play on common DOM events when a data-sound attribute is present.
    document.addEventListener('click', function (e) {
        const el = e.target.closest('[data-sound]');
        if (el && enabled) { play(el.getAttribute('data-sound')); }
    });

    // Expose a global toggle so any UI button can flip sounds on/off.
    document.addEventListener('DOMContentLoaded', function () {
        const toggles = document.querySelectorAll('[data-sound-toggle]');
        toggles.forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                const on = window.AirtrendSounds.toggle();
                btn.setAttribute('aria-pressed', on ? 'true' : 'false');
                btn.dispatchEvent(new CustomEvent('sound-toggle', { detail: { enabled: on } }));
            });
        });
    });
})();
