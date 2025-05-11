<?php

return [
    'host' => getenv('DB_HOST') ?: 'localhost',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'database' => getenv('DB_DATABASE') ?: 'museum_db',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
]; 