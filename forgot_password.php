<?php
// ... header and config code ...

if (!empty($data['email'])) {
    $email = trim($data['email']);
    $userFound = false;
    $targetTable = "";

    // 1. Try searching 'customer' table first
    $tables = ['customer', 'employee']; // List of tables to check
    
    foreach ($tables as $table) {
        $queryUrl = SUPABASE_URL . "/rest/v1/$table?email_address=eq." . urlencode($email) . "&select=*";
        
        $ch = curl_init($queryUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: " . SUPABASE_KEY,
            "Authorization: Bearer " . SUPABASE_KEY
        ]);
        
        $response = curl_exec($ch);
        $result = json_decode($response, true);
        curl_close($ch);

        if (!empty($result)) {
            $userFound = true;
            $user = $result[0];
            $targetTable = $table;
            break; // Stop searching once we find the user
        }
    }

    if ($userFound) {
        // Logic for sending SMS or Gmail instructions
        logAction("FORGOT PASSWORD: Valid request for [$email] found in [$targetTable] table.");
        echo json_encode([
            "success" => true,
            "message" => "Account found in $targetTable records. Reset instructions sent."
        ]);
    } else {
        logAction("FORGOT PASSWORD: Fail for [$email]. User not in any table.");
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "This email is not registered."]);
    }
}