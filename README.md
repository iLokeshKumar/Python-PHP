# Restaurant Fullstack Work

Two implementations of the same restaurant dataset:
- **Python** — FastAPI + PostgreSQL (API)
- **PHP** — CodeIgniter 4 + MySQL (findings, API, Cart)

---

## Python — FastAPI

### Stack
- Python 3.12
- FastAPI 0.136 + Uvicorn
- SQLAlchemy 2.0 (ORM)
- PostgreSQL via psycopg2-binary
- Pydantic v2 schemas
- API Key authentication (`X-API-Key` header)

### Setup

```bash
cd E:\phase\restaurant

python -m venv .venv
.venv\Scripts\activate          # Windows
# source .venv/bin/activate     # Mac/Linux

pip install -r requirements.txt

# Create PostgreSQL database
# psql -U postgres -c "CREATE DATABASE restaurant;"

python seed_data.py

uvicorn main:app --reload --port 8000
```

### Environment

Set `DATABASE_URL` in `db.py` or via environment variable:
```
postgresql://postgres:<password>@localhost:5432/restaurant
```

Default API key: `restaurant2026`  
Override via env var: `API_KEY=your_key`

### Endpoints

| Method | URL | Auth | Description |
|--------|-----|------|-------------|
| GET | `/` | No | Health check |
| GET | `/orders` | Yes | All orders with items + payments |
| GET | `/orders/{order_id}` | Yes | Single order detail |
| GET | `/menu` | Yes | Full menu with categories |

**Authentication** — pass header on all protected routes:
```
X-API-Key: restaurant2026
```

### Postman

Import a request with:
- URL: `http://localhost:8000/orders`
- Header: `X-API-Key: restaurant2026`

Swagger UI available at: `http://localhost:8000/docs`

### File Structure

```
restaurant/
├── main.py          ← FastAPI app + route handlers
├── models.py        ← SQLAlchemy ORM models
├── schemas.py       ← Pydantic response schemas
├── db.py            ← DB connection + session
├── seed_data.py     ← Inserts sample data into PostgreSQL
├── test.py          ← Basic endpoint tests
└── requirements.txt
```

---

## PHP — CodeIgniter 4 (`E:\phase\restaurant-php`)

### Stack
- PHP 8.1+
- CodeIgniter 4
- MySQL 8+
- Bootstrap 5 (CDN, cart UI only)

### Setup

```bash
cd E:\phase\restaurant-php

composer create-project codeigniter4/appstarter . --no-dev

cp env .env
# Edit .env — set database.default.password and username

mysql -u root -p < sql/setup.sql

php spark serve
```

### .env (minimum config)

```ini
CI_ENVIRONMENT = development

database.default.hostname = localhost
database.default.database = restaurant_db
database.default.username = root
database.default.password = your_password
database.default.DBDriver = MySQLi
```

### Endpoints — Task 2 (Orders API)

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/api/orders` | All orders with items + payments |
| GET | `/api/orders/{id}` | Single order detail |

**Sample response — `/api/orders/10`:**
```json
{
  "status": "success",
  "data": {
    "order_id": 10,
    "order_date": "2025-10-01",
    "order_total": 9.25,
    "amount_due": 9.25,
    "total_paid": 9.25,
    "balance_remaining": 0,
    "payment_count": 1,
    "item_count": 4,
    "items": [
      {
        "id": 1,
        "item_id": 2,
        "item_name": "Item2",
        "category": "Starters",
        "menu": "Food",
        "size": null,
        "price": "2.50000",
        "qty": 1,
        "total": "2.50000",
        "order_status": "Completed"
      }
    ],
    "payments": [
      {
        "payment_id": 100,
        "payment_date": "2025-10-01",
        "amount_due": "9.25000",
        "tips": "0.00",
        "discount": "0.00",
        "total_paid": "9.2500",
        "payment_type": "Card",
        "payment_status": "Completed"
      }
    ]
  }
}
```

### Cart — Task 3

URL: `http://localhost:8080/cart`

| Method | URL | Description |
|--------|-----|-------------|
| GET | `/cart` | Cart page |
| GET | `/cart/data` | Current cart JSON (AJAX) |
| POST | `/cart/add` | Add item (`item_id`, `qty`) |
| POST | `/cart/update` | Change qty (`item_id`, `qty`) |
| POST | `/cart/remove` | Remove item (`item_id`) |
| POST | `/cart/clear` | Empty cart |

**Cart items (all prices inclusive of 12.5% tax):**

| Item | Unit Price | Tax (12.5% incl.) |
|------|-----------|-------------------|
| Item 1 — Grilled Chicken | £10.00 | £1.11 |
| Item 2 — Fish & Chips | £7.50 | £0.83 |
| Item 3 — Caesar Salad | £5.00 | £0.56 |
| Item 4 — Garlic Bread | £2.50 | £0.28 |
| Item 5 — Soft Drink | £3.00 | £0.33 |

Tax extracted from inclusive price: `tax = total × (12.5 / 112.5)`

### File Structure

```
restaurant-php/
├── sql/
│   └── setup.sql                        ← Full schema + all seed data
├── FINDINGS.md                          ← Task 1: data analysis findings
├── env                                  ← DB config template
└── app/
    ├── Config/Routes.php
    ├── Controllers/
    │   ├── Api/OrderController.php      ← Task 2 API (ResourceController)
    │   └── CartController.php           ← Task 3 cart (session-backed)
    ├── Models/
    │   └── OrderModel.php               ← Joins orders + items + payments
    └── Views/
        └── cart/index.php               ← Bootstrap cart UI
```

### Database Schema

```
menus ──< categories ──< menu_items ──< item_prices (size variants)
                                    └─< order_items >── orders
                                                         └─< payments
```

---

## Task 1 — Key Data Findings

1. **Row 40 missing** — order_items jumps ID 39→41 (voided line item)
2. **Split payments** — 6 orders paid via Cash + Card across multiple payment rows
3. **Price mismatch** — order_items prices differ from menu_items prices (historical/dynamic pricing at time of order)
4. **Refund** — Order 15 (Payment 107) is the only `Refunded` payment
5. **Duplicate items in Order 19** — same item appears multiple times at different prices (adjustment rows)
6. **Payment ID gaps** — IDs 112–114 and 116–118 missing (voided transactions)