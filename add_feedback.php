<?php

session_start();
include 'db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'gov'){
    header("Location: index.html");
    exit;
}

if(isset($_POST['request_id']) && isset($_POST['feedback'])){
    $request_id = $_POST['request_id'];
    $feedback = $_POST['feedback'];

    $stmt = $conn->prepare("UPDATE help_requests SET feedback=? WHERE id=?");
    $stmt->bind_param("si", $feedback, $request_id);
    $stmt->execute();

    header("Location: gov_dashboard.php");
    exit;
} else {
    echo "Invalid request!";
}
