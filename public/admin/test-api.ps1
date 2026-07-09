<#
.SYNOPSIS
  Test all admin API endpoints end-to-end via ngrok.
  Run: pwsh -NoProfile .\public\admin\test-api.ps1
#>

$BASE = "https://swifter-penpal-cash.ngrok-free.dev"
$SKIP_BROWSER = @{ "ngrok-skip-browser-warning" = "true" }
$PASS = 0; $FAIL = 0

function Ok { $global:PASS++; Write-Host "  ✓ $($args[0])" -ForegroundColor Green }
function Nok { $global:FAIL++; Write-Host "  ✗ $($args[0])" -ForegroundColor Red; if ($args[1]) { Write-Host "    $($args[1])" } }

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  ADMIN API END-TO-END TEST SUITE" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan

# ── 1. LOGIN ──────────────────────────────────────────────
Write-Host "`n── 1. AUTH ──────────────────────────" -ForegroundColor Yellow

$token = $null
try {
    $body = @{ email = "admin@example.com"; password = "password" } | ConvertTo-Json
    $res = Invoke-WebRequest -Uri "$BASE/api/login" -Method POST -Body $body `
        -Headers ($SKIP_BROWSER + @{ "Content-Type" = "application/json"; "Accept" = "application/json" }) `
        -TimeoutSec 15 -UseBasicParsing
    $global:token = ($res.Content | ConvertFrom-Json).data.token
    if ($token) { Ok "POST /api/login (valid admin)" } else { Nok "POST /api/login" "no token" }
} catch { Nok "POST /api/login" $_ }

$auth = $SKIP_BROWSER + @{ "Authorization" = "Bearer $token"; "Accept" = "application/json" }

