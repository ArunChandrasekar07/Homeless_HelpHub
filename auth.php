<?php
    
session_start(); // Important! Start PHP session
header('Content-Type: application/json');
$servername = "sql113.infinityfree.com";   // MySQL Host Name
$username   = "if0_40204678";              // MySQL User Name
$password   = "q3ckJSoieFvg";              // MySQL Password
$dbname     = "if0_40204678_homeless_hub"; // Database Name

$conn = new mysqli($servername, $username, $password, $dbname);
if($conn->connect_error){
    echo json_encode(["status"=>"error", "message"=>"Database connection failed"]);
    exit;
}

// Get POST data
$action = $_POST['action'] ?? '';
$role = $_POST['role'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$fullname = $_POST['fullname'] ?? '';

$tables = [
    "user" => "users",
    "donor" => "donors",
    "gov" => "gov_employees",
    "volunteer" => "volunteers"
];

// Role validation
if(!isset($tables[$role])){
    echo json_encode(["status" => "error", "message" => "Invalid role"]);
    exit;
}

$table = $tables[$role];

// --- SIGNUP ---
if($action === 'signup'){
    $check = $conn->prepare("SELECT * FROM $table WHERE email=?");
    $check->bind_param("s", $email);
    $check->execute();
    $res = $check->get_result();

    if($res->num_rows > 0){
        echo json_encode(["status" => "error", "message" => "Email already exists!"]);
        exit;
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);
if($role === 'user'){
    // Generate random 10-digit phone number as string
    $phone = (string)mt_rand(1000000000, 9999999999);
    $stmt = $conn->prepare("INSERT INTO $table (fullname, email, password, phone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fullname, $email, $hash, $phone);
} else {
    $stmt = $conn->prepare("INSERT INTO $table (fullname, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $fullname, $email, $hash);
}


    if($stmt->execute()){
        echo json_encode(["status" => "success", "message" => ucfirst($role)." account created successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Signup failed!"]);
    }
    exit;
}

// --- LOGIN ---
if($action === 'login'){
    $stmt = $conn->prepare("SELECT * FROM $table WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows == 0){
        echo json_encode(["status" => "error", "message" => "Account not found!"]);
        exit;
    }

    $row = $res->fetch_assoc();
    if(password_verify($password, $row['password'])){
        // ✅ Set session variables
        $_SESSION['user_id'] = $row['id'];   // make sure your table has 'id' column
        $_SESSION['role'] = $role;

        echo json_encode([
            "status" => "success",
            "role" => $role,
            "message" => "Login successful!"
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid password!"]);
    }
    exit;
}

// Invalid action fallback
echo json_encode(["status" => "error", "message" => "Invalid request"]);
?>
