document.addEventListener("DOMContentLoaded", async function() {
    const API_BASE = window.API_CONFIG?.getBaseUrl() || window.location.origin + "/api";
    const token = localStorage.getItem("token") || localStorage.getItem("admin_token");
    if (!token) { alert("Unauthorized!"); window.location.href = "../frontend/login.html"; return; }

    const headers = {
        "Authorization": `Bearer ${token}`,
        "Accept": "application/json",
        "ngrok-skip-browser-warning": "true"
    };

    let products = [];
    let categories = [];
    let currentImageBase64 = '';
    const tableBody = document.getElementById('tableBody');
    const searchBar = document.getElementById('searchBar');
    const categoryFilter = document.getElementById('categoryFilter');
    const priceSortFilter = document.getElementById('priceSortFilter');
    const clearFiltersBtn = document.getElementById('clearFiltersBtn');

    const productForm = document.getElementById('productForm');
    const productModalEl = document.getElementById('productModal');
    const productModal = productModalEl ? new bootstrap.Modal(productModalEl) : null;
    const deleteConfirmModalEl = document.getElementById('deleteConfirmModal');
    const deleteConfirmModal = deleteConfirmModalEl ? new bootstrap.Modal(deleteConfirmModalEl) : null;

    const productModalLabel = document.getElementById('productModalLabel');
    const addNewBtn = document.getElementById('addNewBtn');
    const addCategoryBtn = document.getElementById('addCategoryBtn');
    const categoryModalEl = document.getElementById('categoryModal');
    const categoryModal = categoryModalEl ? new bootstrap.Modal(categoryModalEl) : null;
    const categoryForm = document.getElementById('categoryForm');
    const categoryNameInput = document.getElementById('categoryName');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');

    const prodImageFile = document.getElementById('prodImageFile');
    const prodImageUrl = document.getElementById('prodImageUrl');
    const imagePreviewBox = document.getElementById('imagePreviewBox');

    // Sidebar controls
    const menuToggleBtn = document.getElementById('menuToggleBtn');
    const closeSidebarBtn = document.getElementById('closeSidebarBtn');
    const sidebarMenu = document.getElementById('sidebarMenu');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    if (menuToggleBtn) {
        menuToggleBtn.addEventListener('click', function() {
            if (sidebarMenu) sidebarMenu.classList.add('show');
            if (sidebarOverlay) sidebarOverlay.classList.add('show');
        });
    }
    function closeSidebar() {
        if (sidebarMenu) sidebarMenu.classList.remove('show');
        if (sidebarOverlay) sidebarOverlay.classList.remove('show');
    }
    if (closeSidebarBtn) closeSidebarBtn.addEventListener('click', closeSidebar);
    if (sidebarOverlay) sidebarOverlay.addEventListener('click', closeSidebar);

    // Fetch categories for select dropdown
    async function fetchCategories() {
        try {
            const res = await fetch(`${API_BASE}/categories`, { headers: { 'Accept': 'application/json', 'ngrok-skip-browser-warning': 'true' } });
            if (!res.ok) return;
            const data = await res.json();
            categories = data.data || [];
            // Populate prodCat (clear any hardcoded options first)
            const sel = document.getElementById('prodCat') || document.getElementById('prodCategory');
            if (sel) {
                sel.innerHTML = '';
                if (categories.length) {
                    categories.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.name;
                        sel.appendChild(opt);
                    });
                }
            }
            // Also populate categoryFilter (keep "All Categories" option)
            if (categoryFilter) {
                categoryFilter.innerHTML = '<option value="all">All Categories</option>';
                if (categories.length) {
                    categories.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.name;
                        categoryFilter.appendChild(opt);
                    });
                }
            }
        } catch (e) { console.error("Categories fetch error:", e); }
    }

    // Fetch products
    async function fetchProducts() {
        try {
            const params = new URLSearchParams({ per_page: 100 });
            if (categoryFilter && categoryFilter.value !== 'all') params.set('category_id', categoryFilter.value);
            if (priceSortFilter && priceSortFilter.value === 'high-low') params.set('sort', 'price_desc');
            else if (priceSortFilter && priceSortFilter.value === 'low-high') params.set('sort', 'price_asc');

            const res = await fetch(`${API_BASE}/products?${params.toString()}`, { headers });
            if (!res.ok) throw new Error("Failed to fetch products");
            const data = await res.json();
            // Handle nested response { data: { items: [...] } } or { data: [...] }
            const raw = data.data || data;
            products = raw.data || raw.items || raw || [];
            renderProducts();
        } catch (err) {
            console.error(err);
            if (tableBody) tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">Failed to load products.</td></tr>`;
        }
    }

    function getStockBadge(qty) {
        if (qty > 10) return `<span class="text-success"><span class="status-dot status-in-stock"></span>In Stock (${qty})</span>`;
        if (qty > 0) return `<span class="text-warning"><span class="status-dot status-low-stock"></span>Low Stock (${qty})</span>`;
        return `<span class="text-danger"><span class="status-dot status-out-of-stock"></span>Out of Stock</span>`;
    }

    function getProductImageDOM(name, imageUrl) {
        if (imageUrl && imageUrl.trim() !== '') {
            return `<img src="${imageUrl}" alt="${name}" style="width:50px;height:50px;object-fit:cover;border-radius:8px;">`;
        }
        return `<span class="fw-bold text-primary" style="font-size:1.1rem">${(name || '?').charAt(0).toUpperCase()}</span>`;
    }

    function renderProducts() {
        const searchText = (searchBar?.value || "").toLowerCase().trim();

        let displayList = products.filter(p => {
            const matchesSearch = (p.name || "").toLowerCase().includes(searchText) || (p.sku || "").toLowerCase().includes(searchText);
            return matchesSearch;
        });

        if (tableBody) {
            tableBody.innerHTML = '';
            if (displayList.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">No products found.</td></tr>`;
                return;
            }

            displayList.forEach(p => {
                const catName = p.category?.name || p.category_name || p.cat || '—';
                const imageSrc = p.thumbnail || p.image || '';
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td class="fw-bold text-muted">#${p.id}</td>
                    <td>
                        <div class="d-flex align-items-center gap-3">
                            <div class="prod-img-box">${getProductImageDOM(p.name, imageSrc)}</div>
                            <div>
                                <h6 class="m-0 fw-bold" style="font-size:0.9rem">${p.name || '—'}</h6>
                                <small class="text-muted d-block text-truncate" style="max-width: 150px;">${p.description || p.desc || ''}</small>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge-category">${catName}</span></td>
                    <td class="fw-bold">EGP ${Number(p.price || p.base_price).toLocaleString('en-EG', { minimumFractionDigits: 2 })}</td>
                    <td>${getStockBadge(p.stock || 0)}</td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-primary me-1 edit-btn" data-id="${p.id}"><i class="fa-solid fa-pen"></i></button>
                        <button class="btn btn-sm btn-outline-warning stockout-btn" data-id="${p.id}" title="Mark out of stock"><i class="fa-solid fa-box"></i> Stock</button>
                    </td>
                `;
                tableBody.appendChild(tr);
            });

            document.querySelectorAll('.edit-btn').forEach(btn => btn.addEventListener('click', handleEdit));
            document.querySelectorAll('.stockout-btn').forEach(btn => btn.addEventListener('click', markOutOfStock));
        }
    }

    // Image handlers
    if (prodImageFile) {
        prodImageFile.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    currentImageBase64 = event.target.result;
                    if (prodImageUrl) prodImageUrl.value = '';
                    if (imagePreviewBox) imagePreviewBox.innerHTML = `<img src="${currentImageBase64}" style="max-width:100%;max-height:120px;border-radius:8px;">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (prodImageUrl) {
        prodImageUrl.addEventListener('input', function() {
            if (this.value.trim() !== '') {
                currentImageBase64 = '';
                if (prodImageFile) prodImageFile.value = '';
                if (imagePreviewBox) imagePreviewBox.innerHTML = `<img src="${this.value}" style="max-width:100%;max-height:120px;border-radius:8px;">`;
            } else {
                if (imagePreviewBox) imagePreviewBox.innerHTML = `<i class="fa-regular fa-image text-muted fs-4"></i>`;
            }
        });
    }

    // Submit create/update with FormData (supports file upload)
    if (productForm) {
        productForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const id = document.getElementById('productId')?.value;
            const fd = new FormData();
            fd.set('name', document.getElementById('prodName')?.value || '');
            fd.set('description', document.getElementById('prodDesc')?.value || '');
            fd.set('base_price', document.getElementById('prodPrice')?.value || '0');
            fd.set('category_id', document.getElementById('prodCat')?.value || document.getElementById('prodCategory')?.value || '');
            fd.set('stock', document.getElementById('prodQty')?.value || '0');

            const fileInput = document.getElementById('prodImageFile');
            if (fileInput?.files?.[0]) fd.set('image', fileInput.files[0]);

            const urlInput = document.getElementById('prodImageUrl');
            if (urlInput?.value?.trim()) fd.set('image_url', urlInput.value.trim());

            try {
                const url = id ? `${API_BASE}/products/${id}` : `${API_BASE}/products`;

                const res = await fetch(url, {
                    method: id ? 'PUT' : 'POST',
                    headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json', 'ngrok-skip-browser-warning': 'true' },
                    body: fd
                });

                if (!res.ok) {
                    const errData = await res.json().catch(() => ({}));
                    throw new Error(errData.message || 'Operation failed');
                }

                if (productModal) productModal.hide();
                await fetchProducts();
            } catch (err) {
                alert("Error: " + err.message);
            }
        });
    }

    if (addNewBtn) {
        addNewBtn.addEventListener('click', function() {
            if (productModalLabel) productModalLabel.innerText = "Add New Product";
            if (productForm) productForm.reset();
            const pid = document.getElementById('productId');
            if (pid) pid.value = '';
            currentImageBase64 = '';
            if (prodImageUrl) prodImageUrl.value = '';
            if (prodImageFile) prodImageFile.value = '';
            if (imagePreviewBox) imagePreviewBox.innerHTML = `<i class="fa-regular fa-image text-muted fs-4"></i>`;
            if (productModal) productModal.show();
        });
    }

    // Add Category
    if (addCategoryBtn) {
        addCategoryBtn.addEventListener('click', function() {
            if (categoryForm) categoryForm.reset();
            if (categoryModal) categoryModal.show();
            if (categoryNameInput) categoryNameInput.focus();
        });
    }

    if (categoryForm) {
        categoryForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const name = categoryNameInput?.value?.trim();
            if (!name) return;
            const btn = document.getElementById('saveCategoryBtn');
            if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>'; }
            try {
                const res = await fetch(`${API_BASE}/categories`, {
                    method: 'POST',
                    headers: { ...headers, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ name })
                });
                if (!res.ok) {
                    const errData = await res.json().catch(() => ({}));
                    throw new Error(errData.message || 'Failed to create category');
                }
                if (categoryModal) categoryModal.hide();
                // Refresh the category dropdowns
                await fetchCategories();
                // Auto-select the new category in the product form
                const newCat = await res.json();
                const newId = newCat.data?.id;
                const sel = document.getElementById('prodCat') || document.getElementById('prodCategory');
                if (sel && newId) sel.value = newId;
            } catch (err) {
                alert('Error: ' + err.message);
            } finally {
                if (btn) { btn.disabled = false; btn.textContent = 'Add'; }
            }
        });
    }

    function handleEdit(e) {
        const id = e.currentTarget.getAttribute('data-id');
        const prod = products.find(p => String(p.id) === id);
        if (prod) {
            if (productModalLabel) productModalLabel.innerText = "Update Product";
            const pid = document.getElementById('productId');
            if (pid) pid.value = prod.id;
            const pName = document.getElementById('prodName');
            if (pName) pName.value = prod.name || '';
            const pDesc = document.getElementById('prodDesc');
            if (pDesc) pDesc.value = prod.description || prod.desc || '';
            const pPrice = document.getElementById('prodPrice');
            if (pPrice) pPrice.value = prod.price || prod.base_price || '';
            const pCat = document.getElementById('prodCat') || document.getElementById('prodCategory');
            if (pCat) pCat.value = prod.category_id || prod.cat || '';
            const pQty = document.getElementById('prodQty');
            if (pQty) pQty.value = prod.stock || 0;

            currentImageBase64 = '';
            if (prodImageFile) prodImageFile.value = '';
            // Show current image URL if it's a URL-based image
            const currentImg = Array.isArray(prod.images) && prod.images.length > 0 ? prod.images[0] : '';
            if (prodImageUrl) {
                prodImageUrl.value = (currentImg && currentImg.startsWith('http')) ? currentImg : '';
            }
            const imgSrc = prod.thumbnail || currentImg || '';
            if (imagePreviewBox) {
                imagePreviewBox.innerHTML = imgSrc
                    ? `<img src="${imgSrc}" style="max-width:100%;max-height:120px;border-radius:8px;">`
                    : `<i class="fa-regular fa-image text-muted fs-4"></i>`;
            }
            if (productModal) productModal.show();
        }
    }

    async function markOutOfStock(e) {
        const id = e.currentTarget.getAttribute('data-id');
        const prod = products.find(p => String(p.id) === id);
        if (!prod) return;
        if (prod.stock <= 0) {
            alert('Product is already out of stock.');
            return;
        }
        if (!confirm(`Mark "${prod.name}" as out of stock?`)) return;

        const btn = e.currentTarget;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        try {
            const res = await fetch(`${API_BASE}/products/${id}`, {
                method: 'PUT',
                headers: { ...headers, 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    category_id: prod.category_id,
                    name: prod.name,
                    description: prod.description || '',
                    base_price: prod.price || prod.base_price || 0,
                    stock: 0
                })
            });
            if (!res.ok) {
                const errBody = await res.json().catch(() => ({}));
                if (res.status === 401) {
                    localStorage.removeItem('admin_token');
                    alert('Session expired. Please login again.');
                    window.location.href = 'login.html';
                    return;
                }
                throw new Error(errBody.message || `HTTP ${res.status}`);
            }
            await fetchProducts();
        } catch (err) {
            alert("Failed to mark out of stock: " + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-box"></i> Stock';
        }
    }

    // Filters
    searchBar?.addEventListener('input', renderProducts);
    categoryFilter?.addEventListener('change', fetchProducts);
    priceSortFilter?.addEventListener('change', fetchProducts);

    clearFiltersBtn?.addEventListener('click', function() {
        if (searchBar) searchBar.value = '';
        if (categoryFilter) categoryFilter.value = 'all';
        if (priceSortFilter) priceSortFilter.value = 'default';
        fetchProducts();
    });

    await fetchCategories();
    await fetchProducts();
});
