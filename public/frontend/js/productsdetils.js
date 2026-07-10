document.addEventListener('DOMContentLoaded', function () {
    // Get product ID from URL
    const urlParams = new URLSearchParams(window.location.search);
    const productId = urlParams.get('id');
    
    const decreaseBtn = document.getElementById('decreaseBtn');
    const increaseBtn = document.getElementById('increaseBtn');
    const quantityInput = document.getElementById('quantityInput');

    const minValue = 1; 

    increaseBtn.addEventListener('click', function () {
        let currentValue = parseInt(quantityInput.value, 10) || 0;
        currentValue += 1;
        quantityInput.value = currentValue;
    });

    decreaseBtn.addEventListener('click', function () {
        let currentValue = parseInt(quantityInput.value, 10) || 0;
        if (currentValue > minValue) {
            currentValue -= 1;
        }
        quantityInput.value = currentValue;
    });

    // Load product data if ID is present in URL
    if (productId) {
        loadProduct(productId);
    }
});

// Global delegation: catch Add to Cart / Buy Now clicks
// Uses data-id (set by updateProductPage) OR URL ?id= as fallback
function getProductIdFromUrl() {
    return new URLSearchParams(window.location.search).get('id');
}
document.addEventListener('click', function (e) {
    const btn = e.target.closest('button');
    if (!btn) return;
    const id = btn.getAttribute('data-id') || getProductIdFromUrl();
    if (!id) return;
    const txt = btn.textContent.trim();
    if (txt.includes('Add to Cart')) {
        addToCart(id);
    } else if (txt.includes('Buy Now')) {
        buyNow(id);
    }
});

async function loadProduct(productId) {
    const baseUrl = window.API_CONFIG?.getBaseUrl() || 'http://localhost:8000/api';
    const serverUrl = window.API_CONFIG?.getServerUrl() || 'http://localhost:8000';
    
    try {
        const response = await fetch(`${baseUrl}/products/${productId}`, {
            headers: { "Accept": "application/json" }
        });
        
        if (!response.ok) {
            // 401/403 means auth issue — redirect login
            if (response.status === 401 || response.status === 403) {
                if (window.Auth) Auth.clearAuth();
                window.location.href = "login.html";
                return;
            }
            throw new Error('Product not found');
        }
        
        const data = await response.json();
        const product = data && data.data;
        
        if (!product) {
            throw new Error('Invalid product data');
        }
        
        // Update page elements with product data
        updateProductPage(product);
        loadCompleteTheLook(product.id);
    } catch (error) {
        console.error('Failed to load product:', error);
        document.body.innerHTML = '<div class="container text-center py-5"><h2>Product not found</h2><a href="products.html" class="btn btn-primary">Back to Products</a></div>';
    }
}

function imageUrl(path) {
    if (!path) return 'https://placehold.co/400x300?text=No+Image';
    const srv = window.API_CONFIG?.getServerUrl() || 'http://localhost:8000';
    return path.startsWith('http') ? path : `${srv}${path.startsWith('/') ? '' : '/'}${path}`;
}

function updateProductPage(product) {
    try {
        // Main image — use thumbnail first, fallback to first image
        const mainImage = document.getElementById('mainImage');
        if (mainImage) {
            const imgSrc = product.thumbnail || (product.images?.[0]) || null;
            mainImage.src = imageUrl(imgSrc);
            mainImage.alt = product.name;
        }

        // Product title
        const titleEl = document.querySelector('h1');
        if (titleEl) titleEl.textContent = product.name || '';

        // Price — guard against NaN crash
        const priceEl = document.querySelector('.display-5, h2.text-primary');
        if (priceEl) {
            const p = parseFloat(product.base_price);
            priceEl.textContent = `$${(isNaN(p) ? 0 : p).toFixed(2)}`;
        }

        // Description
        const descEl = document.getElementById('productDescription');
        if (descEl) descEl.textContent = product.description || '';
    } catch (e) {
        console.warn('updateProductPage partial error (non-fatal):', e);
    }

    // Quantity reset
    const qtyInput = document.getElementById('quantityInput');
    if (qtyInput) qtyInput.value = 1;

    // Set data-id on buttons (always runs, even if above crashes)
    document.querySelectorAll('button').forEach(btn => {
        const txt = btn.textContent.trim();
        if (txt.includes('Add to Cart') || txt.includes('Buy Now')) {
            btn.setAttribute('data-id', String(product.id || getProductIdFromUrl()));
        }
    });
}

