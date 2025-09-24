/**
 * Services catalog for the Order page
 * Unofficial static clone for demo.
 */
const SERVICES = [
  // TikTok
  { id: "tiktok-services-emergency", title: "TikTok Services [EMERGENCY] 🚨", category: "tiktok", icon: "https://socpanel.com/storage/networks/tiktok.svg", price: "RM 0.001" },
  { id: "tiktok-views-refills", title: "Tiktok Views [Refill]", category: "tiktok", icon: "https://socpanel.com/storage/networks/tiktok.svg", price: "RM 0.010" },
  { id: "tiktok-top-services", title: "TikTok Service [ Top Services 🌟 ]", category: "tiktok", icon: "https://socpanel.com/storage/networks/tiktok.svg", price: "RM 0.001" },
  { id: "tiktok-services-cheapest", title: "Tiktok Services [Cheapest] 🔥", category: "tiktok", icon: "https://socpanel.com/storage/networks/tiktok.svg", price: "RM 0.001" },
  { id: "tiktok-likes-cheapest", title: "Tiktok Likes [Cheapest] 🔥", category: "tiktok", icon: "https://socpanel.com/storage/networks/tiktok.svg", price: "RM 0.001" },
  { id: "tiktok-followers-cheap-server", title: "Tiktok Followers [Cheap Server]", category: "tiktok", icon: "https://socpanel.com/storage/networks/tiktok.svg", price: "RM 0.001" },
  { id: "tiktok-views", title: "Tiktok Views [No Refill]", category: "tiktok", icon: "https://socpanel.com/storage/networks/tiktok.svg", price: "RM 0.001" },
  { id: "tiktok-likes-no-refill", title: "Tiktok Likes [No Refill]", category: "tiktok", icon: "https://socpanel.com/storage/networks/tiktok.svg", price: "RM 0.001" },
  { id: "tiktok-likes-refill", title: "Tiktok Likes [Refill]", category: "tiktok", icon: "https://socpanel.com/storage/networks/tiktok.svg", price: "RM 0.001" },
  { id: "tiktok-followers-no-refill", title: "Tiktok Followers [No Refill]", category: "tiktok", icon: "https://socpanel.com/storage/networks/tiktok.svg", price: "RM 0.001" },
  { id: "tiktok-followers-refill", title: "Tiktok Followers [Refill]", category: "tiktok", icon: "https://socpanel.com/storage/networks/tiktok.svg", price: "RM 0.001" },
  { id: "tiktok-share", title: "Tiktok Share", category: "tiktok", icon: "https://socpanel.com/storage/networks/tiktok.svg", price: "—" },
  { id: "tiktok-save", title: "Tiktok Save", category: "tiktok", icon: "https://socpanel.com/storage/networks/tiktok.svg", price: "—" },
  { id: "tiktok-download", title: "Tiktok Download", category: "tiktok", icon: "https://socpanel.com/storage/networks/tiktok.svg", price: "—" },
  { id: "tiktok-live-views-best-working", title: "TikTok Live Views [Working ⭐️]", category: "tiktok", icon: "https://socpanel.com/storage/networks/tiktok.svg", price: "—" },
  { id: "tiktok-live-stream-views-100-concurrent-stable", title: "Tiktok Live Stream Views [100% Concurrent] [Stable] 🔥", category: "tiktok", icon: "https://socpanel.com/storage/networks/tiktok.svg", price: "—" },
  { id: "tiktok-malaysia", title: "Tiktok [Malaysia Location Only]", category: "tiktok", icon: "https://socpanel.com/storage/networks/tiktok.svg", price: "—" },

  // Telegram Premium
  { id: "amazing-telegram-services-2", title: "Telegram Premium Package", category: "telegram-premium", icon: "https://cs1.socpanel.com/cs1/project_images/a6irgSqlojdts6TvBmExGaSCu04ucCYa4xtcPzVk.jpg", price: "RM 2.00" },
  { id: "amazing-telegram-services", title: "Telegram Paid Reaction", category: "telegram-premium", icon: "https://cs1.socpanel.com/cs1/project_images/a6irgSqlojdts6TvBmExGaSCu04ucCYa4xtcPzVk.jpg", price: "—" },
  { id: "telegram-boost-channel", title: "Telegram Premium Boost Public Channel [NO REFILL]", category: "telegram-premium", icon: "https://cs1.socpanel.com/cs1/project_images/a6irgSqlojdts6TvBmExGaSCu04ucCYa4xtcPzVk.jpg", price: "—" },
  { id: "boost-channel-active-story", title: "Telegram Premium Boost Private Channel [NO REFILL]", category: "telegram-premium", icon: "https://cs1.socpanel.com/cs1/project_images/a6irgSqlojdts6TvBmExGaSCu04ucCYa4xtcPzVk.jpg", price: "—" },
  { id: "telegram-premium-members-no-refill", title: "Telegram Premium Members [NO REFILL]", category: "telegram-premium", icon: "https://cs1.socpanel.com/cs1/project_images/a6irgSqlojdts6TvBmExGaSCu04ucCYa4xtcPzVk.jpg", price: "—" },
  { id: "telegram-premium-members-refill", title: "Telegram Premium Members [REFILL]", category: "telegram-premium", icon: "https://cs1.socpanel.com/cs1/project_images/a6irgSqlojdts6TvBmExGaSCu04ucCYa4xtcPzVk.jpg", price: "—" },
  { id: "telegram-premium-post-views-no-refill", title: "Telegram Premium Post Views [NO REFILL]", category: "telegram-premium", icon: "https://cs1.socpanel.com/cs1/project_images/a6irgSqlojdts6TvBmExGaSCu04ucCYa4xtcPzVk.jpg", price: "—" },
  { id: "telegram-premium-reaction", title: "Telegram Premium Reaction [👍 ❤️ 🔥 👎💩🤮]", category: "telegram-premium", icon: "https://cs1.socpanel.com/cs1/project_images/a6irgSqlojdts6TvBmExGaSCu04ucCYa4xtcPzVk.jpg", price: "—" },

  // Telegram
  { id: "telegram-members", title: "Telegram Members [HQ] [0%-20% Drop]", category: "telegram", icon: "https://socpanel.com/storage/networks/telegram.svg", price: "—" },
  { id: "telegram-members-no-drop", title: "Telegram Members [#1] [20%-100% Drop]", category: "telegram", icon: "https://socpanel.com/storage/networks/telegram.svg", price: "—" },
  { id: "telegram-members-2-10-100-drop", title: "Telegram Members [#2] [10%-100% Drop]", category: "telegram", icon: "https://socpanel.com/storage/networks/telegram.svg", price: "—" },
  { id: "telegram-post-view-1", title: "Telegram Post View [#1]", category: "telegram", icon: "https://socpanel.com/storage/networks/telegram.svg", price: "—" },
  { id: "telegram-post-view-2", title: "Telegram Post View [#2]", category: "telegram", icon: "https://socpanel.com/storage/networks/telegram.svg", price: "—" },
  { id: "telegram-poll-vote", title: "Telegram Poll Vote", category: "telegram", icon: "https://socpanel.com/storage/networks/telegram.svg", price: "—" },
  { id: "telegram-reactions-2", title: "Telegram Reactions [👍 ❤️🔥👎 🐳] [#2]", category: "telegram", icon: "https://socpanel.com/storage/networks/telegram.svg", price: "—" },
  { id: "telegram-reactions-1", title: "Telegram Reactions [👍 ❤️ 🔥 👎💩🤮] [#1]", category: "telegram", icon: "https://socpanel.com/storage/networks/telegram.svg", price: "—" },
  { id: "telegram-bot-startapp-referral", title: "Telegram Bot StartApp [For Referral]", category: "telegram", icon: "https://socpanel.com/storage/networks/telegram.svg", price: "—" },

  // Instagram
  { id: "instagram-services-emergency", title: "Instagram Services [EMERGENCY] 🚨", category: "instagram", icon: "https://ogprovider.com/ssr-landing-static/eight/images/instagram.svg", price: "—" },
  { id: "instagram-views", title: "Instagram Views", category: "instagram", icon: "https://ogprovider.com/ssr-landing-static/eight/images/instagram.svg", price: "RM 0.020" },
  { id: "instagram-likes", title: "Instagram Likes", category: "instagram", icon: "https://ogprovider.com/ssr-landing-static/eight/images/instagram.svg", price: "—" },
  { id: "instagram-follower-refill", title: "Instagram Follower [Refill]", category: "instagram", icon: "https://ogprovider.com/ssr-landing-static/eight/images/instagram.svg", price: "—" },
  { id: "instagram-follower-no-refill", title: "Instagram Follower [No Refill]", category: "instagram", icon: "https://ogprovider.com/ssr-landing-static/eight/images/instagram.svg", price: "—" },
  { id: "instagram-comments-new-1", title: "Instagram Comments [New #1]", category: "instagram", icon: "https://ogprovider.com/ssr-landing-static/eight/images/instagram.svg", price: "—" },

  // Shopee
  { id: "shopee-services", title: "Shopee Services", category: "shopee", icon: "https://cs1.socpanel.com/cs1/project_images/1HPXswjsq9V5etxMUuiLdaCFacaOu1XFNXaDje5h.png", price: "—" },
  { id: "shopee-live-stream-viewers-hourly-one-in-the-world-cpupcu", title: "Shopee Live Stream Viewers [Hourly] [Provider]", category: "shopee", icon: "https://cs1.socpanel.com/cs1/project_images/1HPXswjsq9V5etxMUuiLdaCFacaOu1XFNXaDje5h.png", price: "—" },

  // Facebook
  { id: "facebook-malaysia", title: "Facebook Malaysia 🇲🇾 [Chinese Malaysia]", category: "facebook", icon: "https://socpanel.com/storage/networks/facebook.svg", price: "—" },
  { id: "facebook-page-profile-follower", title: "Facebook Page / Profile Follower", category: "facebook", icon: "https://socpanel.com/storage/networks/facebook.svg", price: "—" },
  { id: "facebook-group-members-best-price", title: "Facebook Group Members", category: "facebook", icon: "https://socpanel.com/storage/networks/facebook.svg", price: "—" },
  { id: "facebook-post-likes", title: "Facebook Post Likes", category: "facebook", icon: "https://socpanel.com/storage/networks/facebook.svg", price: "—" },
  { id: "facebook-post-reactions", title: "Facebook Post Reactions", category: "facebook", icon: "https://socpanel.com/storage/networks/facebook.svg", price: "—" },
  { id: "facebook-video-views", title: "Facebook Video Views", category: "facebook", icon: "https://socpanel.com/storage/networks/facebook.svg", price: "—" },
  { id: "facebook-live-stream-cheapest", title: "Facebook Live Stream [Cheapest]", category: "facebook", icon: "https://socpanel.com/storage/networks/facebook.svg", price: "—" },
  { id: "facebook-live-stream-super-vip", title: "Facebook Live Stream [Super VIP]", category: "facebook", icon: "https://socpanel.com/storage/networks/facebook.svg", price: "—" },

  // WhatsApp
  { id: "whatsapp-channel-members-targeted-2", title: "Whatsapp Channel Members [No Refill]", category: "whatsapp", icon: "https://cs1.socpanel.com/cs1/project_images/C6VlMV7jEDjpVMAtqXTF7tS5mi2Kf1VUZwJX5r9H.webp", price: "—" },
  { id: "whatsapp-channel-members-targeted", title: "Whatsapp Channel Members [No Refill] [Targeted]", category: "whatsapp", icon: "https://cs1.socpanel.com/cs1/project_images/C6VlMV7jEDjpVMAtqXTF7tS5mi2Kf1VUZwJX5r9H.webp", price: "—" },
  { id: "whatsapp-channel-emoji-reactions", title: "Whatsapp Channel Emoji Reactions [No Refill]", category: "whatsapp", icon: "https://cs1.socpanel.com/cs1/project_images/C6VlMV7jEDjpVMAtqXTF7tS5mi2Kf1VUZwJX5r9H.webp", price: "—" },
  { id: "whatsapp-channel-emoji-reactions-targeted", title: "Whatsapp Channel Emoji Reactions [Targeted]", category: "whatsapp", icon: "https://cs1.socpanel.com/cs1/project_images/C6VlMV7jEDjpVMAtqXTF7tS5mi2Kf1VUZwJX5r9H.webp", price: "—" },

  // YouTube
  { id: "youtube-subscribers-stable-low-drop", title: "YouTube Subscribers [Stable] [Low Drop]", category: "youtube", icon: "https://socpanel.com/storage/networks/youtube.svg", price: "RM 0.150" },
  { id: "youtube-subscribers-no-refill-high-drop", title: "YouTube Subscribers [No Refill] [HIGH DROP]", category: "youtube", icon: "https://socpanel.com/storage/networks/youtube.svg", price: "—" },
  { id: "youtube-views", title: "YouTube Views", category: "youtube", icon: "https://socpanel.com/storage/networks/youtube.svg", price: "—" },
  { id: "youtube-watchtime-refill-slow-2", title: "Youtube Watchtime [REFILL] [SLOW] [#2]", category: "youtube", icon: "https://socpanel.com/storage/networks/youtube.svg", price: "—" },
  { id: "youtube-watchtime-refill-slow-new", title: "Youtube Watchtime [REFILL] [SLOW] [NEW]", category: "youtube", icon: "https://socpanel.com/storage/networks/youtube.svg", price: "—" },

  // Web Traffic
  { id: "all-in-one-traffic-clv-custom-live-visits", title: "All-In-One Traffic [CLV™ - Custom Live Visits]", category: "web-traffic", icon: "https://cs1.socpanel.com/cs1/project_images/V3pdpFZkKbErFCaQMl0uQWxpCRNSaIxv9rv1tIGd.png", price: "RM 1.00" },
  { id: "worldwide-traffic-awv-automated-website-visits", title: "Worldwide Traffic [AWV™ - Automated Website Visits] 🔥", category: "web-traffic", icon: "https://cs1.socpanel.com/cs1/project_images/QYxS5yqLJDbSuD3hZELB6ehGQ6a8yRk7VnUYVySd.png", price: "—" },
  { id: "worldwide-traffic", title: "Worldwide Traffic", category: "web-traffic", icon: "https://cs1.socpanel.com/cs1/project_images/QYxS5yqLJDbSuD3hZELB6ehGQ6a8yRk7VnUYVySd.png", price: "—" },
  { id: "geo-targeted-traffic", title: "GEO Targeted Traffic", category: "web-traffic", icon: "https://cs1.socpanel.com/cs1/project_images/QYxS5yqLJDbSuD3hZELB6ehGQ6a8yRk7VnUYVySd.png", price: "—" },
  { id: "mobile-traffic-from-iphone-14-rst-real-social-traffic", title: "Mobile Traffic from iPhone 15 [RST™ - Real Social Traffic] 🔥", category: "web-traffic", icon: "https://cs1.socpanel.com/cs1/project_images/QYxS5yqLJDbSuD3hZELB6ehGQ6a8yRk7VnUYVySd.png", price: "—" },
  { id: "worldwide-mobile-traffic", title: "Worldwide Mobile Traffic", category: "web-traffic", icon: "https://cs1.socpanel.com/cs1/project_images/QYxS5yqLJDbSuD3hZELB6ehGQ6a8yRk7VnUYVySd.png", price: "—" },
  { id: "geo-mobile-traffic", title: "GEO Mobile Traffic", category: "web-traffic", icon: "https://cs1.socpanel.com/cs1/project_images/QYxS5yqLJDbSuD3hZELB6ehGQ6a8yRk7VnUYVySd.png", price: "—" },
  { id: "worldwide-traffic-from-exchange-platforms", title: "Worldwide Traffic from Exchange Platforms", category: "web-traffic", icon: "https://cs1.socpanel.com/cs1/project_images/QYxS5yqLJDbSuD3hZELB6ehGQ6a8yRk7VnUYVySd.png", price: "—" },
  { id: "masked-website-traffic-custom", title: "Masked Website Traffic - Custom 🔥", category: "web-traffic", icon: "https://cs1.socpanel.com/cs1/project_images/QYxS5yqLJDbSuD3hZELB6ehGQ6a8yRk7VnUYVySd.png", price: "—" },
  { id: "cryptocurrency-niche-targeted-traffic-premium", title: "Cryptocurrency Niche Targeted Traffic (Premium)", category: "web-traffic", icon: "https://cs1.socpanel.com/cs1/project_images/QYxS5yqLJDbSuD3hZELB6ehGQ6a8yRk7VnUYVySd.png", price: "—" },
  { id: "south-korean-targeted-traffic-premium", title: "🇰🇷 South-Korean Targeted Traffic (Premium)", category: "web-traffic", icon: "https://cs1.socpanel.com/cs1/project_images/QYxS5yqLJDbSuD3hZELB6ehGQ6a8yRk7VnUYVySd.png", price: "—" },
  { id: "usa-targeted-traffic-premium", title: "🇺🇸 USA Targeted Traffic (Premium)", category: "web-traffic", icon: "https://cs1.socpanel.com/cs1/project_images/QYxS5yqLJDbSuD3hZELB6ehGQ6a8yRk7VnUYVySd.png", price: "—" },

  // Crypto [USDT]
  { id: "usdt-cryptomus", title: "USDT Cryptomus", category: "crypto", icon: "https://cs1.socpanel.com/cs1/project_images/gz65e9RPyElwabnph0h8HPtoJ5fo2pj6aaA1zB32.png", price: "—" },
  { id: "test-do-not-buy", title: "Test [Do not Buy]", category: "crypto", icon: "https://cs1.socpanel.com/cs1/no_image.svg", price: "—" },
];

