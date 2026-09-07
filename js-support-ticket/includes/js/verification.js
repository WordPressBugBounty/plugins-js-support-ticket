/*
 * Self-hosted human verification, browser side. (Roadmap 4.0-SEC-01)
 *
 * The server issues a signed token and a difficulty. This finds a nonce whose
 * sha256(token:nonce) starts with the requested number of zero bits and writes
 * it into the form. A real visitor sees a status line for a few milliseconds; a
 * script that POSTs the form without running any JavaScript has nothing to
 * send. No third-party request, no image, nothing to solve by hand.
 */
(function () {
    'use strict';

    /* Minimal sha256 returning a lowercase hex digest. Kept local so the check
       works on plain HTTP too, where window.crypto.subtle is unavailable. */
    var K = [
        0x428a2f98, 0x71374491, 0xb5c0fbcf, 0xe9b5dba5, 0x3956c25b, 0x59f111f1, 0x923f82a4, 0xab1c5ed5,
        0xd807aa98, 0x12835b01, 0x243185be, 0x550c7dc3, 0x72be5d74, 0x80deb1fe, 0x9bdc06a7, 0xc19bf174,
        0xe49b69c1, 0xefbe4786, 0x0fc19dc6, 0x240ca1cc, 0x2de92c6f, 0x4a7484aa, 0x5cb0a9dc, 0x76f988da,
        0x983e5152, 0xa831c66d, 0xb00327c8, 0xbf597fc7, 0xc6e00bf3, 0xd5a79147, 0x06ca6351, 0x14292967,
        0x27b70a85, 0x2e1b2138, 0x4d2c6dfc, 0x53380d13, 0x650a7354, 0x766a0abb, 0x81c2c92e, 0x92722c85,
        0xa2bfe8a1, 0xa81a664b, 0xc24b8b70, 0xc76c51a3, 0xd192e819, 0xd6990624, 0xf40e3585, 0x106aa070,
        0x19a4c116, 0x1e376c08, 0x2748774c, 0x34b0bcb5, 0x391c0cb3, 0x4ed8aa4a, 0x5b9cca4f, 0x682e6ff3,
        0x748f82ee, 0x78a5636f, 0x84c87814, 0x8cc70208, 0x90befffa, 0xa4506ceb, 0xbef9a3f7, 0xc67178f2
    ];

    function utf8Bytes(str) {
        var out = [];
        for (var i = 0; i < str.length; i++) {
            var c = str.charCodeAt(i);
            if (c < 0x80) {
                out.push(c);
            } else if (c < 0x800) {
                out.push(0xc0 | (c >> 6), 0x80 | (c & 0x3f));
            } else if (c < 0xd800 || c >= 0xe000) {
                out.push(0xe0 | (c >> 12), 0x80 | ((c >> 6) & 0x3f), 0x80 | (c & 0x3f));
            } else {
                i++;
                var cp = 0x10000 + (((c & 0x3ff) << 10) | (str.charCodeAt(i) & 0x3ff));
                out.push(0xf0 | (cp >> 18), 0x80 | ((cp >> 12) & 0x3f), 0x80 | ((cp >> 6) & 0x3f), 0x80 | (cp & 0x3f));
            }
        }
        return out;
    }

    function sha256hex(str) {
        var bytes = utf8Bytes(str);
        var len = bytes.length;
        var withPad = bytes.slice();
        withPad.push(0x80);
        while (withPad.length % 64 !== 56) {
            withPad.push(0);
        }
        var bitLen = len * 8;
        withPad.push(0, 0, 0, 0);
        withPad.push((bitLen >>> 24) & 0xff, (bitLen >>> 16) & 0xff, (bitLen >>> 8) & 0xff, bitLen & 0xff);

        var h = [0x6a09e667, 0xbb67ae85, 0x3c6ef372, 0xa54ff53a, 0x510e527f, 0x9b05688c, 0x1f83d9ab, 0x5be0cd19];
        var w = new Array(64);

        for (var off = 0; off < withPad.length; off += 64) {
            for (var t = 0; t < 16; t++) {
                var j = off + t * 4;
                w[t] = ((withPad[j] << 24) | (withPad[j + 1] << 16) | (withPad[j + 2] << 8) | withPad[j + 3]) >>> 0;
            }
            for (t = 16; t < 64; t++) {
                var w15 = w[t - 15], w2 = w[t - 2];
                var s0 = (((w15 >>> 7) | (w15 << 25)) ^ ((w15 >>> 18) | (w15 << 14)) ^ (w15 >>> 3)) >>> 0;
                var s1 = (((w2 >>> 17) | (w2 << 15)) ^ ((w2 >>> 19) | (w2 << 13)) ^ (w2 >>> 10)) >>> 0;
                w[t] = (w[t - 16] + s0 + w[t - 7] + s1) >>> 0;
            }
            var a = h[0], b = h[1], c = h[2], d = h[3], e = h[4], f = h[5], g = h[6], hh = h[7];
            for (t = 0; t < 64; t++) {
                var S1 = (((e >>> 6) | (e << 26)) ^ ((e >>> 11) | (e << 21)) ^ ((e >>> 25) | (e << 7))) >>> 0;
                var ch = ((e & f) ^ (~e & g)) >>> 0;
                var t1 = (hh + S1 + ch + K[t] + w[t]) >>> 0;
                var S0 = (((a >>> 2) | (a << 30)) ^ ((a >>> 13) | (a << 19)) ^ ((a >>> 22) | (a << 10))) >>> 0;
                var maj = ((a & b) ^ (a & c) ^ (b & c)) >>> 0;
                var t2 = (S0 + maj) >>> 0;
                hh = g; g = f; f = e;
                e = (d + t1) >>> 0;
                d = c; c = b; b = a;
                a = (t1 + t2) >>> 0;
            }
            h[0] = (h[0] + a) >>> 0;
            h[1] = (h[1] + b) >>> 0;
            h[2] = (h[2] + c) >>> 0;
            h[3] = (h[3] + d) >>> 0;
            h[4] = (h[4] + e) >>> 0;
            h[5] = (h[5] + f) >>> 0;
            h[6] = (h[6] + g) >>> 0;
            h[7] = (h[7] + hh) >>> 0;
        }
        var hex = '';
        for (var i2 = 0; i2 < 8; i2++) {
            hex += ('00000000' + h[i2].toString(16)).slice(-8);
        }
        return hex;
    }

    function solve(box) {
        var token = box.getAttribute('data-token') || '';
        var bits = parseInt(box.getAttribute('data-bits'), 10) || 0;
        var status = box.querySelector('.jsst-verification-status');
        var form = box.form || box.closest('form');
        var field = form ? form.querySelector('.jsst-vpow') : document.querySelector('.jsst-vpow');
        /* The signature half of the token is what the server hashes against. */
        var payload = token.split('.')[0] || token;
        var zeros = bits / 4;
        var target = new Array(zeros + 1).join('0');

        if (!field) {
            return;
        }
        if (bits <= 0) {
            field.value = '0';
            done();
            return;
        }

        var nonce = 0;
        var deadline = Date.now() + 15000;

        function done() {
            box.setAttribute('data-solved', '1');
            if (status) {
                status.textContent = status.getAttribute('data-done') || '';
                status.parentNode.style.display = 'none';
            }
        }

        function chunk() {
            var end = nonce + 4000;
            for (; nonce < end; nonce++) {
                if (sha256hex(payload + ':' + nonce).slice(0, zeros) === target) {
                    field.value = String(nonce);
                    done();
                    return;
                }
            }
            if (Date.now() > deadline) {
                /* Give up rather than spin: the server accepts a missing proof
                   only if the site lowered the difficulty, so surface it. */
                if (status) {
                    status.textContent = status.getAttribute('data-failed') || status.textContent;
                }
                return;
            }
            setTimeout(chunk, 0);
        }

        chunk();
    }

    function init() {
        var boxes = document.querySelectorAll('.jsst-verification-work');
        for (var i = 0; i < boxes.length; i++) {
            if (!boxes[i].getAttribute('data-solved')) {
                solve(boxes[i]);
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
