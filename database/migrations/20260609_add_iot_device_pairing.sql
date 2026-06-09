USE db_fresh_farm;

ALTER TABLE iot_devices
    MODIFY user_id INT(11) NULL,
    ADD COLUMN pairing_code_hash VARCHAR(255) NULL AFTER api_key_hash,
    ADD COLUMN claimed_at DATETIME NULL AFTER pairing_code_hash,
    ADD KEY idx_iot_devices_pairing (device_uid, user_id);

UPDATE iot_devices
SET claimed_at = COALESCE(claimed_at, created_at)
WHERE user_id IS NOT NULL;

