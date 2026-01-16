<?php

session_start();
include 'db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'gov'){
    header("Location: index.html");
    exit;
}

$request_id = $_POST['request_id'] ?? '';
$action = $_POST['action'] ?? '';

if($request_id && in_array($action, ['approve','reject'])){
    $status = $action === 'approve' ? 'Approved' : 'Rejected';
    $approved_by = $action === 'approve' ? $_SESSION['user_id'] : NULL;

    $stmt = $conn->prepare("UPDATE help_requests SET status=?, approved_by=? WHERE id=?");
    $stmt->bind_param("sii", $status, $approved_by, $request_id);
    $stmt->execute();
}

header("Location: gov_dashboard.php");
exit;
