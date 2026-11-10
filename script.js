const products = [
  {
    id: "veste-lin",
    name: "Veste en lin",
    category: "Vêtements",
    size: "Taille M · Très bon état",
    price: 28,
    image: "https://images.unsplash.com/photo-1591047139829-d91aecb6caea?auto=format&fit=crop&w=700&q=80",
    alt: "Veste beige en lin",
    tag: "Belle trouvaille"
  },
  {
    id: "sac-cuir",
    name: "Sac en cuir vintage",
    category: "Accessoires",
    size: "Pièce unique · Bon état",
    price: 35,
    image: "https://images.unsplash.com/photo-1584917865442-de89df76afd3?auto=format&fit=crop&w=700&q=80",
    alt: "Sac à main en cuir",
    tag: "Pièce unique"
  },
  {
    id: "chemise-rayee",
    name: "Chemise rayée",
    category: "Vêtements",
    size: "Taille S · Très bon état",
    price: 19,
    image: "https://images.unsplash.com/photo-1598554747436-c9293d6a588f?auto=format&fit=crop&w=700&q=80",
    alt: "Chemise rayée bleu et blanc",
    tag: "Comme neuve"
  },
  {
    id: "baskets-toile",
    name: "Baskets en toile",
    category: "Chaussures",
    size: "Pointure 38 · Bon état",
    price: 24,
    image: "https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=700&q=80",
    alt: "Baskets en toile claires",
    tag: "Bon plan"
  },
  {
    id: "pull-maille",
    name: "Pull maille douce",
    category: "Vêtements",
    size: "Taille L · Très bon état",
    price: 26,
    image: "https://images.unsplash.com/photo-1576566588028-4147f3842f27?auto=format&fit=crop&w=700&q=80",
    alt: "Pull en maille",
    tag: "Coup de cœur"
  },
  {
    id: "lunettes-soleil",
    name: "Lunettes rétro",
    category: "Accessoires",
    size: "Taille unique · Bon état",
    price: 16,
    image: "https://images.unsplash.com/photo-1511499767150-a48a237f0083?auto=format&fit=crop&w=700&q=80",
    alt: "Lunettes de soleil",
    tag: "Vintage"
  },
  {
    id: "jean-droit",
    name: "Jean droit",
    category: "Vêtements",
    size: "Taille 38 · Très bon état",
    price: 22,
    image: "https://images.unsplash.com/photo-1541099649105-f69ad21f3246?auto=format&fit=crop&w=700&q=80",
    alt: "Jean bleu",
    tag: "Indispensable"
  },
  {
    id: "sandales",
    name: "Sandales en cuir",
    category: "Chaussures",
    size: "Pointure 39 · Bon état",
    price: 29,
    image: "https://images.unsplash.com/photo-1543163521-1bf539c55dd2?auto=format&fit=crop&w=700&q=80",
    alt: "Sandales à talon",
    tag: "Belle trouvaille"
  }
];

const storageKey = "reporte-cart";
let activeCategory = "tout";
let toastTimeout;

const productGrid = document.querySelector("#product-grid");
const searchInput = document.querySelector("#product-search");
const resultsCount = document.querySelector("#results-count");
const emptyState = document.querySelector("#empty-state");
const cartDialog = document.querySelector("#cart-dialog");
const cartItems = document.querySelector("#cart-items");
const cartCount = document.querySelector("#cart-count");
const dialogCartCount = document.querySelector("#dialog-cart-count");
const cartEmpty = document.querySelector("#cart-empty");
const cartFooter = document.querySelector("#cart-footer");
const cartTotal = document.querySelector("#cart-total");
const toast = document.querySelector("#toast");

function readCart() {
  try {
    const savedCart = JSON.parse(localStorage.getItem(storageKey) || "[]");
    if (!Array.isArray(savedCart)) return [];
    return [...new Set(savedCart)].filter((id) => products.some((product) => product.id === id));
  } catch (error) {
    console.error("Impossible de lire le panier enregistré.", error);
    return [];
  }
}

let cart = readCart();

function saveCart() {
  try {
    localStorage.setItem(storageKey, JSON.stringify(cart));
  } catch (error) {
    console.error("Impossible d’enregistrer le panier.", error);
    showToast("Le panier ne peut pas être enregistré sur cet appareil.");
  }
}

function formatPrice(price) {
  return new Intl.NumberFormat("fr-FR", { style: "currency", currency: "EUR", maximumFractionDigits: 0 }).format(price);
}

