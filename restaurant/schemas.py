from pydantic import BaseModel
from typing import Optional, List

class MenuItemOut(BaseModel):
    item_id: int
    item_name: str
    category: str
    menu_group: str
    sizes: Optional[str]
    prices: str

    model_config = {"from_attributes": True}


class OrderItemOut(BaseModel):
    row_id: int
    item_id: int
    item_name: str
    category: str
    menu_group: str
    size: Optional[str]
    unit_price: float
    qty: int
    line_total: float
    order_status: str

    model_config = {"from_attributes": True}


class PaymentOut(BaseModel):
    payment_id: int
    payment_date: str
    amount_due: float
    tips: float
    discount: float
    total_paid: float
    payment_type: str
    payment_status: str

    model_config = {"from_attributes": True}


class OrderDetailOut(BaseModel):
    order_id: int
    order_date: str
    order_total: float
    total_paid: float
    balance: float
    items: List[OrderItemOut]
    payments: List[PaymentOut]

    model_config = {"from_attributes": True}