import os
from typing import List

from fastapi import Depends, FastAPI, HTTPException, Security
from fastapi.security.api_key import APIKeyHeader
from sqlalchemy.orm import Session
from sqlalchemy import distinct

from db import SessionLocal
from models import Category, Menu, MenuGroup, OrderHistory, Payment
from schemas import MenuItemOut, OrderDetailOut

API_KEY = os.getenv("API_KEY", "restaurant2026")
api_key_header = APIKeyHeader(name="X-API-Key", auto_error=True)

app = FastAPI(
    title="Restaurant Orders API",
    description="Lists orders with full item and payment details. Pass X-API-Key header to authenticate.",
    version="0.0.1",
)

def get_db():
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()


def verify_api_key(key: str = Security(api_key_header)):
    if key != API_KEY:
        raise HTTPException(status_code=403, detail="Invalid API key")
    return key

def _build_order(order_id: int, db: Session) -> dict | None:
    first = db.query(OrderHistory).filter(OrderHistory.order_id == order_id).first()
    if not first:
        return None

    rows = (
        db.query(OrderHistory, Menu, Category, MenuGroup)
        .join(Menu, OrderHistory.item_id == Menu.item_id)
        .join(Category, Menu.cat_id == Category.cat_id)
        .join(MenuGroup, Menu.menu_id == MenuGroup.menu_id)
        .filter(OrderHistory.order_id == order_id)
        .order_by(OrderHistory.id)
        .all()
    )

    items = [
        {
            "row_id": oh.id,
            "item_id": oh.item_id,
            "item_name": m.item_name,
            "category": c.category_name,
            "menu_group": mg.menu_name,
            "size": oh.size,
            "unit_price": oh.price,
            "qty": oh.qty,
            "line_total": oh.total,
            "order_status": oh.order_status,
        }
        for oh, m, c, mg in rows
    ]

    payments_rows = (
        db.query(Payment)
        .filter(Payment.order_id == order_id)
        .order_by(Payment.payment_id)
        .all()
    )

    payments = [
        {
            "payment_id": p.payment_id,
            "payment_date": p.payment_date,
            "amount_due": p.amount_due,
            "tips": p.tips,
            "discount": p.discount,
            "total_paid": p.total_paid,
            "payment_type": p.payment_type,
            "payment_status": p.payment_status,
        }
        for p in payments_rows
    ]

    order_total = round(sum(i["line_total"] for i in items), 4)
    total_paid = round(sum(p["total_paid"] for p in payments), 4)

    return {
        "order_id": order_id,
        "order_date": first.order_date,
        "order_total": order_total,
        "total_paid": total_paid,
        "balance": round(order_total - total_paid, 4),
        "items": items,
        "payments": payments,
    }

@app.get("/", tags=["Health"])
def health():
    return {"status": "ok", "message": "Restaurant API Running"}


@app.get("/orders", tags=["Orders"])
def get_all_orders(
    db: Session = Depends(get_db),
    _: str = Security(verify_api_key),
):

    order_ids = (
        db.query(distinct(OrderHistory.order_id))
        .order_by(OrderHistory.order_id)
        .all()
    )
    return [_build_order(oid[0], db) for oid in order_ids]


@app.get("/orders/{order_id}", tags=["Orders"])
def get_order(
    order_id: int,
    db: Session = Depends(get_db),
    _: str = Security(verify_api_key),
):
    result = _build_order(order_id, db)
    if not result:
        raise HTTPException(status_code=404, detail=f"Order {order_id} not found")
    return result


@app.get("/menu", tags=["Menu"])
def get_menu(
    db: Session = Depends(get_db),
    _: str = Security(verify_api_key),
):
    rows = (
        db.query(Menu, Category, MenuGroup)
        .join(Category, Menu.cat_id == Category.cat_id)
        .join(MenuGroup, Menu.menu_id == MenuGroup.menu_id)
        .order_by(MenuGroup.menu_id, Category.cat_id, Menu.item_id)
        .all()
    )
    return [
        {
            "item_id": m.item_id,
            "item_name": m.item_name,
            "category": c.category_name,
            "menu_group": mg.menu_name,
            "sizes": m.size,
            "prices": m.price,
        }
        for m, c, mg in rows
    ]