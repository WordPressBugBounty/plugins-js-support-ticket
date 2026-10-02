/*
 * The background worker behind browser notifications. (Roadmap 4.5-UX-02)
 *
 * It runs with no page open, which is the whole point: an agent is told a
 * ticket was assigned to them whether or not the help desk is on screen. It
 * does exactly two things and deliberately nothing else - show what arrived,
 * and open the ticket when it is clicked - because a service worker that
 * caches or intercepts requests is a service worker that serves somebody a
 * stale ticket.
 */
self.addEventListener('push', function (event) {
    var payload = { title: 'JS Help Desk', body: '', url: '' };
    if (event.data) {
        try {
            payload = event.data.json();
        } catch (e) {
            /* A message we cannot read is still worth surfacing: the agent can
               open the desk and look. Silence would be worse. */
            payload.body = event.data.text();
        }
    }
    event.waitUntil(
        self.registration.showNotification(payload.title || 'JS Help Desk', {
            body: payload.body || '',
            tag: payload.url || 'jsst',
            data: { url: payload.url || '' }
        })
    );
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    var url = (event.notification.data && event.notification.data.url) ? event.notification.data.url : '';
    if (!url) {
        return;
    }
    /* Focus a tab that already has the desk open rather than piling up a new
       one per notification. */
    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
            for (var i = 0; i < list.length; i++) {
                if (list[i].url === url && 'focus' in list[i]) {
                    return list[i].focus();
                }
            }
            if (self.clients.openWindow) {
                return self.clients.openWindow(url);
            }
        })
    );
});
