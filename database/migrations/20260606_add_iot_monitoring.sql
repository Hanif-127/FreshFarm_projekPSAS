USE db_fresh_farm;

CREATE TABLE iot_devices (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    device_uid VARCHAR(80) NOT NULL,
    nama_perangkat VARCHAR(120) NOT NULL,
    lokasi VARCHAR(180) DEFAULT NULL,
    api_key_hash VARCHAR(255) NOT NULL,
    firmware_version VARCHAR(40) DEFAULT NULL,
    sampling_interval_seconds INT(11) NOT NULL DEFAULT 300,
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    last_seen_at DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uniq_iot_device_uid (device_uid),
    KEY idx_iot_devices_user (user_id),
    KEY idx_iot_devices_last_seen (last_seen_at),

    CONSTRAINT fk_iot_devices_user
        FOREIGN KEY (user_id)
        REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE iot_readings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    device_id INT(11) NOT NULL,

    air_temperature_c DECIMAL(5,2) DEFAULT NULL,
    air_humidity_pct DECIMAL(5,2) DEFAULT NULL,
    light_lux DECIMAL(12,2) DEFAULT NULL,

    soil_moisture_raw INT(11) DEFAULT NULL,
    soil_moisture_pct DECIMAL(5,2) DEFAULT NULL,
    soil_temperature_c DECIMAL(5,2) DEFAULT NULL,

    wifi_rssi INT(11) DEFAULT NULL,
    recorded_at DATETIME NOT NULL,
    received_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    KEY idx_iot_readings_device_time (device_id, recorded_at),
    KEY idx_iot_readings_received (received_at),

    CONSTRAINT fk_iot_readings_device
        FOREIGN KEY (device_id)
        REFERENCES iot_devices(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE iot_thresholds (
    id INT(11) NOT NULL AUTO_INCREMENT,
    device_id INT(11) NOT NULL,

    air_temperature_min DECIMAL(5,2) DEFAULT 18.00,
    air_temperature_max DECIMAL(5,2) DEFAULT 34.00,

    air_humidity_min DECIMAL(5,2) DEFAULT 40.00,
    air_humidity_max DECIMAL(5,2) DEFAULT 90.00,

    light_lux_min DECIMAL(12,2) DEFAULT 1000.00,
    light_lux_max DECIMAL(12,2) DEFAULT 80000.00,

    soil_moisture_min DECIMAL(5,2) DEFAULT 55.00,
    soil_moisture_max DECIMAL(5,2) DEFAULT 85.00,

    soil_temperature_min DECIMAL(5,2) DEFAULT 18.00,
    soil_temperature_max DECIMAL(5,2) DEFAULT 32.00,

    soil_dry_adc INT(11) DEFAULT 3200,
    soil_wet_adc INT(11) DEFAULT 1350,
    notification_enabled TINYINT(1) NOT NULL DEFAULT 1,

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uniq_iot_threshold_device (device_id),

    CONSTRAINT fk_iot_thresholds_device
        FOREIGN KEY (device_id)
        REFERENCES iot_devices(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;