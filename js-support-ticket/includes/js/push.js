/*
 * Registering this browser for notifications. (Roadmap 4.5-UX-02)
 *
 * Progressive throughout: the button only appears where the server said push
 * is possible, and every step that a browser can refuse is checked rather than
 * assumed. Permission is asked for on a click and never on page load - a site
 * that asks the moment you arrive is a site everybody blocks.
 */
(function () {
    var button = document.getElementById('jsst-push-enable');
    if (!button) {
        return;
    }
    var state = document.getElementById('jsst-push-state');

    function say(text) {
        if (state) {
            state.textContent = text;
        }
    }

    /* The server hands us the key base64url-encoded; the subscription API
       wants the raw bytes. */
    function keyBytes(base64) {
        var padded = (base64 + '='.repeat((4 - (base64.length % 4)) % 4)).replace(/-/g, '+').replace(/_/g, '/');
        var raw = window.atob(padded);
        var bytes = new Uint8Array(raw.length);
        for (var i = 0; i < raw.length; i++) {
            bytes[i] = raw.charCodeAt(i);
        }
        return bytes;
    }

    function toBase64(buffer) {
        var bytes = new Uint8Array(buffer);
        var binary = '';
        for (var i = 0; i < bytes.byteLength; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return window.btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
    }

    button.addEventListener('click', function () {
        if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
            say(jsstPush.unsupported);
            return;
        }
        Notification.requestPermission().then(function (permission) {
            if (permission !== 'granted') {
                say(jsstPush.refused);
                return;
            }
            var worker = document.getElementById('jsst-push-worker').value;
            navigator.serviceWorker.register(worker).then(function (registration) {
                return registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: keyBytes(document.getElementById('jsst-push-key').value)
                });
            }).then(function (subscription) {
                var body = new FormData();
                body.append('action', 'jsst_push_subscribe');
                body.append('nonce', document.getElementById('jsst-push-nonce').value);
                body.append('endpoint', subscription.endpoint);
                body.append('p256dh', toBase64(subscription.getKey('p256dh')));
                body.append('auth', toBase64(subscription.getKey('auth')));
                return fetch(jsstPush.ajaxurl, { method: 'POST', credentials: 'same-origin', body: body });
            }).then(function (response) {
                return response.json();
            }).then(function (result) {
                say(result && result.success ? jsstPush.done : jsstPush.failed);
            }).catch(function () {
                say(jsstPush.failed);
            });
        });
    });
})();
