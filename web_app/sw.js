self.addEventListener("fetch", event => {
    const req = event.request;

    // Ignorar chrome-extension://
    if (!req.url.startsWith("http")) {
        return; 
    }

    event.respondWith(
        caches.open("pwa-cache").then(cache => {
            return cache.match(req).then(resp => {
                return resp || fetch(req).then(networkResp => {
                    cache.put(req, networkResp.clone());
                    return networkResp;
                });
            });
        })
    );
});