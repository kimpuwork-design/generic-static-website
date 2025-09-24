/**
 * Service Worker to localize external assets by precaching them.
 * It imports the services catalog to gather all icon/image URLs
 * and caches a list of landing page images as well.
 */
self.addEventListener("install", (event) => {
  event.waitUntil((async () => {
    // Import services list to extract icons
    try { importScripts("/assets/services.js"); } catch (e) {}

    const serviceIcons = (self.SERVICES || []).map(s => s.icon).filter(Boolean);

    // Landing page images to cache
    const landingImages = [
      "https://cs1.socpanel.com/cs1/panel_logos/2LZexkl6PJSuDMiYkBEQHj1HLoQhCckfctYMEzVr.png",
      "https://ogprovider.com/ssr-landing-static/eight/images/cart.svg",
      "https://ogprovider.com/ssr-landing-static/eight/images/diamond.svg",
      "https://ogprovider.com/ssr-landing-static/eight/images/instagram.svg",
      "https://ogprovider.com/ssr-landing-static/eight/images/youtube.svg",
      "https://ogprovider.com/ssr-landing-static/eight/images/telegram.svg",
      "https://ogprovider.com/ssr-landing-static/eight/images/twitter.svg",
      "https://ogprovider.com/ssr-landing-static/eight/images/tiktok.svg",
      "https://ogprovider.com/ssr-landing-static/eight/images/key.png",
      "https://ogprovider.com/ssr-landing-static/eight/images/money.png",
      "https://ogprovider.com/ssr-landing-static/eight/images/bag.png",
      "https://ogprovider.com/ssr-landing-static/eight/images/gift.png",
      "https://ogprovider.com/ssr-landing-static/images/socpanel.svg",
      "https://ogprovider.com/ssr-static/images/all-networks.svg",
      "https://socpanel.com/storage/networks/tiktok.svg",
      "https://socpanel.com/storage/networks/telegram.svg",
      "https://socpanel.com/storage/networks/facebook.svg",
      "https://socpanel.com/storage/networks/youtube.svg",
      "https://cs1.socpanel.com/cs1/project_images/ni3SBwxXkCfbsdOpOSg3GjsQPjcluuu76b4TGElA.jpg",
      "https://cs1.socpanel.com/cs1/project_images/4UC30wyEkkNdcwv1u8zErLIlTXrOZr8tuXvfCUly.webp",
      "https://cs1.socpanel.com/cs1/project_images/V3pdpFZkKbErFCaQMl0uQWxpCRNSaIxv9rv1tIGd.png",
      "https://cs1.socpanel.com/cs1/project_images/ELRYSKYZQhpkJYN2lOlRBoq1YVlCFJ0kn4rGYq8N.png",
      "https://www.google.com/favicon.ico"
    ];

    // Combine and dedupe
    const urlsToCache = Array.from(new Set([...landingImages, ...serviceIcons]));

    const cache = await caches.open("og-provider-clone-assets-v1");
    await cache.addAll(urlsToCache);
    self.skipWaiting();
  })());
});

self.addEventListener("activate", (event) => {
  event.waitUntil(self.clients.claim());
});

self.addEventListener("fetch", (event) => {
  const req = event.request;
  const url = new URL(req.url);

  // Only handle cross-origin images/SVG/PNG/WEBP, leave HTML/JS/CSS to network
  const isAsset = /\.(png|jpg|jpeg|svg|webp|gif)$/i.test(url.pathname);
  const isExternal = url.origin !== self.location.origin;

  if (isExternal && isAsset) {
    event.respondWith((async () => {
      const cache = await caches.open("og-provider-clone-assets-v1");
      const cached = await cache.match(req);
      if (cached) return cached;
      try {
        const resp = await fetch(req, { mode: "no-cors" });
        // no-cors responses are opaque; still cache for future
        try { await cache.put(req, resp.clone()); } catch (e) {}
        return resp;
      } catch (e) {
        // Fallback: return a basic 404 response
        return new Response("Asset unavailable", { status: 404 });
      }
    })());
  }
});