<?php
session_start();
include __DIR__ . '/backend/db.php';

header('Content-Type: application/json'); // ✅ Always send JSON response

$response = ['success' => false, 'message' => 'Unknown error'];

if(isset($_POST['email']) && isset($_POST['password'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, fullname, password FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        if(password_verify($password, $user['password'])) {
            $_SESSION['donor_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = 'donor';

            $response = ['success' => true, 'redirect' => 'user_dashboard.php'];
        } else {
            $response = ['success' => false, 'message' => 'Invalid password'];
        }
    } else {
        $response = ['success' => false, 'message' => 'User not found'];
    }
} else {
    $response = ['success' => false, 'message' => 'Missing email or password'];
}

echo json_encode($response);
?>
