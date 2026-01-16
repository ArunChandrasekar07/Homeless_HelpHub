<?php
    
session_start();
include 'db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer'){
    header("Location: index.html");
    exit;
}

$volunteer_id = $_SESSION['user_id'];
$request_id = $_POST['request_id'] ?? '';

if($request_id){
    $stmt = $conn->prepare("INSERT INTO assignments (request_id, volunteer_id, status, created_at) VALUES (?, ?, 'In Progress', NOW())");
    $stmt->bind_param("ii", $request_id, $volunteer_id);
    $stmt->execute();
}

header("Location: volunteer_dashboard.php");
exit;
