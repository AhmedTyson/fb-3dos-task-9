document.addEventListener('DOMContentLoaded', function () {
    const placeholder = document.getElementById('navbar-placeholder');
    if (!placeholder) return;

    const isAdminPage = window.location.pathname.includes('/admin/');
    const prefix = isAdminPage ? '../frontend/' : '';

    const page = window.location.pathname.split('/').pop() || 'home.html';
    const isActive = (p) => page === p || (p === 'products.html' && (page === 'productsdetils.html'));

    placeholder.innerHTML = `
    <nav class="navbar navbar-expand-lg bg-white shadow-sm py-3 fixed-top">
      <div class="container px-4">
        <a class="navbar-brand fw-bold fs-3 text-primary" href="${prefix}home.html">عمور للالكترونيات</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarContent">
          <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center">
            <li class="nav-item"><a class="nav-link mx-2 ${isActive('home.html') ? 'text-primary fw-bold' : 'text-secondary fw-medium'}" href="${prefix}home.html">Home</a></li>
            <li class="nav-item"><a class="nav-link mx-2 ${isActive('products.html') ? 'text-primary fw-bold' : 'text-secondary fw-medium'}" href="${prefix}products.html">Products</a></li>
            <li class="nav-item"><a class="nav-link mx-2 ${isActive('cart.html') ? 'text-primary fw-bold' : 'text-secondary fw-medium'}" href="${prefix}cart.html">Cart</a></li>
            <li class="nav-item"><a class="nav-link mx-2 ${isActive('order.html') ? 'text-primary fw-bold' : 'text-secondary fw-medium'}" href="${prefix}order.html">Orders</a></li>
            <li class="nav-item" id="adminNavItem" style="display:none"><a class="nav-link text-warning fw-bold mx-2" href="../admin/index.html">Admin Panel</a></li>
          </ul>
          <div class="d-flex justify-content-center justify-content-lg-end align-items-center gap-3 mt-3 mt-lg-0">
            <span id="userNameDisplay" class="fw-semibold text-dark"></span>
            <button id="signOutBtn" class="btn btn-primary fw-semibold rounded-2 px-4" title="Sign Out">Sign Out</button>
          </div>
        </div>
      </div>
    </nav>`;

    const adminItem = document.getElementById('adminNavItem');
    if (adminItem && window.Auth && Auth.isAdmin()) {
        adminItem.style.display = '';
    }

    const signOutBtn = document.getElementById('signOutBtn');
    if (signOutBtn && window.Auth) {
        signOutBtn.addEventListener('click', () => {
            if (isAdminPage) { AdminAuth.logout(); }
            else { Auth.logout(); }
        });
    }

    if (window.Auth) Auth.loadUserInfo();
});
