<?php
// Basic validation helpers (prototype-level)

function isPositiveInt($value): bool {
    return is_numeric($value) && (int)$value > 0;
}

function sanitizeString(string $value): string {
    return trim(htmlspecialchars($value, ENT_QUOTES, 'UTF-8'));
}

function requirePostFields(array $fields): bool {
    foreach ($fields as $field) {
        if (!isset($_POST[$field]) || $_POST[$field] === '') {
            return false;
        }
    }
    return true;
}
