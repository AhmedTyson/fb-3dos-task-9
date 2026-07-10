// 1. Heart button toggle
function heartBtn(btn) {
    let heartIcon = btn.querySelector("i");

    if (heartIcon.classList.contains("fa-regular")) {
        heartIcon.classList.remove("fa-regular");
        heartIcon.classList.add("fa-solid");
        heartIcon.style.color = "#ef4444";
    } 
    else {
        heartIcon.classList.remove("fa-solid");
        heartIcon.classList.add("fa-regular");
        heartIcon.style.color = "#666666";
    }
}

// 2. Setup
let allProducts = [];

// 3. Fetch products from API
async function getAllProducts() {
    try {
        let response = await fetch(`${Auth.getBaseUrl()}/products?page=1`, {
            headers: {
                "Accept": "application/json"
            }
        });
        let data = await response.json();
        allProducts = data.data.items;
        displayProducts();
        console.log(allProducts);
    } catch (error) {
        console.error("Error fetching products:", error);
    }
}

// Run on page load
getAllProducts();

// 4. Display products
function displayProducts() {
    let cartona = "";  

    if (!allProducts || allProducts.length === 0) {
        let container = document.querySelector("#productsHome .products-section");
        if (container) container.innerHTML = "<p class='text-center w-100'>No products available.</p>";
        return;
    }

    allProducts.slice(0, 4).forEach(product => {
        if (!product) return;

        let productImg = product.thumbnail;
        if (productImg && !productImg.startsWith('http')) {
            productImg = window.serverUrl() + productImg;
        }

        if (!productImg) {
            productImg = 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?q=80&w=400&auto=format&fit=crop';
        }

        cartona += `
        <div class="product-card">
            <div class="product-img">
                <img src="${productImg}" alt="${product.name || 'Product'}" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?q=80&w=400&auto=format&fit=crop';">
                <button class="heart-btn" onclick="heartBtn(this)">
                    <i class="fa-regular fa-heart"></i>
                </button>
            </div>
            <span class="category-txt">LuxeGear</span>
            <h3>${product.name || 'Premium Product'}</h3>
            <div class="stars-box">
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <i class="fa-solid fa-star"></i>
                <span>(342)</span>
            </div>
            <div class="product-footer">
                <span class="product-price">$${product.base_price || '0.00'}</span>
                <button class="cart-btn" onclick="addToCartHome(${product.id})"><i class="fa-solid fa-cart-shopping"></i></button>
            </div>
        </div>
        `;
    });
    
    let container = document.querySelector("#productsHome .products-section");
    if (container) {
        container.innerHTML = cartona;
    }
}

// 5. Add to cart from home page
async function addToCartHome(productId) {
    const token = Auth.getToken();
    if (!token) { window.location.href = "login.html"; return; }
    try {
        let resp = await fetch(`${Auth.getBaseUrl()}/cart/items`, {
            method: "POST",
            headers: Auth.getHeaders(),
            body: JSON.stringify({ product_id: parseInt(productId), quantity: 1 })
        });
        if (resp.ok) { window.location.href = "cart.html"; }
        else { let d = await resp.json(); console.error(d.message || "Cart add fail"); }
    } catch (e) { console.error("Add to cart error:", e); }
}