#!/bin/bash
# ============================================================
# lamp_setup.sh
# Ubuntu 24.04에 LAMP 스택 + Python 의존성 자동 설치 스크립트
# 실행 방법: chmod +x setup/lamp_setup.sh && sudo ./setup/lamp_setup.sh
# ============================================================

set -e  # 오류 발생 시 즉시 종료

echo "========================================"
echo "  LAMP Stack Auto-Install (Ubuntu 24.04)"
echo "========================================"

# 1. 패키지 목록 업데이트
echo "[1/7] apt update ..."
apt update -y

# 2. Apache2 설치
echo "[2/7] Apache2 설치 ..."
apt install -y apache2
systemctl enable apache2
systemctl start  apache2

# 3. MySQL 설치
echo "[3/7] MySQL 설치 ..."
apt install -y mysql-server
systemctl enable mysql
systemctl start  mysql

# 4. PHP 및 관련 모듈 설치
echo "[4/7] PHP 설치 ..."
apt install -y php libapache2-mod-php php-mysql php-mbstring php-xml

# 5. Apache 재시작 (PHP 모듈 적용)
echo "[5/7] Apache 재시작 ..."
systemctl restart apache2

# 6. Python 의존성 설치
echo "[6/7] Python 의존성 설치 ..."
apt install -y python3-pip python3-venv
pip3 install mysql-connector-python --break-system-packages 2>/dev/null || \
    pip3 install mysql-connector-python

# 7. MySQL DB 초기화
echo "[7/7] MySQL DB 초기화 (iot_db) ..."
SCRIPT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
mysql -u root < "${SCRIPT_DIR}/sql/init.sql"

# 8. monitor.php 배포
echo "[8/8] monitor.php → /var/www/html/ 배포 ..."
cp "${SCRIPT_DIR}/php/monitor.php" /var/www/html/monitor.php
chown www-data:www-data /var/www/html/monitor.php

echo ""
echo "========================================"
echo "  설치 완료!"
echo "  브라우저에서 http://$(hostname -I | awk '{print $1}')/monitor.php 접속"
echo "  injector.py 실행: python3 ${SCRIPT_DIR}/python/injector.py"
echo "========================================"
