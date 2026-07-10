/**
 * API CONFIGURATION
 * Based on TechAccessories.json Postman Collection
 */
const API_BASE_URL = () => window.API_CONFIG?.getBaseUrl() || 'http://localhost:8000/api';

// Utility to format currency
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(amount);
};

// Utility to format date
const formatDate = (dateString) => {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
};

/**
 * Fetch wrapper handling headers and authorization
 */
async function fetchFromApi(endpoint, options = {}) {
    const token = localStorage.getItem('token') || localStorage.getItem('userToken') || localStorage.getItem('auth_token');
    if (!token) {
        throw new Error('No valid token provided. Please log in.');
    }

    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${token}`,
        ...options.headers
    };

    try {
        const response = await fetch(`${API_BASE_URL()}${endpoint}`, { ...options, headers });
        if (!response.ok) {
            throw new Error(`API Error: ${response.status} ${response.statusText}`);
        }
        return await response.json();
    } catch (error) {
        console.error(`Fetch failed for ${endpoint}:`, error);
        throw error;
    }
}

/**
 * Renders orders array into the table
 */
function renderOrders(orders) {
    const tbody = document.getElementById('orders-tbody');
    tbody.innerHTML = ''; // Clear loading text
    
    orders.forEach(order => {
        let statusBadge = '';
        const status = order.status?.toLowerCase() || 'processing';
        if (status === 'delivered' || status === 'completed') {
            statusBadge = `<span class="badge bg-success-subtle text-success rounded-pill px-3 py-2"><i class="fa-solid fa-circle me-1 fs-6 align-middle"></i>Delivered</span>`;
        } else if (status === 'shipped') {
            statusBadge = `<span class="badge bg-info-subtle text-info rounded-pill px-3 py-2"><i class="fa-solid fa-circle me-1 fs-6 align-middle fa-pulse"></i>Shipped</span>`;
        } else {
            statusBadge = `<span class="badge bg-light text-dark border rounded-pill px-3 py-2"><i class="fa-solid fa-circle me-1 fs-6 align-middle"></i>Processing</span>`;
        }

        const imageUrl = order.items && order.items[0] && order.items[0].product?.image_url 
            ? order.items[0].product.image_url 
            : 'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?w=150&q=80';

        const row = document.createElement('tr');
        row.setAttribute('data-order-id', order.id);
        row.innerHTML = `
            <td class="px-3 py-3">
                <span class="fw-bold text-primary">#LG-${order.id}-X</span>
            </td>
            <td class="px-3 py-3 text-secondary">${formatDate(order.created_at)}</td>
            <td class="px-3 py-3">
                <div class="d-flex align-items-center">
                    <div class="rounded border overflow-hidden bg-light" style="width: 40px; height: 40px;">
                        <img class="w-100 h-100 object-fit-cover" src="${imageUrl}" alt="Product" />
                    </div>
                    ${(order.items?.length > 1) ? `
                    <div class="rounded border bg-secondary-subtle text-dark d-flex align-items-center justify-content-center fw-medium" style="width: 40px; height: 40px; margin-left: -10px; z-index: 2; border: 2px solid #fff !important; font-size: 12px;">
                        +${order.items.length - 1}
                    </div>` : ''}
                </div>
            </td>
            <td class="px-3 py-3">${statusBadge}</td>
            <td class="px-3 py-3 fw-bold">${formatCurrency(order.total || order.total_amount || 0)}</td>
            <td class="px-3 py-3 text-end">
                <button class="btn btn-sm btn-outline-secondary rounded-pill me-1 download-pdf-btn" data-order-id="${order.id}" title="Download Invoice">
                    <i class="fa-solid fa-file-pdf"></i> View
                </button>
                <a href="products.html" class="btn btn-sm btn-outline-primary rounded-pill">Reorder</a>
            </td>
        `;
        tbody.appendChild(row);
    });

    // Attach PDF download listeners
    document.querySelectorAll('.download-pdf-btn').forEach(btn => {
        btn.addEventListener('click', downloadOrderPdf);
    });
}

async function downloadOrderPdf(e) {
    const orderId = e.currentTarget.getAttribute('data-order-id');
    const btn = e.currentTarget;
    btn.disabled = true;
    const origHtml = btn.innerHTML;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>';
    try {
        const token = localStorage.getItem('token') || localStorage.getItem('userToken') || localStorage.getItem('auth_token');
        const res = await fetch(`${API_BASE_URL()}/orders/${orderId}/print-file`, {
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });
        if (res.status === 401) {
            if (window.Auth) Auth.clearAuth();
            window.location.href = 'login.html';
            return;
        }
        if (!res.ok) {
            const errBody = await res.json().catch(() => ({}));
            throw new Error(errBody.message || `HTTP ${res.status}`);
        }
        const blob = await res.blob();
        const blobUrl = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = blobUrl;
        a.download = `order-${orderId}.pdf`;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        window.URL.revokeObjectURL(blobUrl);
    } catch (err) {
        console.error('PDF download failed:', err);
        alert('Failed to download invoice: ' + err.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}

/**
 * MODULE 4: ORDERS
 */
async function loadOrders() {
    try {
        const response = await fetchFromApi('/orders?page=1&per_page=10');
        const orders = response.data?.items || response.data || []; 
        if (!orders || orders.length === 0) {
            document.getElementById('orders-tbody').innerHTML = `<tr><td colspan="6" class="p-5 text-center text-secondary">You have no recent orders.</td></tr>`;
            return;
        }
        renderOrders(orders);
    } catch (error) {
        console.error("Failed to load orders:", error);
        // Show error state instead of demo data
        document.getElementById('orders-tbody').innerHTML = `<tr><td colspan="6" class="p-5 text-center text-danger">Failed to load orders. Please log in again.</td></tr>`;
    }
}

/**
 * MODULE 2: PRODUCTS
 */
async function loadProducts() {
    const grid = document.getElementById('products-grid');
    try {
        const response = await fetchFromApi('/products?page=1');
        const products = response.data?.items || response.data || [];
        if (!products || products.length === 0) throw new Error("No products found");
        renderProductsGrid(products.slice(0, 2));
    } catch (error) {
        console.error("Failed to load products:", error);
        grid.innerHTML = `<div class="col-12 p-5 text-center text-secondary">Failed to load recommendations.</div>`;
    }
}

function renderProductsGrid(productsArr) {
    const grid = document.getElementById('products-grid');
    grid.innerHTML = ''; 

    // 1. Promo block
    const promoBlock = document.createElement('div');
    promoBlock.className = 'col-lg-6 mb-3';
    promoBlock.innerHTML = `
        <div class="card border-0 h-100 p-4 d-flex flex-column justify-content-between position-relative overflow-hidden shadow-sm text-white" style="background: linear-gradient(135deg, #1e3c72, #2a5298);">
            <div style="z-index: 2;">
                <span class="badge bg-warning text-dark mb-2 fw-bold">EXCLUSIVES</span>
                <h3 class="fw-bold mb-1 text-white">The Apex Collection</h3>
                <p class="text-white-50 small" style="max-width: 300px;">Early access to our upcoming premium winter selection starts tomorrow.</p>
            </div>
            <div class="mt-4" style="z-index: 2;">
                <button class="btn btn-light rounded-pill px-4 py-2 fw-semibold text-primary" id="notifyMeBtn">Notify Me</button>
            </div>
            <div class="position-absolute end-0 bottom-0 opacity-25 p-3" style="transform: rotate(12deg); font-size: 120px;">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>
        </div>
    `;
    grid.appendChild(promoBlock);

    // 2. Product recommendation cards
    productsArr.forEach(product => {
        const imageUrl = product.images && product.images[0] ? product.images[0] : 'https://via.placeholder.com/400x300?text=Product';
        
        const productCard = document.createElement('div');
        productCard.className = 'col-md-6 col-lg-3 mb-3';
        productCard.innerHTML = `
            <div class="card h-100 p-3 border-0 shadow-sm" style="transition: transform 0.2s; cursor: pointer; background: #fff;">
                <div class="bg-light rounded mb-2 overflow-hidden" style="height: 140px;">
                    <img class="w-100 h-100 object-fit-cover" src="${imageUrl}" alt="${product.name}" />
                </div>
                <h6 class="fw-bold text-dark text-truncate mb-1" title="${product.name}">${product.name}</h6>
                <p class="text-primary fw-semibold mb-0">${formatCurrency(product.base_price || product.price || 0)}</p>
            </div>
        `;
        const cardEl = productCard.querySelector('.card');
        cardEl.addEventListener('mouseenter', function() { this.style.transform = 'scale(1.03)'; });
        cardEl.addEventListener('mouseleave', function() { this.style.transform = 'scale(1)'; });
        cardEl.addEventListener('click', () => { window.location.href = `productsdetils.html?id=${product.id}`; });
        
        grid.appendChild(productCard);
    });

    // 3. Gold membership bar
    const membershipBlock = document.createElement('div');
    membershipBlock.className = 'col-12 mt-2';
    membershipBlock.innerHTML = `
        <div class="card border-0 p-4 shadow-sm bg-white">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle d-flex align-items-center justify-content-center text-primary" style="width: 60px; height: 60px; flex-shrink: 0;">
                    <i class="fa-solid fa-crown fs-3"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Gold Membership</h5>
                    <p class="text-secondary mb-0 small">You're 200 points away from unlocking free global express shipping.</p>
                </div>
            </div>
        </div>
    `;
    grid.appendChild(membershipBlock);
}

// Application Initialization
document.addEventListener('DOMContentLoaded', () => {
    loadOrders();
    loadProducts();

    // Filter button — scroll to orders table
    const filterBtn = document.getElementById('filterOrdersBtn');
    if (filterBtn) {
        filterBtn.addEventListener('click', () => {
            document.querySelector('.table-responsive')?.scrollIntoView({ behavior: 'smooth' });
        });
    }

    // New Order button — go to products
    const newOrderBtn = document.getElementById('newOrderBtn');
    if (newOrderBtn) {
        newOrderBtn.addEventListener('click', () => {
            window.location.href = 'products.html';
        });
    }

    // Notify Me — simple alert
    document.addEventListener('click', (e) => {
        if (e.target.closest('#notifyMeBtn')) {
            alert('You will be notified when the Apex Collection launches.');
        }
    });

    // Order row click → show order number, then go to products
    const tbody = document.getElementById('orders-tbody');
    if (tbody) {
        tbody.addEventListener('click', (e) => {
            const row = e.target.closest('tr[data-order-id]');
            if (row && !e.target.closest('button,a')) {
                const orderNum = row.querySelector('.fw-bold.text-primary')?.textContent || `#LG-${row.getAttribute('data-order-id')}`;
                alert(`Order ${orderNum}\nThank you for your purchase! Track your order status above.`);
            }
        });
    }
});