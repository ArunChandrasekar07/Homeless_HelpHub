<?php
session_start();
include 'db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'volunteer'){
    header("Location: index.html");
    exit;
}

$volunteer_id = $_SESSION['user_id'];

// Volunteer info
$volunteer_stmt = $conn->prepare("SELECT fullname, email, phone FROM users WHERE id=?");
$volunteer_stmt->bind_param("i", $volunteer_id);
$volunteer_stmt->execute();
$volunteer_res = $volunteer_stmt->get_result()->fetch_assoc();

// Available requests
$requests_stmt = $conn->prepare("
    SELECT hr.*, u.fullname AS user_name, u.email AS user_email, u.phone AS user_phone,
           g.fullname AS gov_name, g.email AS gov_email
    FROM help_requests hr
    JOIN users u ON hr.user_id = u.id
    LEFT JOIN gov_employees g ON hr.approved_by = g.id
    WHERE hr.status='Approved' AND hr.id NOT IN (SELECT request_id FROM assignments)
    ORDER BY hr.created_at DESC
");
$requests_stmt->execute();
$requests_res = $requests_stmt->get_result();

// Assigned requests
$assigned_stmt = $conn->prepare("
    SELECT a.id AS assignment_id, hr.*, 
           u.fullname AS user_name, u.email AS user_email, u.phone AS user_phone,
           g.fullname AS gov_name, g.email AS gov_email, a.status AS assignment_status, a.created_at AS assigned_at
    FROM assignments a
    JOIN help_requests hr ON a.request_id = hr.id
    JOIN users u ON hr.user_id = u.id
    LEFT JOIN gov_employees g ON hr.approved_by = g.id
    WHERE a.volunteer_id = ?
    ORDER BY a.created_at DESC
");
$assigned_stmt->bind_param("i", $volunteer_id);
$assigned_stmt->execute();
$assigned_res = $assigned_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Volunteer Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
  /* Responsive table */
  @media (max-width: 768px) {
    table thead { display: none; }
    table tbody tr { display: block; margin-bottom: 1rem; border: 1px solid #ddd; border-radius: 0.5rem; overflow: hidden; }
    table tbody td { display: flex; justify-content: space-between; padding: 0.5rem; border: none; }
    table tbody td::before { content: attr(data-label); font-weight: 600; color: #555; }
  }
</style>
</head>
<body class="bg-gray-50 min-h-screen p-6">

<!-- Header -->
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-indigo-700">Volunteer Dashboard 👐</h1>
    <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">Logout</a>
</div>


<!-- Available Requests -->
<section class="mb-8 bg-white shadow-md rounded-2xl p-6">
    <h2 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">Available Requests</h2>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse border text-sm">
            <thead>
                <tr class="bg-indigo-100 text-gray-800">
                    <th class="p-3 border">User</th>
                    <th class="p-3 border">Title</th>
                     <th class="p-3 border">Description</th>
        <th class="p-3 border">Quantity Required</th>
                     <th class="p-3 border">Request Date</th>
        <th class="p-3 border">Required Date</th>
                    <th class="p-3 border">Location</th>
                    <th class="p-3 border">Gov Approver</th>
                    <th class="p-3 border">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if($requests_res->num_rows > 0): ?>
                <?php while($row = $requests_res->fetch_assoc()): ?>
                <tr class="hover:bg-indigo-50 transition">
                    <td class="p-3 border" data-label="User">
                        <b><?= htmlspecialchars($row['user_name'] ?? '-') ?></b><br>
                        <span class="text-gray-500 text-xs">Email: <?= htmlspecialchars($row['user_email'] ?? '-') ?></span><br>
                        <span class="text-gray-500 text-xs">Phone: <?= htmlspecialchars($row['user_phone'] ?? 'N/A') ?></span>
                    </td>
                    <td class="p-3 border" data-label="Title"><?= htmlspecialchars($row['title'] ?? '-') ?></td>
                     <td class="p-3 border"><?= htmlspecialchars($row['description']) ?></td>
        <td class="p-3 border text-center"><?= htmlspecialchars($row['people_required'] ?? '-') ?></td>
                    <td class="p-3 border" data-label="Request Date"><?= date("d M Y, H:i", strtotime($row['created_at'])) ?></td>
<td class="p-3 border text-center">
    <?= !empty($row['required_date']) ? date('d M Y', strtotime($row['required_date'])) : '-' ?>
</td>
                    <td class="p-3 border" data-label="Location"><?= htmlspecialchars($row['location'] ?? '-') ?></td>
                    <td class="p-3 border" data-label="Gov Approver">
                        <?= htmlspecialchars($row['gov_name'] ?? '-') ?><br>
                        <span class="text-gray-500 text-xs"><?= htmlspecialchars($row['gov_email'] ?? '-') ?></span>
                    </td>
                    <td class="p-3 border text-center" data-label="Action">
                        <form method="POST" action="assign_request.php">
                            <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700 transition">Take Request</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center p-4 text-gray-500">No requests available.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Assigned Requests -->
<section class="bg-white shadow-md rounded-2xl p-6">
    <h2 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">Your Assigned Requests</h2>
    <div class="overflow-x-auto">
        <table class="w-full border-collapse border text-sm">
            <thead>
                <tr class="bg-indigo-100 text-gray-800">
                    <th class="p-3 border">User</th>
                    <th class="p-3 border">Title</th>
                    <th class="p-3 border">Location</th>
                    <th class="p-3 border">Status</th>
                    <th class="p-3 border">Gov Approver</th>
                    <th class="p-3 border">Assigned At</th>
                    <th class="p-3 border">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if($assigned_res->num_rows > 0): ?>
                <?php while($row = $assigned_res->fetch_assoc()): ?>
                <tr class="hover:bg-indigo-50 transition">
                    <td class="p-3 border" data-label="User">
                        <b><?= htmlspecialchars($row['user_name'] ?? '-') ?></b><br>
                        <span class="text-gray-500 text-xs">Email: <?= htmlspecialchars($row['user_email'] ?? '-') ?></span><br>
                        <span class="text-gray-500 text-xs">Phone: <?= htmlspecialchars($row['user_phone'] ?? 'N/A') ?></span>
                    </td>
                    <td class="p-3 border" data-label="Title"><?= htmlspecialchars($row['title'] ?? '-') ?></td>
                    <td class="p-3 border" data-label="Location"><?= htmlspecialchars($row['location'] ?? '-') ?></td>
                    <td class="p-3 border font-semibold <?= $row['assignment_status']=='Completed'?'text-green-600':'text-yellow-600' ?>" data-label="Status">
                        <?= htmlspecialchars($row['assignment_status'] ?? '-') ?>
                    </td>
                    <td class="p-3 border" data-label="Gov Approver"><?= htmlspecialchars($row['gov_name'] ?? '-') ?></td>
                    <td class="p-3 border" data-label="Assigned At"><?= date("d M Y, H:i", strtotime($row['assigned_at'])) ?></td>
                    <td class="p-3 border text-center" data-label="Action">
                        <form method="POST" action="update_assignment.php" class="flex gap-2 justify-center">
                            <input type="hidden" name="assignment_id" value="<?= $row['assignment_id'] ?>">
                            <select name="status" class="border px-2 py-1 rounded">
                                <option value="In Progress" <?= $row['assignment_status']=='In Progress'?'selected':'' ?>>In Progress</option>
                                <option value="Completed" <?= $row['assignment_status']=='Completed'?'selected':'' ?>>Completed</option>
                            </select>
                            <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center p-4 text-gray-500">No assigned requests yet.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Watermark -->
<div id="footerMark" style="
     position: fixed;
    bottom: 12px;
    right: 20px;
    font-family: 'Palatino Linotype', 'Georgia', serif;
    font-size: 14px;
    font-style: italic;
    font-weight: 500;
    color: rgba(0,0,0,0.2);
    letter-spacing: 0.7px;
    text-shadow: 0 0 1px rgba(0,0,0,0.1);
    pointer-events: none;
    user-select: none;
    z-index: 9999;
">
    Crafted by Arun
</div>
</body>
</html>