const CATEGORIES = {
  all: "All",
  "tiktok": "Tiktok",
  "telegram-premium": "Telegram Premium",
  "telegram": "Telegram",
  "instagram": "Instagram",
  "shopee": "Shopee",
  "facebook": "Facebook",
  "whatsapp": "WhatsApp",
  "youtube": "Youtube",
  "web-traffic": "Web Traffic",
  "crypto": "Crypto [USDT]",
};

function humanCategoryFromHash(hash) {
  const key = (hash || "").replace(/^#/, "") || "all";
  return { key, label: CATEGORIES[key] || "All" };
}

const STATE = { search: "", sort: "az" };

function renderServices(categoryKey) {
  const grid = document.getElementById("service-grid");
  const heading = document.getElementById("service-heading");
  if (!grid) return;

  const cat = categoryKey && CATEGORIES[categoryKey] ? categoryKey : "all";
  let list = cat === "all" ? SERVICES.slice() : SERVICES.filter(s => s.category === cat);

  // search
  const q = (STATE.search || "").trim().toLowerCase();
  if (q) {
    list = list.filter(s => (s.title || "").toLowerCase().includes(q));
  }

  // sort
  list.sort((a, b) => {
    const ta = (a.title || "").toLowerCase();
    const tb = (b.title || "").toLowerCase();
    if (STATE.sort === "za") return tb.localeCompare(ta);
    return ta.localeCompare(tb);
  });

  if (heading) {
    heading.textContent = cat === "all" ? "All services" : CATEGORIES[cat];
  }

  grid.innerHTML = list.map(s => `
    <article class="card">
      <div class="top">
        <img src="${s.icon}" width="24" height="24" alt="">
        <h4>${s.title}</h4>
      </div>
      <p>${s.price ? `Starting at <span class="price">${s.price}</span>` : ""}</p>
      <a class="btn btn--small btn--primary" href="./service.html?slug=${s.id}">Select</a>
    </article>
  `).join("");

  // update active state on sidebar filters
  document.querySelectorAll(".filters a").forEach(a => {
    const isActive = a.getAttribute("href") === `#${cat}` || (cat === "all" && a.getAttribute("href") === "#all");
    a.setAttribute("aria-current", isActive ? "page" : "false");
  });
}

function initFilters() {
  document.querySelectorAll(".filters a").forEach(a => {
    a.addEventListener("click", (e) => {
      e.preventDefault();
      const href = a.getAttribute("href") || "#all";
      history.replaceState(null, "", href);
      renderServices(href.slice(1));
    });
  });

  const searchEl = document.getElementById("search");
  const sortEl = document.getElementById("sort");
  if (searchEl) {
    searchEl.addEventListener("input", () => {
      STATE.search = searchEl.value || "";
      const { key } = humanCategoryFromHash(location.hash);
      renderServices(key);
    });
  }
  if (sortEl) {
    sortEl.addEventListener("change", () => {
      STATE.sort = sortEl.value || "az";
      const { key } = humanCategoryFromHash(location.hash);
      renderServices(key);
    });
  }

  window.addEventListener("hashchange", () => {
    const { key } = humanCategoryFromHash(location.hash);
    renderServices(key);
  });

  const { key } = humanCategoryFromHash(location.hash);
  renderServices(key);
}

document.addEventListener("DOMContentLoaded", initFilters);