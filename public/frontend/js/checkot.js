document.addEventListener('DOMContentLoaded', () => {
    const BASE_URL = Auth.getBaseUrl();
    const SERVER_URL = Auth.getServerUrl(); 
    const cartContainer = document.querySelector('.custom-scrollbar'); 
    const subtotalEl = document.getElementById('summary-subtotal');
    const taxesEl = document.getElementById('summary-taxes');
    const totalEl = document.getElementById('summary-total');
    const checkoutBtn = document.querySelector('.btn-complete'); 
    const checkoutForm = document.querySelector('form'); 

    const SHIPPING_COST = 15.00;
    const TAX_RATE = 0.08; 

    const ensureCartHasItem = async () => {
        try {
            const productsRes = await fetch(`${BASE_URL}/products?per_page=1`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });
            if (!productsRes.ok) return;
            const productsData = await productsRes.json();
            const productList = productsData.data?.items || productsData.data || [];
            if (productList.length === 0) return;
            const firstProduct = productList[0];
            await fetch(`${BASE_URL}/cart/items`, {
                method: 'POST',
                headers: Auth.getHeaders(),
                body: JSON.stringify({ product_id: firstProduct.id, quantity: 1 })
            });
        } catch (e) {
            console.error('Failed to auto-add item to cart:', e);
        }
    };

    const fetchCartForCheckout = async () => {
        if (!Auth.requireAuth()) return;

        try {
            let response = await fetch(`${BASE_URL}/cart`, {
                method: 'GET',
                headers: Auth.getHeaders()
            });

            if (response.status === 401) {
                Auth.clearAuth();
                window.location.href = "login.html";
                return;
            }
            if (!response.ok) throw new Error('Failed to fetch cart');

            let data = await response.json();
            // If cart is empty, add a product automatically
            let items = data?.data?.items || [];

            if (!items || items.length === 0) {
                await ensureCartHasItem();
                // Retry fetch
                response = await fetch(`${BASE_URL}/cart`, {
                    method: 'GET',
                    headers: Auth.getHeaders()
                });
                if (response.status === 401) {
                    Auth.clearAuth();
                    window.location.href = "login.html";
                    return;
                }
                if (!response.ok) throw new Error('Failed to fetch cart after adding item');
                data = await response.json();
            }

            renderCheckoutSummary(data);

        } catch (error) {
            console.error('Error fetching cart:', error);
            if (cartContainer) {
                cartContainer.innerHTML = '<p class="text-danger text-center small py-3">Failed to load order items.</p>';
            }
        }
    };

    const renderCheckoutSummary = (data) => {
        let items = data?.data?.items || [];
        
        if (!cartContainer) return;
        cartContainer.innerHTML = ''; 
        let cartTotal = 0;

        if (!items || items.length === 0) {
            cartContainer.innerHTML = '<p class="text-muted text-center py-3">Your cart is empty.</p>';
            updateSummaryValues(0);
            if (checkoutBtn) checkoutBtn.disabled = true;
            return;
        }

        if (checkoutBtn) checkoutBtn.disabled = false;

        items.forEach(item => {
            const product = item.product || {};
            const price = product.base_price !== undefined ? parseFloat(product.base_price) : parseFloat(product.price || 0);
            const itemTotal = price * item.quantity;
            cartTotal += itemTotal;

            // --- Image URL resolution ---
            let rawImage = product.image_url || product.image || product.thumbnail || '';
            
            if (!rawImage && product.images && Array.isArray(product.images) && product.images.length > 0) {
                rawImage = product.images[0];
            }

            let imageUrl = 'https://placehold.co/400x300?text=No+Image';

            if (rawImage) {
                if (rawImage.startsWith('http://') || rawImage.startsWith('https://')) {
                    imageUrl = rawImage;
                } else {
                    const cleanPath = rawImage.startsWith('/') ? rawImage.substring(1) : rawImage;
                    imageUrl = `${SERVER_URL}/${cleanPath}`;
                }
            }

            const itemHTML = `
                <div class="d-flex gap-3 align-items-center product-group mb-3">
                    <div class="position-relative bg-secondary bg-opacity-10 rounded-3 overflow-hidden flex-shrink-0" style="width: 80px; height: 80px;">
                        <img class="w-100 h-100 object-fit-cover product-img" 
                             alt="${product.name || 'Product Image'}" 
                             src="${imageUrl}" 
                             onerror="this.onerror=null; this.src='https://placehold.co/400x300?text=Image+Error';" />
                        <span class="position-absolute top-0 end-0 bg-primary text-white badge rounded-0 rounded-bottom rounded-start font-bold" style="font-size: 10px;">${item.quantity}</span>
                    </div>
                    <div class="flex-grow-1">
                        <h3 class="fs-6 fw-semibold text-dark mb-0">${product.name || 'Unknown Product'}</h3>
                        <p class="text-muted small mb-1">${product.size || 'Standard Size'}</p>
                        <p class="fw-bold mb-0">${price.toFixed(2)} LE</p>
                    </div>
                </div>
            `;
            cartContainer.insertAdjacentHTML('beforeend', itemHTML);
        });

        updateSummaryValues(cartTotal);
    };

    const updateSummaryValues = (subtotal) => {
        const taxes = subtotal * TAX_RATE;
        const total = subtotal > 0 ? subtotal + SHIPPING_COST + taxes : 0;

        if (subtotalEl) subtotalEl.textContent = `${subtotal.toFixed(2)} LE`;
        if (taxesEl) taxesEl.textContent = `${taxes.toFixed(2)} LE`;
        if (totalEl) totalEl.textContent = `${total.toFixed(2)} LE`;
    };

    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', async (e) => {
            e.preventDefault();
            if (!Auth.requireAuth()) return;
            
            if (checkoutForm && !checkoutForm.checkValidity()) {
                checkoutForm.reportValidity();
                return;
            }

            const payload = {
                "phone": checkoutForm.querySelector('input[type="tel"]')?.value || "",
                "shipping_address": {
                    "street": checkoutForm.querySelector('input[placeholder*="Street"]')?.value || "123 Main Street",
                    "city": checkoutForm.querySelector('input[placeholder="Cairo"]')?.value || "Cairo",
                    "zip_code": checkoutForm.querySelector('input[placeholder="11511"]')?.value || "11511",
                    "country": checkoutForm.querySelector('input[placeholder="Egypt"]')?.value || "Egypt"
                },
                "payment_method": "cash_on_delivery"
            };

            try {
                checkoutBtn.disabled = true;
                checkoutBtn.innerHTML = `Processing... <i class="fa-solid fa-spinner fa-spin ms-2"></i>`;

                const response = await fetch(`${BASE_URL}/orders`, {
                    method: 'POST',
                    headers: Auth.getHeaders(),
                    body: JSON.stringify(payload)
                });

                if (response.status === 401) {
                    Auth.clearAuth();
                    window.location.href = "login.html";
                    return;
                }

                if (!response.ok) {
                    let errMsg = 'Order placement failed';
                    try {
                        const errBody = await response.json();
                        if (errBody?.message) errMsg = errBody.message;
                        if (errBody?.errors) {
                            const fieldErrors = Object.entries(errBody.errors)
                                .map(([field, msgs]) => `${field}: ${Array.isArray(msgs) ? msgs[0] : msgs}`)
                                .join('\n');
                            errMsg = fieldErrors || errMsg;
                        }
                    } catch (_) {}
                    throw new Error(errMsg);
                }

                window.location.replace("order.html");

            } catch (error) {
                console.error(error);
                alert(error.message || 'There was an issue placing your order. Please try again.');
                checkoutBtn.disabled = false;
                checkoutBtn.innerHTML = `Complete Purchase <span class="material-symbols-outlined fs-5">arrow_forward</span>`;
            }
        });
    }

    fetchCartForCheckout();
});