<?php
// Authentication bootstrap
require_once __DIR__ . '/config/Config.php';
require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/Session.php';
require_once __DIR__ . '/config/CSRF.php';
require_once __DIR__ . '/config/Admin.php';

Config::load(__DIR__ . '/.env');
Session::init();

if (!Session::isAuthenticated()) {
    header('Location: admin_login.php');
    exit;
}
