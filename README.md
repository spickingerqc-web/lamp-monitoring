# LAMP Real-Time IoT Sensor Monitoring

> 3주차 캡스톤 디자인 & 임베디드 리눅스 과제
> VMware + Ubuntu 24.04 위에 LAMP 스택을 구축하고, Python으로 생성한 가상 센서 데이터를 MySQL에 저장하여 PHP 웹 페이지로 실시간 모니터링하는 시스템

---

## 시스템 구성

```
┌─────────────────────────────────────────────┐
│         VMware (Host: Windows)              │
│  ┌──────────────────────────────────────┐   │
│  │         Ubuntu 24.04 VM              │   │
│  │                                      │   │
│  │  injector.py ──INSERT──► MySQL       │   │
│  │                           │          │   │
│  │  Browser ──GET──► Apache ─┤          │   │
│  │                  PHP ◄────┘          │   │
│  └──────────────────────────────────────┘   │
└─────────────────────────────────────────────┘
```

---

## 파일 구조

```
project2/
├── README.md               ← 이 파일
├── project.md              ← 프로젝트 개요
├── process.md              ← 시스템 설명 + Mermaid 블록도
├── submission.txt          ← 동작 영상 URL + GitHub repo
├── sql/
│   └── init.sql            ← DB/테이블 초기화
├── python/
│   └── injector.py         ← 가상 센서 데이터 주입 (5초 간격)
├── php/
│   └── monitor.php         ← 실시간 모니터링 웹페이지
└── setup/
    └── lamp_setup.sh       ← LAMP 자동 설치 스크립트
```

---

## 설치 방법

### 1. LAMP 자동 설치 (Ubuntu 24.04)

```bash
chmod +x setup/lamp_setup.sh
sudo ./setup/lamp_setup.sh
```

### 2. 수동 설치

```bash
# Apache, MySQL, PHP 설치
sudo apt update
sudo apt install -y apache2 mysql-server php libapache2-mod-php php-mysql

# Python MySQL 드라이버
sudo apt install -y python3-pymysql
```

---

## 사용 방법

### 1. DB 초기화

```bash
mysql -u root -plinux < sql/init.sql
```

### 2. 데이터 주입 (터미널 1)

```bash
python3 python/injector.py
```

5초마다 온도·습도·기압 랜덤 값을 MySQL에 INSERT합니다.

```
[2026-03-19 14:44:22] INSERT → temp=26.47°C  hum=30.96%  pres=1016.43hPa
[2026-03-19 14:44:27] INSERT → temp=2.03°C   hum=25.79%  pres=981.87hPa
```

### 3. 모니터링 페이지 배포

```bash
sudo cp php/monitor.php /var/www/html/monitor.php
```

브라우저에서 접속:
```
http://<VM_IP>/monitor.php
```

---

## 데이터베이스 스키마

| 컬럼 | 타입 | 설명 |
|---|---|---|
| id | INT AUTO_INCREMENT | PK |
| temperature | FLOAT | 온도 (°C, -10 ~ 50) |
| humidity | FLOAT | 습도 (%, 20 ~ 90) |
| pressure | FLOAT | 기압 (hPa, 950 ~ 1050) |
| recorded_at | DATETIME | 측정 시각 (자동) |

---

## 모니터링 화면 기능

- 최신 센서 값 카드 (온도 / 습도 / 기압)
- 온도 추이 라인 차트
- 습도 & 기압 이중 축 라인 차트
- 최신 50건 데이터 테이블
- **30초마다 자동 새로고침**

---

## 기술 스택

| 구성 | 버전 |
|---|---|
| OS | Ubuntu 24.04 LTS |
| Apache | 2.4 |
| MySQL | 8.0 |
| PHP | 8.3 |
| Python | 3.12 |
| Chart.js | 4.x |

---

## 관련 문서

- [process.md](./process.md) — 전체 시스템 흐름 및 Mermaid 블록도
- [project.md](./project.md) — 과제 개요 및 수행 절차