try {
    $body = @{ email = "admin@example.com"; password = "wrong" } | ConvertTo-Json
    $null = Invoke-WebRequest -Uri "$BASE/api/login" -Method POST -Body $body `
        -Headers ($SKIP_BROWSER + @{ "Content-Type" = "application/json" }) -TimeoutSec 10 -UseBasicParsing
    Nok "POST /api/login (bad pw → 401)" "should have rejected"
} catch {
    if ($_.Exception.Response.StatusCode -eq 401) { Ok "POST /api/login (bad pw → 401)" } else { Nok "POST /api/login (bad pw)" "expected 401 got $($_.Exception.Response.StatusCode)" }
}

# ── 2. DASHBOARD KPIs ──────────────────────────────────────
Write-Host "`n── 2. DASHBOARD KPIs ─────────────────" -ForegroundColor Yellow

try {
    $res = Invoke-WebRequest -Uri "$BASE/api/admin/reports/sales?format=json" -Headers $auth -TimeoutSec 10 -UseBasicParsing
    $d = ($res.Content | ConvertFrom-Json).data
    if ($d.total_orders -and $d.total_revenue -and $d.top_product) {
        Ok "GET /api/admin/reports/sales"
        Write-Host "     revenue=$($d.total_revenue) orders=$($d.total_orders) top=$($d.top_product.name)"
    } else { Nok "GET /api/admin/reports/sales" "missing fields" }
} catch { Nok "GET /api/admin/reports/sales" $_ }

try {
    $res = Invoke-WebRequest -Uri "$BASE/api/admin/reports/sales?format=json" -Headers $auth -TimeoutSec 10 -UseBasicParsing
    $bs = ($res.Content | ConvertFrom-Json).data.best_sellers
    if ($bs.Count -ge 1) { Ok "best_sellers array ($($bs.Count) items)" } else { Nok "best_sellers" "empty" }
} catch { Nok "best_sellers" $_ }

try {
    $res = Invoke-WebRequest -Uri "$BASE/api/admin/reports/sales?format=json" -Headers $auth -TimeoutSec 10 -UseBasicParsing
    $dd = ($res.Content | ConvertFrom-Json).data.daily_breakdown
    if ($dd.Count -ge 1) { Ok "daily_breakdown array ($($dd.Count) days)" } else { Nok "daily_breakdown" "empty" }
} catch { Nok "daily_breakdown" $_ }

# ── 3. CUSTOMERS ───────────────────────────────────────────
Write-Host "`n── 3. CUSTOMERS ─────────────────────" -ForegroundColor Yellow

try {
    $res = Invoke-WebRequest -Uri "$BASE/api/admin/users?per_page=5" -Headers $auth -TimeoutSec 10 -UseBasicParsing
    $d = ($res.Content | ConvertFrom-Json)
    $total = $d.data.pagination.total
    $items = @($d.data.data).Count
    if ($items -ge 1) {
        Ok "GET /api/admin/users"
        Write-Host "     total=$total returned=$items X-Total=$($res.Headers['X-Pagination-Total-Count'])"
    } else { Nok "GET /api/admin/users" "no items" }
} catch { Nok "GET /api/admin/users" $_ }

try {
    $res = Invoke-WebRequest -Uri "$BASE/api/admin/users?search=ahmed&per_page=3" -Headers $auth -TimeoutSec 10 -UseBasicParsing
    $items = @(($res.Content | ConvertFrom-Json).data.data).Count
    if ($items -ge 1) { Ok "search users (found $items)" } else { Nok "search users" "0 results" }
} catch { Nok "search users" $_ }

# ── 4. ORDERS ──────────────────────────────────────────────
Write-Host "`n── 4. ORDERS ────────────────────────" -ForegroundColor Yellow

try {
    $res = Invoke-WebRequest -Uri "$BASE/api/admin/orders?per_page=3" -Headers $auth -TimeoutSec 10 -UseBasicParsing
    $d = ($res.Content | ConvertFrom-Json)
    $items = @($d.data.items).Count; $total = $d.data.pagination.total
    if ($items -ge 1) { Ok "GET /api/admin/orders (total=$total)" } else { Nok "GET /api/admin/orders" "0 items" }
} catch { Nok "GET /api/admin/orders" $_ }

try {
    $res = Invoke-WebRequest -Uri "$BASE/api/admin/orders?status=pending&per_page=5" -Headers $auth -TimeoutSec 10 -UseBasicParsing
    $items = @(($res.Content | ConvertFrom-Json).data.items).Count
    if ($items -ge 1) { Ok "pending orders ($items found)" } else { Nok "pending orders" "0" }
} catch { Nok "pending orders" $_ }

try {
    $res = Invoke-WebRequest -Uri "$BASE/api/admin/orders?status=approved&per_page=5" -Headers $auth -TimeoutSec 10 -UseBasicParsing
    $items = @(($res.Content | ConvertFrom-Json).data.items).Count
    if ($items -ge 1) { Ok "approved orders ($items found)" } else { Nok "approved orders" "0" }
} catch { Nok "approved orders" $_ }

try {
    # Fetch fresh list (bypass cache with unique param)
    $ts = [DateTimeOffset]::UtcNow.ToUnixTimeSeconds()
    $res = Invoke-WebRequest -Uri "$BASE/api/admin/orders?status=pending&per_page=5&_=$ts" -Headers $auth -TimeoutSec 10 -UseBasicParsing
    $orders = @(($res.Content | ConvertFrom-Json).data.items)
    if ($orders.Count -eq 0) { throw "no pending orders available" }
    $id = $orders[0].id
    $body = @{ status = "cancelled" } | ConvertTo-Json
    $upd = Invoke-WebRequest -Uri "$BASE/api/admin/orders/$id/status" -Method PUT -Body $body `
        -Headers ($auth + @{ "Content-Type" = "application/json" }) -TimeoutSec 10 -UseBasicParsing
    if ($upd.StatusCode -eq 200) { Ok "PUT .../orders/{id}/status (pending→cancelled)" } else { Nok "PUT .../orders/{id}/status" ($upd.Content | ConvertFrom-Json).message }
} catch { Nok "PUT .../orders/{id}/status" $_ }

try {
    $res = Invoke-WebRequest -Uri "$BASE/api/admin/orders?per_page=1" -Headers $auth -TimeoutSec 10 -UseBasicParsing
    $id = @(($res.Content | ConvertFrom-Json).data.items)[0].id
    $pdf = Invoke-WebRequest -Uri "$BASE/api/admin/orders/$id/print-file" -Headers $auth -TimeoutSec 15 -UseBasicParsing
    if ($pdf.Headers['Content-Type'] -match 'pdf') {
        Ok "GET .../orders/{id}/print-file (PDF $($pdf.RawContentLength) bytes)"
    } else { Nok "print-file" "not PDF: $($pdf.Headers['Content-Type'])" }
} catch { Nok "print-file" $_ }

# ── 5. PRODUCTS ────────────────────────────────────────────
Write-Host "`n── 5. PRODUCTS ──────────────────────" -ForegroundColor Yellow

try {
    $res = Invoke-WebRequest -Uri "$BASE/api/products?per_page=3" -Headers $SKIP_BROWSER -TimeoutSec 10 -UseBasicParsing
    $d = $res.Content | ConvertFrom-Json
    $items = @($d.data).Count
    if ($items -ge 1) { Ok "GET /api/products (public, $items items)" } else { Nok "GET /api/products" "0" }
} catch { Nok "GET /api/products" $_ }

try {
    $res = Invoke-WebRequest -Uri "$BASE/api/products?search=keyboard&per_page=3" -Headers $SKIP_BROWSER -TimeoutSec 10 -UseBasicParsing
    $items = @(($res.Content | ConvertFrom-Json).data).Count
    if ($items -ge 1) { Ok "search products (found $items)" } else { Nok "search products" "0" }
} catch { Nok "search products" $_ }

try {
    $res = Invoke-WebRequest -Uri "$BASE/api/categories" -Headers $SKIP_BROWSER -TimeoutSec 10 -UseBasicParsing
    $items = @(($res.Content | ConvertFrom-Json).data).Count
    if ($items -ge 1) { Ok "GET /api/categories ($items)" } else { Nok "GET /api/categories" "0" }
} catch { Nok "GET /api/categories" $_ }

try {
    $body = @{ name = "Test E2E"; description = "test"; base_price = 99.99; category_id = 1; stock = 10 } | ConvertTo-Json
    $res = Invoke-WebRequest -Uri "$BASE/api/products" -Method POST -Body $body `
        -Headers ($auth + @{ "Content-Type" = "application/json" }) -TimeoutSec 10 -UseBasicParsing
    $created = $res.Content | ConvertFrom-Json
    $id = $created.data?.id ?? $created.id
    Ok "POST /api/products (create id=$id)"
} catch { Nok "POST /api/products" $_ }

try {
    $body = @{ name = "TestCat$(Get-Random -Min 100 -Max 999)" } | ConvertTo-Json
    $res = Invoke-WebRequest -Uri "$BASE/api/categories" -Method POST -Body $body `
        -Headers ($auth + @{ "Content-Type" = "application/json" }) -TimeoutSec 10 -UseBasicParsing
    $cat = ($res.Content | ConvertFrom-Json).data
    Ok "POST /api/categories (created id=$($cat.id) $($cat.name))"
} catch { Nok "POST /api/categories" $_ }

# ── 6. STATIC PAGES ────────────────────────────────────────
Write-Host "`n── 6. STATIC PAGES ──────────────────" -ForegroundColor Yellow

foreach ($page in @("/admin/index.html","/admin/login.html","/admin/orders.html","/admin/products.html","/admin/customers.html")) {
    try { $r = Invoke-WebRequest -Uri "$BASE$page" -Headers $SKIP_BROWSER -TimeoutSec 10 -UseBasicParsing; Ok "GET $page" } catch { Nok "GET $page" $_ }
}

# ── 7. REPORT EXPORT ────────────────────────────────────────
Write-Host "`n── 7. REPORT EXPORT ─────────────────" -ForegroundColor Yellow

try {
    $res = Invoke-WebRequest -Uri "$BASE/api/admin/reports/sales?format=pdf" -Headers $auth -TimeoutSec 20 -UseBasicParsing
    if ($res.Headers['Content-Type'] -match 'pdf') { Ok "PDF export ($($res.RawContentLength) bytes)" } else { Nok "PDF export" "not PDF: $($res.Headers['Content-Type'])" }
} catch { Nok "PDF export" $_ }

try {
    $res = Invoke-WebRequest -Uri "$BASE/api/admin/reports/sales?format=xlsx" -Headers $auth -TimeoutSec 20 -UseBasicParsing
    if ($res.Headers['Content-Type'] -match 'sheet|octet') { Ok "Excel export ($($res.RawContentLength) bytes)" } else { Nok "Excel export" "not spreadsheet: $($res.Headers['Content-Type'])" }
} catch { Nok "Excel export" $_ }

# ── SUMMARY ─────────────────────────────────────────────────
$TOTAL = $PASS + $FAIL
$PCT = if ($TOTAL -gt 0) { [math]::Round($PASS / $TOTAL * 100, 1) } else { 0 }
Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "  RESULTS: $PASS / $TOTAL passed ($PCT%)" -ForegroundColor $(if ($FAIL -eq 0) { "Green" } else { "Yellow" })
Write-Host "========================================" -ForegroundColor Cyan
if ($FAIL -gt 0) { exit 1 } else { exit 0 }
