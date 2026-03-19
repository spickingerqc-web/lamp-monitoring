#!/usr/bin/env python3
"""
injector.py
-----------
가상 센서 데이터(온도·습도·기압)를 5초 간격으로 생성하여
MySQL iot_db.sensor_data 테이블에 INSERT한다.

실행 방법:
    sudo apt install python3-pymysql
    python3 injector.py

종료:
    Ctrl+C
"""

import time
import random
import pymysql
from datetime import datetime

# ── DB 접속 정보 (환경에 맞게 수정) ──────────────────────────
DB_CONFIG = {
    "host":     "localhost",
    "port":     3306,
    "user":     "root",           # MySQL 사용자
    "password": "linux",           # MySQL 비밀번호
    "db":       "iot_db",
    "charset":  "utf8mb4",
}

# ── 데이터 생성 간격 (초) ──────────────────────────────────────
INTERVAL = 5


def generate_sensor_data() -> dict:
    """가상 센서 값을 랜덤으로 생성한다."""
    return {
        "temperature": round(random.uniform(-10.0, 50.0), 2),  # °C
        "humidity":    round(random.uniform(20.0, 90.0),  2),  # %
        "pressure":    round(random.uniform(950.0, 1050.0), 2),  # hPa
    }


def insert_data(cursor, data: dict) -> None:
    sql = """
        INSERT INTO sensor_data (temperature, humidity, pressure)
        VALUES (%(temperature)s, %(humidity)s, %(pressure)s)
    """
    cursor.execute(sql, data)  # pymysql도 %(key)s 지원


def main():
    print("injector.py 시작 — Ctrl+C 로 종료")
    try:
        conn = pymysql.connect(**DB_CONFIG)
        cursor = conn.cursor()
        print(f"MySQL 연결 성공: {DB_CONFIG['host']} / {DB_CONFIG['db']}")
    except pymysql.Error as err:
        print(f"[ERROR] DB 연결 실패: {err}")
        return

    try:
        while True:
            data = generate_sensor_data()
            insert_data(cursor, data)
            conn.commit()
            ts = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
            print(
                f"[{ts}] INSERT → "
                f"temp={data['temperature']}°C  "
                f"hum={data['humidity']}%  "
                f"pres={data['pressure']}hPa"
            )
            time.sleep(INTERVAL)

    except KeyboardInterrupt:
        print("\n종료 요청 — 연결을 닫습니다.")
    finally:
        cursor.close()
        conn.close()
        print("DB 연결 종료.")


if __name__ == "__main__":
    main()
