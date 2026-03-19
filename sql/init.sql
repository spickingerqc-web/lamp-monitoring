-- ============================================================
-- init.sql  :  iot_db 및 sensor_data 테이블 초기화
-- 실행 방법 : sudo mysql -u root -p < sql/init.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS iot_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE iot_db;

CREATE TABLE IF NOT EXISTS sensor_data (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    temperature FLOAT   NOT NULL COMMENT '온도 (°C)',
    humidity    FLOAT   NOT NULL COMMENT '습도 (%)',
    pressure    FLOAT   NOT NULL COMMENT '기압 (hPa)',
    recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '측정 시각'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 테스트용 초기 데이터 (선택)
INSERT INTO sensor_data (temperature, humidity, pressure) VALUES
    (22.5, 55.3, 1013.2),
    (23.1, 54.8, 1012.9),
    (21.8, 56.0, 1013.5);

SELECT 'iot_db.sensor_data 초기화 완료' AS status;
