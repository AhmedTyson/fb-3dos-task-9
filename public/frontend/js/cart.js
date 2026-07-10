// Global configuration and auth
const getBaseUrl = () => window.API_CONFIG?.getBaseUrl() || 'http://localhost:8000/api';
const getServerUrl = () => window.API_CONFIG?.getServerUrl() || 'http://localhost:8000';

let deleteItemId = null;
let deleteProductId = null;

// Use Auth module; fallback to direct localStorage read if Auth not loaded
function getToken() {
    return window.Auth ? Auth.getToken() : (localStorage.getItem("token") || localStorage.getItem("userToken") || localStorage.getItem("auth_token"));
}
function getHeaders() {
    return window.Auth ? Auth.getHeaders() : {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "Authorization": `Bearer ${getToken()}`
    };
}

function getImageUrl(rawImg) {
    if (!rawImg) return 'https://placehold.co/400x300?text=No+Image';
    if (rawImg.startsWith('http://') || rawImg.startsWith('https://')) {
        return rawImg;
    }
    const cleanName = rawImg.trim().replace(/^\//, '');
    return `${getServerUrl()}${cleanName.startsWith('/') ? '' : '/'}${cleanName}`;
}

async function getCartContents() {
    const cartBody = document.getElementById("cartbody"); 
    const token = getToken();
    if (!token) {
        if (cartBody) cartBody.innerHTML = '<p class="text-danger text-sm p-3">Please log in to view your cart.</p>';
        return;
    }

    try {
        let response = await fetch(`${getBaseUrl()}/cart`, {
            method: "GET",
            headers: getHeaders()
        });

        if (response.status === 401) {
            if (window.Auth) Auth.clearAuth();
            window.location.href = "login.html";
            return;
        }

        if (!response.ok) throw new Error(`Error: ${response.status}`);

        let data = await response.json();
        console.log("Cart data:", data);

        let cartItems = [];
        if (data?.data?.items) {
            cartItems = data.data.items;
        }

        displayCart(cartItems);
    } catch (error) {
        console.error("Error fetching cart:", error);
        displayCart([]);
    }
}

function displayCart(items) {
    const cartBody = document.getElementById("cartbody"); 
    const emptyCartSection = document.getElementById("emptycart"); 
    const cartContentSection = document.getElementById("cartcontent"); 
    const summarySubtotal = document.getElementById("summary-subtotal");
    const summaryTotal = document.getElementById("summary-total");

    let cartTotal = 0;

    if (!items || items.length === 0) {
        if (emptyCartSection) emptyCartSection.classList.remove("d-none");
        if (cartContentSection) cartContentSection.classList.add("d-none");
        if (cartBody) cartBody.innerHTML = "";
        
        if (summarySubtotal) summarySubtotal.textContent = "0.00 LE";
        if (summaryTotal) summaryTotal.textContent = "0.00 LE";
        return;
    }

    if (emptyCartSection) emptyCartSection.classList.add("d-none");
    if (cartContentSection) cartContentSection.classList.remove("d-none");

    let cartHTML = ""; 

    items.forEach((item) => {
        let price = 0;
        if (item.product) {
            price = item.product.base_price !== undefined ? Number(item.product.base_price) : Number(item.product.price || 0);
        } else {
            price = item.price !== undefined ? Number(item.price) : 0;
        }
        
        let itemTotal = price * item.quantity;
        cartTotal += itemTotal;

        let formattedPrice = !isNaN(itemTotal) ? itemTotal.toFixed(2) : "0.00"; 
        let productName = item.product ? item.product.name : "Unknown Product";
        let productSize = item.product?.size || 'Premium Quality Item';
        
        let rawImgPath = '';
        if (item.product) {
            if (item.product.thumbnail) {
                rawImgPath = item.product.thumbnail;
            } else if (item.product.images && Array.isArray(item.product.images) && item.product.images.length > 0) {
                rawImgPath = item.product.images[0];
            }
        }

        let placeholderSrc = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='400' height='300' viewBox='0 0 400 300'><rect width='100%' height='100%' fill='%23f0f0f0'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='14' fill='%23999'>Loading...</text></svg>";

        let productId = item.product_id || (item.product ? item.product.id : null);
        let cartItemId = item.id;

        cartHTML += `
            <div class="card cart-item-card mb-3 p-3 border border-light-subtle rounded-3">
                <div class="row g-0 align-items-center">
                    <div class="col-md-3 p-1 text-center">
                        <img src="${placeholderSrc}" data-secure-cart-thumb="${rawImgPath}" class="secure-cart-img img-fluid rounded-3" alt="${productName}" style="max-height: 110px; object-fit: cover; width: 100%;">
                    </div>
                    <div class="col-md-7 leftside px-3">
                        <div class="card-body p-0">
                            <h5 class="card-title fw-bold fs-5 text-dark mb-1">${productName}</h5>
                            <p class="text-muted small mb-3">${productSize}</p>
                            <div class="quantity d-flex align-items-center">
                                <button class="btn btn-sm border-0" onclick="updateQuantity(${productId}, ${item.quantity - 1}, ${cartItemId})">
                                    <i class="fa-solid fa-minus text-secondary"></i> <span class="d-none">-</span>
                                </button>
                                <span class="px-3 fw-bold text-dark">${item.quantity}</span>
                                <button class="btn btn-sm border-0" onclick="updateQuantity(${productId}, ${item.quantity + 1}, ${cartItemId})">
                                    <i class="fa-solid fa-plus text-secondary"></i> <span class="d-none">+</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 rightside d-flex flex-column justify-content-between p-2 align-items-end" style="min-height: 110px;">
                        <a href="javascript:void(0)" class="delete text-muted fs-5" onclick="showDeleteModal(${cartItemId}, ${productId})" title="Remove item">
                            <i class="fa-solid fa-trash-can"></i> <span class="d-none">Remove</span>
                        </a>
                        <h6 class="productprice mb-0">${formattedPrice} LE</h6>
                    </div>
                </div>
            </div>
        `;
    });

    if (cartBody) cartBody.innerHTML = cartHTML;
    
    document.querySelectorAll('.secure-cart-img').forEach(img => {
        let thumbPath = img.getAttribute('data-secure-cart-thumb');
        loadSecureCartImage(img, thumbPath);
    });

    if (summarySubtotal) summarySubtotal.textContent = `${cartTotal.toFixed(2)} LE`;
    if (summaryTotal) summaryTotal.textContent = `${cartTotal.toFixed(2)} LE`;
}

function loadSecureCartImage(imgElement, filename) {
    if (!filename) {
        imgElement.src = 'https://placehold.co/400x300?text=No+Image';
        return;
    }
    if (filename.startsWith('http://') || filename.startsWith('https://')) {
        imgElement.src = filename;
        return;
    }
    const cleanName = filename.startsWith('/') ? filename.substring(1) : filename;
    imgElement.src = `${getServerUrl()}/${cleanName}`;
}

function showDeleteModal(cartItemId, productId) {
    deleteItemId = cartItemId; 
    deleteProductId = productId;
    const modalElement = document.getElementById('deletemessage');
    if (modalElement) {
        const deleteModal = new bootstrap.Modal(modalElement);
        deleteModal.show();
    }
}

async function confirmDeleteProduct() {
    try {
        let response = await fetch(`${getBaseUrl()}/cart/items/${deleteItemId}`, {
            method: "DELETE",
            headers: getHeaders()
        });

        if (response.status === 401) {
            if (window.Auth) Auth.clearAuth();
            window.location.href = "login.html";
            return;
        }

        if (response.ok) {
            const modalElement = document.getElementById('deletemessage');
            if (modalElement) {
                const modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) modalInstance.hide();
            }
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) backdrop.remove();
            document.body.style.overflow = 'auto';

            getCartContents(); 
        } else {
            let errBody = await response.json().catch(() => ({}));
            alert(errBody.message || 'Failed to remove item');
        }
    } catch (error) {
        console.error("Error deleting product:", error);
        alert('Network error removing item');
    }
}

document.addEventListener("DOMContentLoaded", () => {
    getCartContents();
});

async function updateQuantity(productId, newQuantity, cartItemId) {
    if (newQuantity < 1) {
        showDeleteModal(cartItemId, productId);
        return;
    }

    try {
        let response = await fetch(`${getBaseUrl()}/cart/items/${cartItemId}`, { 
            method: "PUT", 
            headers: getHeaders(),
            body: JSON.stringify({ quantity: parseInt(newQuantity) })
        });

        if (response.status === 401) {
            if (window.Auth) Auth.clearAuth();
            window.location.href = "login.html";
            return;
        }

        if (response.ok) {
            getCartContents(); 
        } else {
            let errBody = await response.json().catch(() => ({}));
            alert(errBody.message || 'Failed to update quantity');
        }
    } catch (error) {
        console.error("Error updating quantity:", error);
        alert('Network error updating quantity');
    }
}