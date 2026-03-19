# Project Overview — Week 3 Capstone Design & Embedded Linux

## 목적

VMware 위에 Ubuntu 24.04를 설치하고, LAMP(Linux + Apache + MySQL + PHP) 스택을 구성한 뒤
Python 스크립트로 가상 센서 데이터를 생성하여 MySQL에 저장하고,
PHP 기반 동적 HTML 페이지로 실시간 모니터링하는 웹 시스템을 구축한다.

## 구성 요소

| 구성 요소 | 역할 |
|---|---|
| VMware + Ubuntu 24.04 | 실습 환경 (LAMP 서버) |
| Apache2 | 웹 서버 |
| MySQL 8 | 데이터 저장소 |
| PHP 8 | 동적 HTML 생성 |
| Python 3 (`injector.py`) | 가상 센서 데이터 주입 |
| `monitor.php` | 실시간 데이터 시각화 페이지 |
| `process.md` | 시스템 설명 및 Mermaid 블록도 |

## 수행 절차

1. VMware에 Ubuntu 24.04 VM 생성
2. LAMP 스택 설치 및 설정
3. MySQL DB/테이블 생성 (`iot_db.sensor_data`)
4. `injector.py` 실행 → 주기적으로 가짜 센서 값 INSERT
5. `monitor.php` 접속 → 실시간 테이블/차트 확인
6. GitHub 레포지토리 생성 후 `git push`
7. 동작 영상 + repo 이름 `submission.txt`에 기록
8. `process.md`에 전체 흐름 및 Mermaid 다이어그램 작성

## 파일 구조

```
project2/
├── project.md          ← 이 파일 (프로젝트 개요)
├── process.md          ← 시스템 설명 + Mermaid 블록도
├── submission.txt      ← 동작 영상 URL + GitHub repo 이름
├── sql/
│   └── init.sql        ← DB/테이블 초기화 스크립트
├── python/
│   └── injector.py     ← 가상 데이터 주입 스크립트
├── php/
│   └── monitor.php     ← 실시간 모니터링 페이지
└── setup/
    └── lamp_setup.sh   ← LAMP 자동 설치 스크립트
```
