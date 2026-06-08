<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Restaurant Cart</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<style>
  body { background: #f8f9fa; }
  .menu-card { cursor: pointer; transition: transform .15s, box-shadow .15s; }
  .menu-card:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.12); }
  .menu-card .price-badge { font-size: 1.1rem; font-weight: 700; }
  #cart-panel { position: sticky; top: 1rem; }
  .qty-btn { width: 30px; height: 30px; padding: 0; line-height: 1; }
  #cart-table tbody tr td { vertical-align: middle; }
  .tax-row td { font-size: .9rem; color: #6c757d; }
  .total-row td { font-weight: 700; font-size: 1.05rem; }
  #empty-cart { display: none; }
  .added-flash { animation: flashGreen .4s ease-out; }
  @keyframes flashGreen { 0%,100% { background: inherit; } 50% { background: #d1e7dd; } }
</style>
</head>
<body>

<div class="container-fluid py-4">
  <div class="row g-4">

    <div class="col-lg-7">
      <h4 class="mb-3 fw-bold">Menu</h4>
      <div class="row g-3">
        <?php foreach ($catalog as $item): ?>
        <div class="col-sm-6 col-md-4">
          <div class="card menu-card h-100 shadow-sm"
               data-id="<?= $item['id'] ?>"
               data-name="<?= esc($item['name']) ?>"
               data-price="<?= $item['price'] ?>"
               onclick="addToCart(<?= $item['id'] ?>)">
            <div class="card-body text-center">
              <div class="mb-2 fs-1">🍽️</div>
              <h6 class="card-title fw-bold mb-1"><?= esc($item['name']) ?></h6>
              <p class="card-text text-muted small mb-2"><?= esc($item['description']) ?></p>
              <span class="badge bg-success price-badge">£<?= number_format($item['price'], 2) ?></span>
              <div class="mt-1"><small class="text-muted">Incl. <?= $tax_rate ?>% Tax</small></div>
            </div>
            <div class="card-footer bg-transparent text-center">
              <small class="text-primary fw-semibold">+ Add to Cart</small>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="col-lg-5">
      <div id="cart-panel" class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
          <span class="fw-bold fs-5">🛒 Cart <span class="badge bg-secondary ms-1" id="cart-count">0</span></span>
          <button class="btn btn-sm btn-outline-light" onclick="clearCart()">Clear</button>
        </div>
        <div class="card-body p-0">

          <div id="empty-cart" class="text-center py-5 text-muted">
            <div class="fs-1">🛒</div>
            <p class="mb-0">Cart is empty</p>
          </div>

          <table class="table mb-0" id="cart-table">
            <thead class="table-light">
              <tr>
                <th>Item</th>
                <th class="text-center">Unit Price</th>
                <th class="text-center" style="width:110px">Qty</th>
                <th class="text-end">Price</th>
              </tr>
            </thead>
            <tbody id="cart-body">

            </tbody>
            <tfoot id="cart-foot">

            </tfoot>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
const BASE_URL = '<?= base_url() ?>';
const CSRF_TOKEN_NAME = '<?= csrf_token() ?>';
let CSRF_HASH = '<?= csrf_hash() ?>';

function fmt(n) { return '£' + parseFloat(n).toFixed(2); }

async function post(endpoint, body = {}) {
  body[CSRF_TOKEN_NAME] = CSRF_HASH;
  const res = await fetch(BASE_URL + endpoint, {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(body),
  });
  const data = await res.json();
  if (data[CSRF_TOKEN_NAME]) CSRF_HASH = data[CSRF_TOKEN_NAME]; // rotate token
  return data;
}

async function get(endpoint) {
  const res = await fetch(BASE_URL + endpoint, {
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
  });
  return res.json();
}

async function addToCart(itemId) {
  const card = document.querySelector(`.menu-card[data-id="${itemId}"]`);
  card.classList.add('added-flash');
  setTimeout(() => card.classList.remove('added-flash'), 400);
  const data = await post('cart/add', { item_id: itemId, qty: 1 });
  if (data.success) renderCart(data.cart, data.totals);
}

async function updateQty(itemId, delta) {
  const input = document.getElementById('qty-' + itemId);
  const newQty = Math.max(0, parseInt(input.value) + delta);
  const data = await post('cart/update', { item_id: itemId, qty: newQty });
  if (data.success) renderCart(data.cart, data.totals);
}

async function removeItem(itemId) {
  const data = await post('cart/remove', { item_id: itemId });
  if (data.success) renderCart(data.cart, data.totals);
}

async function clearCart() {
  const data = await post('cart/clear');
  if (data.success) renderCart(data.cart, data.totals);
}

function renderCart(cart, totals) {
  const tbody = document.getElementById('cart-body');
  const tfoot = document.getElementById('cart-foot');
  const table = document.getElementById('cart-table');
  const empty = document.getElementById('empty-cart');
  const badge = document.getElementById('cart-count');

  badge.textContent = totals.item_count;

  if (cart.length === 0) {
    table.style.display = 'none';
    empty.style.display = 'block';
    return;
  }

  table.style.display = '';
  empty.style.display = 'none';

  tbody.innerHTML = cart.map(item => `
    <tr id="row-${item.item_id}" class="${item.qty === 0 ? 'd-none' : ''}">
      <td>
        <span class="fw-semibold">${item.name}</span>
        <button class="btn btn-link btn-sm text-danger p-0 ms-1" onclick="removeItem(${item.item_id})" title="Remove">✕</button>
      </td>
      <td class="text-center text-muted">${fmt(item.price)}</td>
      <td class="text-center">
        <div class="d-flex align-items-center justify-content-center gap-1">
          <button class="btn btn-outline-secondary qty-btn" onclick="updateQty(${item.item_id}, -1)">−</button>
          <input id="qty-${item.item_id}" type="number" min="0" value="${item.qty}"
                 class="form-control form-control-sm text-center p-0"
                 style="width:40px"
                 onchange="setQty(${item.item_id}, this.value)">
          <button class="btn btn-outline-secondary qty-btn" onclick="updateQty(${item.item_id}, 1)">+</button>
        </div>
      </td>
      <td class="text-end fw-semibold">${fmt(item.line_total)}</td>
    </tr>
  `).join('');

  tfoot.innerHTML = `
    <tr class="tax-row">
      <td colspan="3" class="text-end">Net (ex. tax):</td>
      <td class="text-end">${fmt(totals.net)}</td>
    </tr>
    <tr class="tax-row">
      <td colspan="3" class="text-end">Tax (${totals.tax_rate}%):</td>
      <td class="text-end">${fmt(totals.tax)}</td>
    </tr>
    <tr class="total-row table-dark">
      <td colspan="3" class="text-end">Total (Incl. Tax):</td>
      <td class="text-end">${fmt(totals.total_incl_tax)}</td>
    </tr>
  `;
}

async function setQty(itemId, val) {
  const qty = Math.max(0, parseInt(val) || 0);
  const data = await post('cart/update', { item_id: itemId, qty: qty });
  if (data.success) renderCart(data.cart, data.totals);
}

(async () => {
  const data = await get('cart/data');
  renderCart(data.cart, data.totals);
})();
</script>
</body>
</html>