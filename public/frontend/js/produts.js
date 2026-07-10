document.addEventListener('DOMContentLoaded', () => {
    // Initialize modules
    const baseUrl = window.API_CONFIG.getBaseUrl();
    const serverUrl = window.API_CONFIG.getServerUrl();
    let allProducts = [];

    async function getCategories() {
        try {
            let res = await fetch(`${baseUrl}/categories`, {
                headers: { "Accept": "application/json" }
            });
            let data = await res.json();
            let select = document.getElementById('category-filter');
            if (select) { 
                select.innerHTML = '<option value="">All Categories</option>';
                data.data.forEach(c => {
                    select.innerHTML += `<option value="${c.id}">${c.name}</option>`;
                });
            }
        } catch (e) {
            console.error("Categories fail:", e);
        }
    }

    let currentPage = 1;
    let currentFetchCtrl = null;
    let searchTimeout = null;

    async function getAllProducts() {
        if (currentFetchCtrl) currentFetchCtrl.abort();
        currentFetchCtrl = new AbortController();

        const gridView = document.getElementById('grid-view');
        if (gridView) gridView.innerHTML = '<div style="text-align:center; padding:40px; grid-column: 1/-1;">Loading...</div>';

        try {
            const params = new URLSearchParams();
            params.set('page', currentPage);
            params.set('per_page', 12);
            
            const search = document.getElementById('search-input')?.value.trim();
            if (search) params.set('search', search);
            
            const cat = document.getElementById('category-filter')?.value;
            if (cat) params.set('category_id', cat);
            
            const min = document.getElementById('min-price')?.value;
            if (min) params.set('min_price', min);
            
            const max = document.getElementById('max-price')?.value;
            if (max) params.set('max_price', max);

            const sort = document.getElementById('sort-select')?.value;
            if (sort) params.set('sort', sort);

            let response = await fetch(`${baseUrl}/products?${params.toString()}`, {
                method: 'GET',
                headers: { "Accept": "application/json" },
                signal: currentFetchCtrl.signal
            });
            let data = await response.json();

            allProducts = data.data.items || [];
            
            let resText = document.querySelector('.results-text strong');
            if (resText) resText.textContent = data.data.pagination ? data.data.pagination.total : allProducts.length;

            displayProducts();
            initProductCardClicks();
            if (data.data.pagination) {
                renderPagination(data.data.pagination);
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error("Failed to load products:", error);
                if (gridView) gridView.innerHTML = '<div style="color:red; text-align:center; padding:40px; grid-column: 1/-1;">Error loading products.</div>';
            }
        }
    }

    function renderPagination(p) {
        const container = document.getElementById('pagination-container');
        if (!container) return;
        if (p.last_page <= 1) {
            container.innerHTML = '';
            return;
        }

        let html = '';
        if (p.current_page > 1) {
            html += `<button class="page-btn" data-page="${p.current_page - 1}"><span class="material-symbols-outlined">chevron_left</span></button>`;
        }
        
        for (let i = Math.max(1, p.current_page - 2); i <= Math.min(p.last_page, p.current_page + 2); i++) {
            html += `<button class="page-btn ${i === p.current_page ? 'active' : ''}" data-page="${i}">${i}</button>`;
        }

        if (p.current_page < p.last_page) {
            html += `<button class="page-btn" data-page="${p.current_page + 1}"><span class="material-symbols-outlined">chevron_right</span></button>`;
        }

        container.innerHTML = html;
        container.querySelectorAll('button[data-page]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                currentPage = parseInt(e.currentTarget.getAttribute('data-page'));
                getAllProducts();
                window.scrollTo({top: 0, behavior: 'smooth'});
            });
        });
    }

    function getImageUrl(thumbnail) {
        if (!thumbnail) return 'https://placehold.co/400x300?text=No+Image';
        if (thumbnail.startsWith('http')) return thumbnail;
        return `${serverUrl}${thumbnail.startsWith('/') ? '' : '/'}${thumbnail}`;
    }

    // Secure image loading function to handle ngrok/CORS issues
    async function loadSecureImage(imgElement, filename) {
        let cleanName = filename.trim().replace(/^\//, '');
        if (cleanName.includes('?')) cleanName = cleanName.split('?')[0];

        // Try multiple possible paths
        let pathsToTry = [
            `${serverUrl}/uploads/${cleanName}`,
            `${serverUrl}/storage/${cleanName}`,
            `${serverUrl}/${cleanName}`
        ];

        for (let url of pathsToTry) {
            try {
                let res = await fetch(url);
                if (res.ok) {
                    let blob = await res.blob();
                    let objectURL = URL.createObjectURL(blob);
                    imgElement.src = objectURL;
                    return;
                }
            } catch (err) {
                console.clear();
            }
        }
        // If all attempts fail, use placeholder
        imgElement.src = 'https://placehold.co/400x300?text=Image+Not+Found';
    }

    function displayProducts() {
        let gridCartona = ``;
        let listCartona = ``;

        for (let i = 0; i < allProducts.length; i++) {
            let product = allProducts[i];
            
            let placeholderSrc = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='400' height='300' viewBox='0 0 400 300'><rect width='100%' height='100%' fill='%23f0f0f0'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' font-family='sans-serif' font-size='16' fill='%23999'>Loading Image...</text></svg>";

            const imgUrl = getImageUrl(product.thumbnail);

            gridCartona += `
                <div class="product-card" data-price="${product.base_price}" data-category-id="${product.category_id}" data-product-id="${product.id}">
                    <div class="card-image-wrap">
                        <a href="productsdetils.html?id=${product.id}"><img src="${imgUrl}" alt="${product.name}" loading="lazy"/></a>
                        <button class="favorite-btn top-right"><span class="material-symbols-outlined">favorite</span></button>
                        <div class="add-to-cart-wrap">
                            <button class="primary-btn full-width add-to-cart-btn" data-id="${product.id}" ${!product.in_stock ? "disabled style='background: #ccc; cursor: not-allowed;'" : ""}>
                                <span class="material-symbols-outlined">add_shopping_cart</span> 
                                ${product.in_stock ? 'Add to Cart' : 'Unavailable'}
                            </button>
                        </div>
                    </div>
                    <div class="card-content">
                        <div class="card-header">
                            <h3><a href="productsdetils.html?id=${product.id}" style="color: inherit; text-decoration: none;">${product.name}</a></h3>
                            <span class="price">$${parseFloat(product.base_price).toFixed(2)}</span>
                        </div>
                        <p class="description">${product.description || ''}</p>
                        <div style="font-size: 12px; color: var(--text-outline); display: flex; justify-content: space-between; align-items: center; margin-top: 12px;">
                            <span>Sizes: ${product.size || 'N/A'}</span>
                            <span style="color: ${product.in_stock ? 'green' : 'red'}; font-weight: bold;">
                                ${product.in_stock ? 'In Stock' : 'Out of Stock'}
                            </span>
                        </div>
                    </div>
                </div>
            `;

            listCartona += `
                <tr data-price="${product.base_price}" data-category-id="${product.category_id}" data-product-id="${product.id}">
                    <td><img src="${imgUrl}" class="table-img" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;"></td>
                    <td>
                        <a href="productsdetils.html?id=${product.id}" style="color: inherit; text-decoration: none;"><strong>${product.name}</strong></a><br>
                        <small style="color: var(--text-variant)">${product.description ? product.description.substring(0, 40) : ''}...</small>
                    </td>
                    <td style="color: var(--text-variant);">ID: ${product.category_id}</td>
                    <td class="table-price">$${parseFloat(product.base_price).toFixed(2)}</td>
                    <td style="color: ${product.in_stock ? 'green' : 'red'}; font-weight: bold; font-size: 14px;">
                        ${product.in_stock ? 'In Stock' : 'Out of Stock'}
                    </td>
                    <td>
                        <button class="action-btn add-to-cart-btn" data-id="${product.id}" ${!product.in_stock ? "disabled style='background: #ccc; cursor: not-allowed;'" : ""}>
                            <span class="material-symbols-outlined" style="font-size: 18px;">add_shopping_cart</span> Add
                        </button>
                    </td>
                </tr>
            `;
        }

        const gView = document.getElementById('grid-view');
        const pTbody = document.getElementById('product-tbody');
        
        if (gView) gView.innerHTML = gridCartona || '<div style="text-align:center; width:100%; padding:40px; grid-column:1/-1;">No products found.</div>';
        if (pTbody) pTbody.innerHTML = listCartona || '<tr><td colspan="6" style="text-align:center; padding:20px;">No products found.</td></tr>';

        // Initialize secure image loading
        document.querySelectorAll('.secure-prod-img').forEach(img => {
            let thumb = img.getAttribute('data-secure-thumb');
            if (thumb) {
                loadSecureImage(img, thumb);
            } else {
                img.src = 'https://placehold.co/400x300?text=No+Image';
            }
        });
    }

    const btnGrid = document.getElementById('btn-grid');
    const btnList = document.getElementById('btn-list');
    const gridView = document.getElementById('grid-view');
    const listView = document.getElementById('list-view');

    if (btnGrid && btnList) {
        btnGrid.addEventListener('click', () => {
            btnGrid.classList.add('active');
            btnList.classList.remove('active');
            if (gridView) gridView.style.display = 'grid';
            if (listView) listView.style.display = 'none';
        });

        btnList.addEventListener('click', () => {
            btnList.classList.add('active');
            btnGrid.classList.remove('active');
            if (listView) listView.style.display = 'block';
            if (gridView) gridView.style.display = 'none';
        });
    }

    // Add click handlers for product cards to navigate to detail page
    function initProductCardClicks() {
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', (e) => {
                // Don't navigate if clicking on buttons
                if (e.target.closest('button')) return;
                const productId = card.getAttribute('data-product-id');
                if (productId) {
                    window.location.href = `productsdetils.html?id=${productId}`;
                }
            });
        });

        // Also handle list view clicks
        document.querySelectorAll('tr[data-product-id]').forEach(row => {
            row.addEventListener('click', (e) => {
                if (e.target.closest('button')) return;
                const productId = row.getAttribute('data-product-id');
                if (productId) {
                    window.location.href = `productsdetils.html?id=${productId}`;
                }
            });
        });
    }

    async function addToCart(productId) {
        if (!Auth.requireAuth()) return;

        try {
            let response = await fetch(`${baseUrl}/cart/items`, {
                method: "POST",
                headers: Auth.getHeaders(),
                body: JSON.stringify({
                    product_id: parseInt(productId),
                    quantity: 1
                })
            });

            if (response.status === 401) {
                Auth.clearAuth();
                window.location.href = "login.html";
                return;
            }

            if (response.ok) {
                window.location.href = "cart.html"; 
            } else {
                let result = await response.json().catch(() => ({}));
                alert(result.message || "Failed to add to cart");
            }
        } catch (error) {
            console.error("Error adding to cart:", error);
            alert("Error: " + error.message);
        }
    }

    const gridArea = document.getElementById('grid-view');
    const listBody = document.getElementById('product-tbody');

    if (gridArea) {
        gridArea.addEventListener('click', (e) => {
            const btn = e.target.closest('.add-to-cart-btn');
            if (btn) {
                const productId = btn.getAttribute('data-id');
                addToCart(productId);
            }
        });
    }

    if (listBody) {
        listBody.addEventListener('click', (e) => {
            const btn = e.target.closest('.add-to-cart-btn');
            if (btn) {
                const productId = btn.getAttribute('data-id');
                addToCart(productId);
            }
        });
    }

    getCategories();
    getAllProducts();

    const applyBtn = document.getElementById('apply-filters-btn');
    if (applyBtn) {
        applyBtn.addEventListener('click', () => {
            currentPage = 1;
            getAllProducts();
        });
    }
    
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', e => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                getAllProducts();
            }, 500);
        });
    }

    const sortSelect = document.getElementById('sort-select');
    if (sortSelect) {
        sortSelect.addEventListener('change', () => {
            currentPage = 1;
            getAllProducts();
        });
    }
});