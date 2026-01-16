<?php
session_start();
include 'db.php';

// Check login
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'gov'){
    header("Location: index.html");
    exit;
}

// Fetch all requests
$requests_stmt = $conn->prepare("
    SELECT hr.*, 
           u.fullname as user_name, u.email as user_email, u.phone as user_phone,
           g.fullname as gov_name
    FROM help_requests hr
    JOIN users u ON hr.user_id=u.id
    LEFT JOIN gov_employees g ON hr.approved_by = g.id
    ORDER BY created_at DESC
");
$requests_stmt->execute();
$requests_res = $requests_stmt->get_result();

// Dashboard stats
$stats_res = $conn->query("
    SELECT 
        SUM(status='Pending') as pending,
        SUM(status='Approved') as approved,
        SUM(status='Rejected') as rejected
    FROM help_requests
");
$stats = $stats_res->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Gov Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">

<!-- Header -->
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-indigo-700">Gov Employee Dashboard 🏛️</h1>
    <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">Logout</a>
</div>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-yellow-200 p-6 rounded-2xl shadow text-center">
        <h2 class="font-semibold text-lg">Pending Requests</h2>
        <p class="text-2xl font-bold"><?= $stats['pending'] ?></p>
    </div>
    <div class="bg-green-200 p-6 rounded-2xl shadow text-center">
        <h2 class="font-semibold text-lg">Approved Requests</h2>
        <p class="text-2xl font-bold"><?= $stats['approved'] ?></p>
    </div>
    <div class="bg-red-200 p-6 rounded-2xl shadow text-center">
        <h2 class="font-semibold text-lg">Rejected Requests</h2>
        <p class="text-2xl font-bold"><?= $stats['rejected'] ?></p>
    </div>
</div>

<!-- Requests Table -->
<div class="p-6 bg-white shadow-lg rounded-2xl border border-gray-200">
    <h2 class="font-semibold text-2xl mb-4 text-indigo-600">User Requests</h2>
    <table class="w-full border-collapse rounded overflow-hidden text-sm">
        <thead>
    <tr class="bg-gray-200">
        <th class="p-3 border">User</th>
        <th class="p-3 border">Email</th>
        <th class="p-3 border">Phone</th>
        <th class="p-3 border">Title</th>
        <th class="p-3 border">Description</th>
        <th class="p-3 border">Quantity Required</th>
        <th class="p-3 border">Required Date</th>
        <th class="p-3 border">Location</th>
        <th class="p-3 border">Status</th>
        <th class="p-3 border">Feedback</th>
        <th class="p-3 border">Approved By</th>
        <th class="p-3 border">Action</th>
    </tr>
</thead>
<tbody>
<?php if($requests_res->num_rows > 0): ?>
    <?php while($row = $requests_res->fetch_assoc()): ?>
    <tr class="hover:bg-gray-50 transition">
        <td class="p-3 border"><?= htmlspecialchars($row['user_name']) ?></td>
        <td class="p-3 border"><?= htmlspecialchars($row['user_email']) ?></td>
        <td class="p-3 border"><?= htmlspecialchars($row['user_phone'] ?? 'N/A') ?></td>
        <td class="p-3 border font-semibold">
            <?= htmlspecialchars($row['title']) ?>
            <?php if(!empty($row['updated_at'])): ?>
                <span class="text-xs text-gray-500 italic">(Edited)</span>
            <?php endif; ?>
        </td>
        <td class="p-3 border"><?= htmlspecialchars($row['description']) ?></td>
        <td class="p-3 border text-center"><?= htmlspecialchars($row['people_required'] ?? '-') ?></td>
        <td class="p-3 border text-center"><?= htmlspecialchars($row['required_date'] ?? '-') ?></td>
        <td class="p-3 border"><?= htmlspecialchars($row['location']) ?></td>
        <td class="p-3 border font-semibold <?= $row['status']=='Pending'?'text-yellow-600':($row['status']=='Approved'?'text-green-600':'text-red-600') ?>">
            <?= htmlspecialchars($row['status']) ?>
        </td>
        <td class="p-3 border">
        <?php if(!empty($row['feedback'])): ?>
            <span class="text-gray-700 font-medium"><?= htmlspecialchars($row['feedback']) ?></span>
        <?php else: ?>
            <form method="POST" action="add_feedback.php" class="flex gap-2">
                <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                <input type="text" name="feedback" placeholder="Add feedback" class="border rounded px-2 py-1 w-full focus:outline-none focus:ring focus:ring-indigo-300">
                <button class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700 transition">Save</button>
            </form>
        <?php endif; ?>
        </td>
        <td class="p-3 border"><?= htmlspecialchars($row['gov_name'] ?? '-') ?></td>
        <td class="p-3 border">
            <?php if($row['status']=='Pending'): ?>
                <form method="POST" action="approve_request.php" class="flex gap-2 justify-center">
                    <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                    <button name="action" value="approve" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition">Approve</button>
                    <button name="action" value="reject" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition">Reject</button>
                </form>
            <?php else: ?>
                -
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="12" class="text-center p-5 text-gray-500 italic">No requests found.</td>
    </tr>
<?php endif; ?>
</tbody>

    </table>
</div>

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

<script>
if(window.innerWidth <= 768){
    const mark = document.getElementById('footerMark');
    mark.style.left = '50%';
    mark.style.right = 'auto';
    mark.style.transform = 'translateX(-71%)';
}
</script>

</body>
</html>
