document.addEventListener("DOMContentLoaded", async function () {
    const API_BASE = window.API_CONFIG?.getBaseUrl() || window.location.origin + "/api";
    const token = localStorage.getItem("token") || localStorage.getItem("admin_token");
    if (!token) { alert("Unauthorized!"); window.location.href = "../frontend/login.html"; return; }

    const headers = {
        "Authorization": `Bearer ${token}`,
        "Accept": "application/json",
        "ngrok-skip-browser-warning": "true"
    };

    const tableBody = document.getElementById('customersTableBody');
    const searchBar = document.getElementById('searchInput');
    const tableSummary = document.getElementById('tableSummary');
    const paginationNav = document.getElementById('paginationNav');

    let allCustomers = [];
    let currentPage = 1;
    let perPage = 10;
    let totalCustomers = 0;
    let lastPage = 1;

    let refreshing = false;

    async function fetchCustomers(page) {
        try {
            const url = `${API_BASE}/admin/users?page=${page}&per_page=${perPage}`;
            const res = await fetch(url, { headers });
            if (!res.ok) throw new Error("Failed to fetch customers");
            const body = await res.json();
            // Cache middleware wraps in { message, source, data, pagination? }
            const raw = body.data || body || [];
            allCustomers = raw.data || raw.items || raw || [];
            totalCustomers = body?.pagination?.total || raw?.pagination?.total || body?.meta?.total || raw?.meta?.total || allCustomers.length;
            lastPage = body?.pagination?.last_page || raw?.pagination?.last_page || body?.meta?.last_page || raw?.meta?.last_page || 1;
            currentPage = body?.pagination?.current_page || raw?.pagination?.current_page || page;
            renderCustomers();
            renderPagination();
        } catch (err) {
            console.error(err);
            if (tableBody) tableBody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-danger">Failed to load customers.</td></tr>`;
        }
    }

    function renderCustomers() {
        const searchText = (searchBar?.value || "").toLowerCase().trim();
        let displayList = allCustomers.filter(c => {
            return (c.name || "").toLowerCase().includes(searchText) ||
                   (c.email || "").toLowerCase().includes(searchText);
        });

        if (!tableBody) return;
        tableBody.innerHTML = '';
        if (displayList.length === 0) {
            tableBody.innerHTML = `<tr><td colspan="5" class="text-center py-4 text-muted">No customers found.</td></tr>`;
            if (tableSummary) tableSummary.textContent = '0 entries';
            return;
        }

        displayList.forEach(c => {
            const initial = (c.name || "U").charAt(0).toUpperCase();
            const badgeClass = c.role === 'admin' ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success';
            const roleText = c.role === 'admin' ? 'Admin' : 'Customer';
            const regDate = c.created_at ? c.created_at.split('T')[0] : '—';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary rounded-circle text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; min-width: 40px; font-weight:600;">
                            ${initial}
                        </div>
                        <div>
                            <h6 class="m-0 fw-bold" style="font-size: 0.9rem;">${c.name || '—'}</h6>
                        </div>
                    </div>
                </td>
                <td class="text-muted" style="font-size:0.85rem;">${c.email || '—'}</td>
                <td class="text-muted" style="font-size:0.85rem;">${regDate}</td>
                <td class="fw-bold">${c.orders_count || 0}</td>
                <td><span class="badge ${badgeClass} rounded-pill px-2 py-1" style="font-size: 0.75rem;">${roleText}</span></td>
            `;
            tableBody.appendChild(tr);
        });

        if (tableSummary) {
            const start = (currentPage - 1) * perPage + 1;
            const end = Math.min(currentPage * perPage, totalCustomers);
            tableSummary.textContent = `Showing ${start} to ${end} of ${totalCustomers} entries`;
        }
    }

    function renderPagination() {
        if (!paginationNav) return;
        paginationNav.innerHTML = '';
        if (lastPage <= 1) return;

        // Prev
        const prevLi = document.createElement('li');
        prevLi.className = `page-item ${currentPage <= 1 ? 'disabled' : ''}`;
        prevLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage - 1}"><i class="fa-solid fa-chevron-left"></i></a>`;
        paginationNav.appendChild(prevLi);

        // Pages
        const maxVisible = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(lastPage, startPage + maxVisible - 1);
        if (endPage - startPage < maxVisible - 1) startPage = Math.max(1, endPage - maxVisible + 1);

        for (let p = startPage; p <= endPage; p++) {
            const li = document.createElement('li');
            li.className = `page-item ${p === currentPage ? 'active' : ''}`;
            li.innerHTML = `<a class="page-link" href="#" data-page="${p}">${p}</a>`;
            paginationNav.appendChild(li);
        }

        // Next
        const nextLi = document.createElement('li');
        nextLi.className = `page-item ${currentPage >= lastPage ? 'disabled' : ''}`;
        nextLi.innerHTML = `<a class="page-link" href="#" data-page="${currentPage + 1}"><i class="fa-solid fa-chevron-right"></i></a>`;
        paginationNav.appendChild(nextLi);

        // Click handler
        paginationNav.querySelectorAll('.page-link[data-page]').forEach(a => {
            a.addEventListener('click', function(e) {
                e.preventDefault();
                const page = parseInt(this.dataset.page);
                if (page && page !== currentPage && page >= 1 && page <= lastPage) {
                    currentPage = page;
                    fetchCustomers(page);
                }
            });
        });
    }

    const refreshBtn = document.getElementById('refreshBtn');
    const refreshIcon = document.getElementById('refreshIcon');
    refreshBtn?.addEventListener('click', function () {
        if (refreshing) return;
        refreshing = true;
        if (refreshIcon) refreshIcon.className = 'fa-solid fa-rotate fa-spin';
        if (searchBar) searchBar.value = '';
        currentPage = 1;
        fetchCustomers(1).finally(() => {
            refreshing = false;
            if (refreshIcon) setTimeout(() => refreshIcon.className = 'fa-solid fa-rotate', 600);
        });
    });

    searchBar?.addEventListener('input', function() {
        renderCustomers();
    });

    await fetchCustomers(1);
});