function buyNow(productId) {
    if (!Auth.requireAuth()) return;
    const qty = parseInt(document.getElementById('quantityInput')?.value || 1);
    fetch(`${Auth.getBaseUrl()}/cart/items`, {
        method: "POST",
        headers: Auth.getHeaders(),
        body: JSON.stringify({ product_id: parseInt(productId), quantity: qty })
    })
    .then(r => {
        if (r.status === 401) { Auth.clearAuth(); window.location.href = "login.html"; return; }
        if (!r.ok) throw new Error('Failed to add to cart');
        return r.json();
    })
    .then(d => { window.location.href = "checkot.html"; })
    .catch(e => { console.error(e); alert("Error: " + e.message); });
}

async function loadCompleteTheLook(excludeId) {
    const baseUrl = window.API_CONFIG?.getBaseUrl() || 'http://localhost:8000/api';
    const serverUrl = window.API_CONFIG?.getServerUrl() || 'http://localhost:8000';
    const grid = document.getElementById('completeLookGrid');
    if (!grid) return;

    try {
        let res = await fetch(`${baseUrl}/products?per_page=10`, {
            headers: { "Accept": "application/json" }
        });
        if (!res.ok) return;
        let data = await res.json();
        let products = data.data?.items || data.data || [];
        // Filter out current product, take up to 4
        let picks = products.filter(p => p.id != excludeId).slice(0, 4);

        if (picks.length === 0) {
            // Fallback: just show first 4 (including current, no filter match)
            picks = products.slice(0, 4);
        }

        grid.innerHTML = picks.map(p => {
            let imgSrc = p.thumbnail || (p.images?.[0]) || '';
            if (imgSrc && !imgSrc.startsWith('http')) {
                const clean = imgSrc.startsWith('/') ? imgSrc.substring(1) : imgSrc;
                imgSrc = `${serverUrl}/${clean}`;
            }
            if (!imgSrc) imgSrc = 'https://placehold.co/400x300?text=No+Image';

            return `
            <div class="col-lg-3 col-md-6">
                <div class="card border-0" style="cursor:pointer" onclick="window.location.href='productsdetils.html?id=${p.id}'">
                    <div class="product-thumb rounded-4">
                        <img src="${imgSrc}" class="card-img-top rounded-4" alt="${p.name || 'Product'}" loading="lazy" />
                        <div class="product-overlay">
                            <p class="overlay-price">$${parseFloat(p.base_price || p.price || 0).toFixed(2)}</p>
                            <p class="overlay-desc">${p.description ? p.description.substring(0, 50) : ''}</p>
                        </div>
                    </div>
                    <div class="card-body px-0">
                        <h6 class="fw-bold mb-1">${p.name || ''}</h6>
                    </div>
                </div>
            </div>`;
        }).join('');
    } catch (e) {
        console.error('Complete the Look error:', e);
    }
}

function addToCart(productId) {
    if (!Auth.requireAuth()) return;
    fetch(`${Auth.getBaseUrl()}/cart/items`, {
        method: "POST",
        headers: Auth.getHeaders(),
        body: JSON.stringify({
            product_id: parseInt(productId),
            quantity: parseInt(document.getElementById('quantityInput')?.value || 1)
        })
    })
    .then(response => {
        if (response.status === 401) { Auth.clearAuth(); window.location.href = "login.html"; return; }
        if (!response.ok) throw new Error('Failed to add to cart');
        return response.json();
    })
    .then(data => {
        window.location.href = "cart.html";
    })
    .catch(error => {
        console.error("Error adding to cart:", error);
        alert("Error: " + error.message);
    });
}