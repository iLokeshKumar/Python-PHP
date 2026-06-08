from sqlalchemy import Column, Integer, String, Float, Index
from db import Base


class MenuGroup(Base):
    __tablename__ = "menu_groups"

    menu_id = Column(Integer, primary_key=True)
    menu_name = Column(String, nullable=False)


class Category(Base):
    __tablename__ = "categories"

    cat_id = Column(Integer, primary_key=True)
    category_name = Column(String, nullable=False)
    menu_id = Column(Integer, nullable=False)


class Menu(Base):
    __tablename__ = "menu"

    item_id = Column(Integer, primary_key=True)
    item_name = Column(String, nullable=False)
    cat_id = Column(Integer, nullable=False)
    menu_id = Column(Integer, nullable=False)
    size = Column(String, nullable=True)
    price = Column(String, nullable=False)


class OrderHistory(Base):
    __tablename__ = "order_history"

    id = Column(Integer, primary_key=True)
    order_date = Column(String, nullable=False)
    order_id = Column(Integer, nullable=False, index=True)
    item_id = Column(Integer, nullable=False)
    size = Column(String, nullable=True)
    price = Column(Float, nullable=False)
    qty = Column(Integer, nullable=False)
    order_status = Column(String, nullable=False)
    total = Column(Float, nullable=False)


class Payment(Base):
    __tablename__ = "payments"

    id = Column(Integer, primary_key=True)
    payment_date = Column(String, nullable=False)
    payment_id = Column(Integer, nullable=False, unique=True, index=True)
    order_id = Column(Integer, nullable=False, index=True)
    amount_due = Column(Float, nullable=False)
    tips = Column(Float, default=0.0)
    discount = Column(Float, default=0.0)
    total_paid = Column(Float, nullable=False)
    payment_type = Column(String, nullable=False)
    payment_status = Column(String, nullable=False)