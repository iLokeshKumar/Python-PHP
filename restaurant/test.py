from db import SessionLocal

try:
    db = SessionLocal()
    print("Database Connected Successfully")
except Exception as e:
    print(e)