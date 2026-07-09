document.addEventListener("DOMContentLoaded", async function () {
    const API_BASE = window.location.origin + "/api";
    const token = localStorage.getItem("admin_token");
    if (!token) { alert("Unauthorized!"); window.location.href = "login.html"; return; }

    const headers = {
        "Authorization": `Bearer ${token}`,
        "Accept": "application/json",
        "ngrok-skip-browser-warning": "true"
    };

    let orders = [];
    const ordersTableBody = document.getElementById('ordersTableBody');
    const searchOrdersBar = document.getElementById('searchOrdersBar');
    const statusFilter = document.getElementById('statusFilter');
    const ordersSortFilter = document.getElementById('ordersSortFilter');
    const clearOrdersFiltersBtn = document.getElementById('clearOrdersFiltersBtn');
    const updateStatusForm = document.getElementById('updateStatusForm');
    const orderStatusModal = new bootstrap.Modal(document.getElementById('orderStatusModal'));

    async function fetchOrders() {
        try {
            const params = new URLSearchParams();
            if (statusFilter.value !== 'all') params.set('status', statusFilter.value);
            params.set('per_page', '50');

            const url = `${API_BASE}/admin/orders?${params.toString()}`;
            const res = await fetch(url, { headers });
            if (!res.ok) throw new Error("Failed to fetch orders");
            const data = await res.json();
            // Cache middleware wraps in { message, source, data: actual }
            const payload = data.data || data;
            orders = payload.data || payload.items || payload || [];
            renderOrders(orders);
        } catch (err) {
            console.error(err);
            ordersTableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-danger">Failed to load orders.</td></tr>`;
        }
    }

    function renderOrders(list) {
        const searchText = searchOrdersBar.value.toLowerCase().trim();
        const selectedSort = ordersSortFilter.value;

        // Client-side search
        let displayList = list.filter(o => {
            const idMatch = String(o.id).toLowerCase().includes(searchText);
            const nameMatch = (o.customer_name || "").toLowerCase().includes(searchText);
            return idMatch || nameMatch;
        });

        // Client-side sort
        if (selectedSort === 'price-high-low') {
            displayList.sort((a, b) => Number(b.total) - Number(a.total));
        } else if (selectedSort === 'price-low-high') {
            displayList.sort((a, b) => Number(a.total) - Number(b.total));
        } else {
            displayList.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
        }

        ordersTableBody.innerHTML = '';
        if (displayList.length === 0) {
            ordersTableBody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">No orders found.</td></tr>`;
            return;
        }

        displayList.forEach(o => {
            const customerName = o.customer_name || "—";
            const customerEmail = o.customer_email || "—";
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="fw-bold text-primary">#${o.id}</td>
                <td>
                    <div>
                        <h6 class="m-0 fw-bold" style="font-size: 0.9rem;">${customerName}</h6>
                        <small class="text-muted" style="font-size: 0.75rem;">${customerEmail}</small>
                    </div>
                </td>
                <td class="text-muted">${o.created_at ? o.created_at.split('T')[0] : '—'}</td>
                <td class="fw-bold">EGP ${Number(o.total).toLocaleString('en-EG', { minimumFractionDigits: 2 })}</td>
                <td><span class="badge-status status-${o.status}">${o.status}</span></td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary me-1 edit-status-btn" data-id="${o.id}">
                        <i class="fa-solid fa-arrows-rotate me-1"></i> Status
                    </button>
                    <button class="btn btn-sm btn-outline-danger print-pdf-btn" data-id="${o.id}">
                        <i class="fa-solid fa-file-pdf me-1"></i> PDF
                    </button>
                </td>
            `;
            ordersTableBody.appendChild(tr);
        });

        document.querySelectorAll('.edit-status-btn').forEach(btn => btn.addEventListener('click', openStatusModal));
        document.querySelectorAll('.print-pdf-btn').forEach(btn => btn.addEventListener('click', downloadPdf));
    }

    function openStatusModal(e) {
        const id = e.currentTarget.getAttribute('data-id');
        const order = orders.find(o => String(o.id) === id);
        if (order) {
            document.getElementById('statusOrderId').value = order.id;
            document.getElementById('modalOrderStatus').value = order.status;
            orderStatusModal.show();
        }
    }

    async function downloadPdf(e) {
        const id = e.currentTarget.getAttribute('data-id');
        const btn = e.currentTarget;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Loading...';
        try {
            const res = await fetch(`${API_BASE}/admin/orders/${id}/print-file`, { headers });
            if (!res.ok) {
                const errBody = await res.json().catch(() => ({}));
                const msg = errBody.message || `HTTP ${res.status}`;
                throw new Error(msg);
            }
            const blob = await res.blob();
            const blobUrl = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = blobUrl;
            a.download = `order-${id}.pdf`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(blobUrl);
        } catch (err) {
            alert("PDF download failed: " + err.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-file-pdf me-1"></i> PDF';
        }
    }

    updateStatusForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const id = document.getElementById('statusOrderId').value;
        const newStatus = document.getElementById('modalOrderStatus').value;

        try {
            const res = await fetch(`${API_BASE}/admin/orders/${id}/status`, {
                method: 'PUT',
                headers: { ...headers, 'Content-Type': 'application/json' },
                body: JSON.stringify({ status: newStatus })
            });
            if (!res.ok) {
                const errBody = await res.json().catch(() => ({}));
                const msg = errBody.message || errBody.errors?.status?.[0] || `HTTP ${res.status}`;
                throw new Error(msg);
            }
            orderStatusModal.hide();
            await fetchOrders();
        } catch (err) {
            alert("Status update failed: " + err.message);
        }
    });

    searchOrdersBar.addEventListener('input', () => renderOrders(orders));
    statusFilter.addEventListener('change', fetchOrders);
    ordersSortFilter.addEventListener('change', fetchOrders);

    clearOrdersFiltersBtn.addEventListener('click', function () {
        searchOrdersBar.value = '';
        statusFilter.value = 'all';
        ordersSortFilter.value = 'default';
        fetchOrders();
    });

    await fetchOrders();
});

// Sidebar toggle
(function() {
    const sidebar = document.getElementById('sidebarMenu');
    const toggleBtn = document.getElementById('menuToggleBtn');
    const closeBtn = document.getElementById('closeSidebarBtn');
    const overlay = document.getElementById('sidebarOverlay');
    function openSidebar() { if (sidebar) sidebar.classList.add('show'); if (overlay) overlay.classList.add('show'); }
    function closeSidebar() { if (sidebar) sidebar.classList.remove('show'); if (overlay) overlay.classList.remove('show'); }
    if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
    if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);
})();

// Mini bar animations (dashboard leftovers, harmless)
setTimeout(() => {
    document.querySelectorAll('.mini-bar').forEach(bar => {
        const h = bar.getAttribute('data-height');
        if (h) bar.style.height = h;
    });
}, 500);