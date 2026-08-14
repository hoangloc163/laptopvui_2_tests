<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from .env if present
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = new Symfony\Component\Dotenv\Dotenv();
    $dotenv->load(__DIR__ . '/../.env');
}

// Default APP_URL
if (!getenv('APP_URL')) {
    putenv('APP_URL=http://localhost:8000');
}

// Timezone for date-based tests
date_default_timezone_set('Asia/Ho_Chi_Minh');
