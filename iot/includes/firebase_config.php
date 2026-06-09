<?php

$iot_firebase_database_url = rtrim(
    getenv('FRESHFARM_FIREBASE_DATABASE_URL')
        ?: 'https://freshfarm-iot-default-rtdb.asia-southeast1.firebasedatabase.app',
    '/'
);