function renderProducts() {
  const search = searchInput.value.trim().toLocaleLowerCase("fr");
  const visibleProducts = products.filter((product) => {
    const matchesCategory = activeCategory === "tout" || product.category === activeCategory;
    const matchesSearch = `${product.name} ${product.category} ${product.size}`.toLocaleLowerCase("fr").includes(search);
    return matchesCategory && matchesSearch;
  });

  productGrid.innerHTML = visibleProducts.map((product) => `
    <article class="product-card">
      <div class="product-image-wrap">
        <img class="product-image" src="${product.image}" alt="${product.alt}" loading="lazy">
        <span class="product-tag">${product.tag}</span>
        <button class="add-button" type="button" data-add="${product.id}" aria-label="${cart.includes(product.id) ? `${product.name} est déjà dans le panier` : `Ajouter ${product.name} au panier`}" ${cart.includes(product.id) ? "disabled" : ""}>${cart.includes(product.id) ? "✓" : "+"}</button>
      </div>
      <div class="product-info">
        <p class="product-category">${product.category}</p>
        <div class="product-name-row">
          <h3 class="product-name">${product.name}</h3>
          <span class="product-price">${formatPrice(product.price)}</span>
        </div>
        <p class="product-details">${product.size}</p>
      </div>
    </article>
  `).join("");
  resultsCount.textContent = `${visibleProducts.length} pièce${visibleProducts.length > 1 ? "s" : ""} à découvrir`;
  emptyState.hidden = visibleProducts.length > 0;
}

function renderCart() {
  const items = cart.map((id) => products.find((product) => product.id === id)).filter(Boolean);
  const itemCount = items.length;
  cartCount.textContent = itemCount;
  cartCount.setAttribute("aria-label", `${itemCount} article${itemCount > 1 ? "s" : ""}`);
  dialogCartCount.textContent = `(${itemCount})`;
  cartEmpty.hidden = itemCount > 0;
  cartFooter.hidden = itemCount === 0;
  cartItems.innerHTML = items.map((product) => `
    <article class="cart-item">
      <img src="${product.image}" alt="">
      <div>
        <h3>${product.name}</h3>
        <p>${product.size}</p>
        <button class="remove-item" type="button" data-remove="${product.id}">Retirer</button>
      </div>
      <span class="cart-item-price">${formatPrice(product.price)}</span>
    </article>
  `).join("");
  cartTotal.textContent = formatPrice(items.reduce((total, product) => total + product.price, 0));
}

function showToast(message) {
  toast.textContent = message;
  toast.classList.add("visible");
  window.clearTimeout(toastTimeout);
  toastTimeout = window.setTimeout(() => toast.classList.remove("visible"), 2600);
}

document.querySelectorAll(".filter-button").forEach((button) => {
  button.addEventListener("click", () => {
    activeCategory = button.dataset.filter;
    document.querySelectorAll(".filter-button").forEach((filterButton) => {
      const isActive = filterButton === button;
      filterButton.classList.toggle("active", isActive);
      filterButton.setAttribute("aria-pressed", String(isActive));
    });
    renderProducts();
  });
});

searchInput.addEventListener("input", renderProducts);

productGrid.addEventListener("click", (event) => {
  const button = event.target.closest("[data-add]");
  if (!button) return;

  const product = products.find((item) => item.id === button.dataset.add);
  if (!product) return;
  if (cart.includes(product.id)) return;
  cart.push(product.id);
  saveCart();
  renderCart();
  renderProducts();
  showToast(`${product.name} ajouté au panier`);
});

cartItems.addEventListener("click", (event) => {
  const button = event.target.closest("[data-remove]");
  if (!button) return;

  const itemIndex = cart.indexOf(button.dataset.remove);
  if (itemIndex < 0) return;
  cart.splice(itemIndex, 1);
  saveCart();
  renderCart();
  renderProducts();
});

document.querySelector("#open-cart").addEventListener("click", () => cartDialog.showModal());
document.querySelector("#close-cart").addEventListener("click", () => cartDialog.close());
document.querySelector("#continue-shopping").addEventListener("click", () => cartDialog.close());

cartDialog.addEventListener("click", (event) => {
  if (event.target === cartDialog) cartDialog.close();
});

document.querySelector("#checkout-button").addEventListener("click", () => {
  document.querySelector("#checkout-message").textContent = "Merci ! Cette démonstration ne traite pas de vrai paiement.";
});

renderProducts();
renderCart();
