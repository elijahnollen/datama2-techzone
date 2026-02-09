<?php
// Starts session safely (only once)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize cart session structure if missing
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [
        'items' => [] // productID => ['productID','product_name','unit_price','qty']
    ];
}
