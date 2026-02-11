<?php
// ... keep your existing headers and logAction function ...

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {
    $email = trim($data->email);
    $password = $data->password;
    // Capture the Remember Me flag from the design
    $rememberMe = isset($data->rememberMe) && $data->rememberMe ? true : false;

    // ... (Your existing table loop logic to find the user) ...

    if ($user) {
        if ($password === $user->password) {
            $role = ($foundTable === 'employee') ? $user->employee_role : 'customer';
            
            // LOG SUCCESS with persistence detail
            $logMsg = "SUCCESSFUL LOGIN: User [$email] as [$role]";
            if ($rememberMe) $logMsg .= " (Remember Me enabled)";
            logAction($logMsg);

            echo json_encode([
                "success" => true,
                "message" => "Login successful",
                "role" => $role,
                "persist" => $rememberMe // Functionally tells the system to stay logged in
            ]);
        } else {
            logAction("FAILED LOGIN: User [$email] - Incorrect password");
            echo json_encode(["success" => false, "message" => "Invalid password"]);
        }
    } else {
        logAction("FAILED LOGIN: Non-existent user [$email]");
        echo json_encode(["success" => false, "message" => "User not found"]);
    }
}