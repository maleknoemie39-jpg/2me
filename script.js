const productGrid = document.querySelector("#product-grid");
const searchInput = document.querySelector("#product-search");
const sortSelect = document.querySelector("#product-sort");
const resultsCount = document.querySelector("#results-count");
const emptyState = document.querySelector("#empty-state");
const cartDialog = document.querySelector("#cart-dialog");
const originalProductOrder = new Map(
  [...productGrid.querySelectorAll(".product-card")].map((product, index) => [product, index])
);

function filterProducts() {
  const activeFilter = document.querySelector(".filter-button.active")?.dataset.filter || "tout";
  const search = searchInput.value.trim().toLocaleLowerCase("fr");
  let visibleCount = 0;

  productGrid.querySelectorAll(".product-card").forEach((product) => {
    const matchesCategory = activeFilter === "tout" || product.dataset.category === activeFilter;
    const matchesSearch = product.dataset.search.toLocaleLowerCase("fr").includes(search);
    const isVisible = matchesCategory && matchesSearch;
    product.hidden = !isVisible;
    if (isVisible) visibleCount += 1;
  });

  resultsCount.textContent = `${visibleCount} pièce${visibleCount > 1 ? "s" : ""} à découvrir`;
  emptyState.hidden = visibleCount > 0;
}

function sortProducts() {
  const sortBy = sortSelect.value;
  const products = [...productGrid.querySelectorAll(".product-card")];

  products.sort((left, right) => {
    if (sortBy === "price-asc" || sortBy === "price-desc") {
      const priceDifference = Number(left.dataset.price) - Number(right.dataset.price);
      if (priceDifference !== 0) return sortBy === "price-asc" ? priceDifference : -priceDifference;
    }

    if (sortBy === "name-asc") {
      const nameDifference = left.dataset.name.localeCompare(right.dataset.name, "fr");
      if (nameDifference !== 0) return nameDifference;
    }

    return originalProductOrder.get(left) - originalProductOrder.get(right);
  });

  products.forEach((product) => productGrid.append(product));
}

document.querySelectorAll(".filter-button").forEach((button) => {
  button.addEventListener("click", () => {
    document.querySelectorAll(".filter-button").forEach((filterButton) => {
      const isActive = filterButton === button;
      filterButton.classList.toggle("active", isActive);
      filterButton.setAttribute("aria-pressed", String(isActive));
    });
    filterProducts();
  });
});

searchInput.addEventListener("input", filterProducts);
sortSelect.addEventListener("change", sortProducts);
document.querySelector("#open-cart").addEventListener("click", () => cartDialog.showModal());
document.querySelector("#close-cart").addEventListener("click", () => cartDialog.close());
document.querySelector("#continue-shopping")?.addEventListener("click", () => cartDialog.close());
cartDialog.addEventListener("click", (event) => {
  if (event.target === cartDialog) cartDialog.close();
});

filterProducts();

const query = new URLSearchParams(window.location.search);
if (query.has("open_cart") || document.body.dataset.openCart === "1") cartDialog.showModal();
if (query.has("message")) {
  window.history.replaceState({}, "", window.location.pathname + window.location.hash);
}
