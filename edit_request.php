<?php
session_start();
include 'db.php';

// Temporary helpful debugging: remove or set to 0 on production
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit;
}

$user_id = $_SESSION['user_id'];
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if($id <= 0){
    echo "Invalid request id.";
    exit;
}

// Fetch the existing request
$stmt = $conn->prepare("SELECT * FROM help_requests WHERE id=? AND user_id=?");
if(!$stmt){
    die("Prepare failed (fetch): " . $conn->error);
}
$stmt->bind_param("ii", $id, $user_id);
if(!$stmt->execute()){
    die("Execute failed (fetch): " . $stmt->error);
}
$res = $stmt->get_result();
if($res->num_rows === 0){
    echo "Request not found or unauthorized access!";
    exit;
}
$request = $res->fetch_assoc();

// Update request on submit
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    // Basic sanitize/trim
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $loc = trim($_POST['location'] ?? '');
    $people_required = isset($_POST['people_required']) ? intval($_POST['people_required']) : 1;
    $required_date = trim($_POST['required_date'] ?? '');

    // Validate required fields
    if($title === '' || $desc === '' || $loc === ''){
        echo "<script>alert('Title, description and location are required.');history.back();</script>";
        exit;
    }

    // Validate date (allow empty -> set to NULL)
    if($required_date === '') {
        $required_date_sql = null;
    } else {
        $d = date_parse($required_date);
        if(!checkdate($d['month'], $d['day'], $d['year'])){
            echo "<script>alert('Invalid required date.');history.back();</script>";
            exit;
        }
        $required_date_sql = $required_date; // format YYYY-MM-DD expected from input[type=date]
    }

    // Prepare update (use ? placeholders). Set edited=1 to mark edited requests.
    $update_sql = "
        UPDATE help_requests
        SET title = ?, description = ?, location = ?, people_required = ?, required_date = ?, updated_at = 1
        WHERE id = ? AND user_id = ?
    ";

    $update = $conn->prepare("
        UPDATE help_requests 
        SET title=?, description=?, location=?, people_required=?, required_date=?, updated_at=1, status='Pending'
        WHERE id=? AND user_id=?
    ");

    // bind_param requires a variable for each param. required_date can be NULL - bind as string but pass NULL if empty
    // types: s (title), s (desc), s (loc), i (quantity), s (required_date), i (id), i (user_id)
    // When passing NULL for a string, we need to bind a PHP null — mysqli will send SQL NULL.
    if($required_date_sql === null) {
        // We must pass null for the date param
        $update->bind_param("sssiiii", $title, $desc, $loc, $people_required, $required_date_sql, $id, $user_id);
        // Notice: some PHP/MySQL setups don't allow passing null with "s" type. If you get issues, switch to this branch:
        // $required_date_sql = null; $update->bind_param("sssiiii", $title, $desc, $loc, $people_required, $required_date_sql, $id, $user_id);
    } else {
        $update->bind_param("sssisii", $title, $desc, $loc, $people_required, $required_date_sql, $id, $user_id);
    }

    // Execute and check errors
    if(!$update->execute()){
        // show clear DB error (useful for debugging)
        die("Execute failed (update): " . $update->error);
    }

    // success -> redirect
    header("Location: user_dashboard.php?msg=updated");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Request</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">
  <div class="max-w-lg mx-auto bg-white shadow-md rounded-xl p-6">
    <h2 class="text-xl font-bold text-indigo-700 mb-4">✏️ Edit Help Request</h2>
    <form method="post">
        <label class="block text-sm font-semibold text-gray-700 mb-2">
                Request Title <span class="text-red-500">*</span>
            </label>
      <input name="title" type="text" required value="<?= htmlspecialchars($request['title']) ?>" class="w-full p-3 mb-3 border rounded-lg focus:ring-2 focus:ring-indigo-400">
        <label class="block text-sm font-semibold text-gray-700 mb-2">
                Detailed Description <span class="text-red-500">*</span>
            </label>
      <textarea name="description" required class="w-full p-3 mb-3 border rounded-lg focus:ring-2 focus:ring-indigo-400"><?= htmlspecialchars($request['description']) ?></textarea>
 <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Quantity Required <span class="text-red-500">*</span>
                </label>
      <input name="people_required" type="number" min="1" required value="<?= htmlspecialchars($request['people_required'] ?? 1) ?>" class="border p-3 rounded-lg w-full mb-3 focus:ring-2 focus:ring-indigo-400">
 <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Required Date <span class="text-red-500">*</span>
                </label>
      <input name="required_date" type="date" value="<?= htmlspecialchars($request['required_date']) ?>" class="border p-3 rounded-lg w-full mb-4 focus:ring-2 focus:ring-indigo-400">
 <label class="block text-sm font-semibold text-gray-700 mb-2">
                Location <span class="text-red-500">*</span>
            </label>
      <input name="location" type="text" required value="<?= htmlspecialchars($request['location']) ?>" class="w-full p-3 mb-4 border rounded-lg focus:ring-2 focus:ring-indigo-400">

      <div class="flex items-center">
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg">Update</button>
        <a href="user_dashboard.php" class="ml-4 text-gray-600 hover:underline">Cancel</a>
      </div>
    </form>
  </div>
</body>
</html>
