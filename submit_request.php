<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user'){
    header("Location: index.html");
    exit;
}

$title = $_POST['title'] ?? '';
$description = $_POST['description'] ?? '';
$location = $_POST['location'] ?? '';
$people_required = $_POST['people_required'] ?? 1;
$required_date = $_POST['required_date'] ?? null;
$user_id = $_SESSION['user_id'];

// Prevent duplicate requests (same title + location within 7 days)
$check = $conn->prepare("
    SELECT * FROM help_requests 
    WHERE user_id=? AND title=? AND location=? 
    AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
");
$check->bind_param("iss", $user_id, $title, $location);
$check->execute();
$dup = $check->get_result();

if($dup->num_rows > 0){
    echo "<script>
        alert('You already submitted a similar request recently. Please wait before submitting again.');
        window.location='user_dashboard.php';
    </script>";
    exit;
}

if($title && $description && $location && $required_date){
    $stmt = $conn->prepare("
        INSERT INTO help_requests 
        (title, description, location, people_required, required_date, user_id, status) 
        VALUES (?, ?, ?, ?, ?, ?, 'Pending')
    ");
    $stmt->bind_param("sssisi", $title, $description, $location, $people_required, $required_date, $user_id);
    $stmt->execute();
}

header("Location: user_dashboard.php");
exit;
?>
