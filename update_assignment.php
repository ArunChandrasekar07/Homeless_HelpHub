<?php
    
session_start();
include 'db.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer'){
    header("Location: index.html");
    exit;
}

$assignment_id = $_POST['assignment_id'] ?? '';
$status = $_POST['status'] ?? '';

if($assignment_id && in_array($status, ['In Progress','Completed'])){
    $stmt = $conn->prepare("UPDATE assignments SET status=? WHERE id=?");
    $stmt->bind_param("si", $status, $assignment_id);
    $stmt->execute();
}

header("Location: volunteer_dashboard.php");
exit;
