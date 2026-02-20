<?php
require 'config.php';

// The password you want everyone to have for testing
$new_password = 'password123';
$new_hash = password_hash($new_password, PASSWORD_BCRYPT);

try {
    // 1. Ensure the column is long enough to hold the full hash
    $pdo->exec("ALTER TABLE employee MODIFY password_hash VARCHAR(255)");

    // 2. Update every single row in the employee table
    $stmt = $pdo->prepare("UPDATE employee SET password_hash = ?, employee_status = 'Active'");
    $stmt->execute([$new_hash]);

    $count = $stmt->rowCount();
    echo "Success! " . $count . " employees now have the password: " . $new_password;
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
?>