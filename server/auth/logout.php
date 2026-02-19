<?php
require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../lib/session.php';

session_unset();
session_destroy();

header('Location: ' . BASE_URL . '/client/login.php');
exit;
