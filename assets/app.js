/**
 * Minimal client-side features:
 * - Cart (localStorage)
 * - Auth demo (localStorage)
 * - Header badge updates
 * - Hook login/register forms
 * - Hook service page submit to add to cart
 */

(function(){
  const CART_KEY = "og_clone_cart";
  const USER_KEY = "og_clone_user";

  function getCart(){
    try { return JSON.parse(localStorage.getItem(CART_KEY) || "[]"); } catch(e){ return []; }
  }
  function setCart(items){
    localStorage.setItem(CART_KEY, JSON.stringify(items || []));
    updateCartBadge();
  }
  function addToCart(slug, qty){
    if(!slug) return;
    const items = getCart();
    const existing = items.find(i => i.slug === slug);
    if(existing){
      existing.qty = (existing.qty || 0) + (qty || 1);
    } else {
      items.push({ slug, qty: qty || 1, ts: Date.now() });
    }
    setCart(items);
  }
  function removeFromCart(slug){
    const items = getCart().filter(i => i.slug !== slug);
    setCart(items);
  }
  function clearCart(){
    setCart([]);
  }
  function updateCartBadge(){
    const el = document.getElementById("cartCount");
    if(!el) return;
    const count = getCart().reduce((sum, i) => sum + (i.qty || 0), 0);
    el.textContent = String(count);
    el.style.display = count > 0 ? "inline-flex" : "none";
  }

  function getUser(){
    try { return JSON.parse(localStorage.getItem(USER_KEY) || "null"); } catch(e){ return null; }
  }
  function setUser(user){
    if(user) localStorage.setItem(USER_KEY, JSON.stringify(user));
    else localStorage.removeItem(USER_KEY);
    updateUserLinks();
  }
  function updateUserLinks(){
    const user = getUser();
    const signInLink = document.getElementById("linkSignIn");
    const accountLink = document.getElementById("linkAccount");
    if(signInLink) signInLink.style.display = user ? "none" : "inline-flex";
    if(accountLink) accountLink.style.display = user ? "inline-flex" : "none";
    if(accountLink) accountLink.textContent = user ? (user.username || user.email || "Account") : "Account";
  }

  function initLoginForm(){
    const form = document.querySelector("form");
    if(!form || location.pathname.indexOf("/login") === -1) return;
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const emailOrLogin = document.getElementById("login")?.value || "";
      const password = document.getElementById("password")?.value || "";
      if(!emailOrLogin || !password) { alert("Please enter login and password."); return; }
      setUser({ email: emailOrLogin, username: emailOrLogin.split("@")[0] });
      location.href = "/order/";
    });
  }

  function initRegisterForm(){
    const form = document.querySelector("form");
    if(!form || location.pathname.indexOf("/reg") === -1) return;
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const email = document.getElementById("email")?.value || "";
      const username = document.getElementById("username")?.value || "";
      const password = document.getElementById("password")?.value || "";
      if(!email || !username || !password) { alert("Please fill all required fields."); return; }
      setUser({ email, username });
      location.href = "/order/";
    });
  }

  function initServicePage(){
    if(location.pathname.indexOf("/order/service.html") === -1) return;
    const form = document.getElementById("orderForm");
    if(!form) return;
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const url = new URL(location.href);
      const slug = url.searchParams.get("slug");
      const qty = parseInt(document.getElementById("quantity")?.value || "1", 10);
      addToCart(slug, isNaN(qty) ? 1 : qty);
      alert("Added to cart.");
    });
  }

  function initCartPage(){
    if(location.pathname.indexOf("/cart.html") === -1) return;
    const listEl = document.getElementById("cartList");
    const totalEl = document.getElementById("cartTotal");
    const emptyEl = document.getElementById("cartEmpty");
    const items = getCart();
    if(items.length === 0){
      if(emptyEl) emptyEl.style.display = "block";
      if(listEl) listEl.innerHTML = "";
      if(totalEl) totalEl.textContent = "RM 0.00";
      return;
    }
    if(emptyEl) emptyEl.style.display = "none";
    // Compose list
    const svcById = {};
    (window.SERVICES || []).forEach(s => { svcById[s.id] = s; });
    let sum = 0;
    listEl.innerHTML = items.map(i => {
      const s = svcById[i.slug] || { title: i.slug, price: "" };
      const priceText = s.price || "";
      const priceVal = parseFloat((priceText || "").replace(/[^0-9.]/g, "")) || 0;
      sum += priceVal * (i.qty || 1);
      return `
        <div class="cart-item">
          <div class="cart-item-main">
            <strong>${s.title}</strong>
            <div class="muted">Slug: ${i.slug}</div>
          </div>
          <div class="cart-item-meta">
            <span>${i.qty || 1} × ${priceText || "RM -"}</span>
            <button class="btn btn--small" data-remove="${i.slug}">Remove</button>
          </div>
        </div>
      `;
    }).join("");
    if(totalEl) totalEl.textContent = "RM " + sum.toFixed(2);
    listEl.querySelectorAll("button[data-remove]").forEach(btn => {
      btn.addEventListener("click", () => {
        removeFromCart(btn.getAttribute("data-remove"));
        initCartPage(); // refresh
      });
    });

    // Clear cart
    const clearBtn = document.getElementById("clearCartBtn");
    if(clearBtn) clearBtn.addEventListener("click", () => { clearCart(); initCartPage(); });
  }

  function initHeader(){
    updateCartBadge();
    updateUserLinks();
    const signOutBtn = document.getElementById("btnSignOut");
    if(signOutBtn) {
      signOutBtn.addEventListener("click", (e) => {
        e.preventDefault();
        setUser(null);
        location.href = "/";
      });
    }
  }

  document.addEventListener("DOMContentLoaded", function(){
    initHeader();
    initLoginForm();
    initRegisterForm();
    initServicePage();
    initCartPage();

    // Register service worker to localize external assets
    if ("serviceWorker" in navigator) {
      navigator.serviceWorker.register("/assets/sw.js").catch(() => {});
    }
  });

  // Expose for other scripts if needed
  window.App = { getCart, setCart, addToCart, removeFromCart, clearCart, getUser, setUser, updateCartBadge };
})();