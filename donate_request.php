<?php
    
session_start();
include 'db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor'){
    header("Location: index.html");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $donor_id = $_SESSION['user_id']; // use user_id from session
    $donated_by = $_SESSION['fullname'] ?? 'Donor'; // fallback name
    $request_id = $_POST['request_id'] ?? null;
    $amount = $_POST['amount'] ?? 0;

    if(!$request_id || $amount <= 0){
        die("Invalid donation details!");
    }

    $stmt = $conn->prepare("INSERT INTO donations (donor_id, donated_by, request_id, amount) VALUES (?, ?, ?, ?)");
    if(!$stmt){
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("isid", $donor_id, $donated_by, $request_id, $amount);

    if($stmt->execute()){
        header("Location: donor_dashboard.php?success=Donation+successful");
        exit;
    } else {
        die("Error saving donation: " . $conn->error);
    }
}
$update = $conn->prepare("
    UPDATE help_requests 
    SET status = 'Approved', 
        volunteer_id = ?, 
        approved_at = NOW()
    WHERE id = ?
");
$update->bind_param("ii", $volunteer_id, $request_id);
$update->execute();

header("Location: volunteer_dashboard.php");
?>
