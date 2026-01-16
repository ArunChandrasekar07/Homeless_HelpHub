<?php
session_start();
include 'db.php';

// Check login and role
if(!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'donor'){
    header("Location: index.html");
    exit;
}

$donor_id = $_SESSION['user_id'];

// Fetch approved requests
$requests_stmt = $conn->prepare("
    SELECT hr.*, 
           u.fullname AS user_name, 
           u.email AS user_email, 
           u.phone AS user_phone,
           g.fullname AS gov_name,
           g.email AS gov_email
    FROM help_requests hr
    JOIN users u ON hr.user_id = u.id
    LEFT JOIN gov_employees g ON hr.approved_by = g.id
    WHERE hr.status = 'Approved'
    ORDER BY hr.created_at DESC
");
$requests_stmt->execute();
$requests_res = $requests_stmt->get_result();

// Fetch donor’s donation history
$history_stmt = $conn->prepare("
    SELECT hr.title, hr.location, hr.status, d.amount, 
           u.fullname AS user_name, u.email AS user_email, u.phone AS user_phone,
           g.fullname AS gov_name
    FROM donations d
    JOIN help_requests hr ON d.request_id = hr.id
    JOIN users u ON hr.user_id = u.id
    LEFT JOIN gov_employees g ON hr.approved_by = g.id
    WHERE d.donor_id = ?
    ORDER BY d.created_at DESC
");
$history_stmt->bind_param("i", $donor_id);
$history_stmt->execute();
$history_res = $history_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Donor Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
  <!-- Header -->
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-indigo-700">Donor Dashboard 💖</h1>
    <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">Logout</a>
</div>
<!-- Available Requests -->
<div class="p-6 bg-white shadow-lg rounded-2xl mb-6 border border-gray-200">
    <h2 class="font-semibold text-2xl mb-4 text-indigo-600">Available Help Requests</h2>
    <table class="w-full border-collapse rounded overflow-hidden text-sm">
        <thead>
            <tr class="bg-indigo-100 text-indigo-800">
                <th class="p-3 border">Requester</th>
                <th class="p-3 border">Title</th>
                 <th class="p-3 border">Description</th>
                <th class="p-3 border">Quantity Required</th>
                <th class="p-3 border">Required Date</th>
                <th class="p-3 border">Location</th>
                <th class="p-3 border">Approved By</th>
                <th class="p-3 border">Action</th>
            </tr>
        </thead>
        <tbody>
        <?php if($requests_res->num_rows > 0): ?>
            <?php while($row = $requests_res->fetch_assoc()): ?>
            <tr class="hover:bg-gray-50 transition">
                <td class="p-3 border">
                    <b><?= htmlspecialchars($row['user_name']) ?></b><br>
                    <span class="text-gray-600 text-sm"><?= htmlspecialchars($row['user_email']) ?></span><br>
                    <span class="text-gray-500 text-xs">📞 <?= htmlspecialchars($row['user_phone'] ?? 'N/A') ?></span>
                </td>
                <td class="p-3 border"><?= htmlspecialchars($row['title']) ?></td>
                <td class="p-3 border"><?= htmlspecialchars($row['description']) ?></td>
        <td class="p-3 border text-center"><?= htmlspecialchars($row['people_required'] ?? '-') ?></td>
        <td class="p-3 border text-center"><?= htmlspecialchars($row['required_date'] ?? '-') ?></td>
                <td class="p-3 border"><?= htmlspecialchars($row['location']) ?></td>
                <td class="p-3 border text-sm">
                    <?= htmlspecialchars($row['gov_name'] ?? '-') ?><br>
                    <span class="text-gray-600"><?= htmlspecialchars($row['gov_email'] ?? '-') ?></span>
                </td>
                <td class="p-3 border">
                    <form method="POST" action="donate_request.php" class="flex gap-2 justify-center items-center">
                        <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
                        <input type="number" name="amount" placeholder="₹" required class="border px-2 py-1 rounded w-24 focus:outline-none focus:ring focus:ring-indigo-300">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-1 rounded-lg hover:bg-indigo-700 transition">
                            Donate
                        </button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center p-5 text-gray-500 italic">No approved requests available right now.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Donation History -->
<div class="p-6 bg-white shadow-lg rounded-2xl border border-gray-200">
    <h2 class="font-semibold text-2xl mb-4 text-green-600">Your Donation History</h2>
    <table class="w-full border-collapse rounded text-sm">
        <thead>
            <tr class="bg-green-100 text-green-800">
                <th class="p-3 border">Requester</th>
                <th class="p-3 border">Title</th>
                <th class="p-3 border">Location</th>
                <th class="p-3 border">Status</th>
                <th class="p-3 border">Amount</th>
                <th class="p-3 border">Approved By</th>
            </tr>
        </thead>
        <tbody>
        <?php if($history_res->num_rows > 0): ?>
            <?php while($row = $history_res->fetch_assoc()): ?>
            <tr class="hover:bg-gray-50 transition">
                <td class="p-3 border">
                    <b><?= htmlspecialchars($row['user_name']) ?></b><br>
                    <span class="text-gray-600 text-sm"><?= htmlspecialchars($row['user_email']) ?></span><br>
                    <span class="text-gray-500 text-xs">📞 <?= htmlspecialchars($row['user_phone'] ?? 'N/A') ?></span>
                </td>
                <td class="p-3 border"><?= htmlspecialchars($row['title']) ?></td>
                <td class="p-3 border"><?= htmlspecialchars($row['location']) ?></td>
                <td class="p-3 border font-semibold <?= $row['status']=='Pending'?'text-yellow-600':'text-green-700' ?>">
                    <?= htmlspecialchars($row['status']) ?>
                </td>
                <td class="p-3 border text-indigo-700 font-bold">₹<?= htmlspecialchars($row['amount']) ?></td>
                <td class="p-3 border"><?= htmlspecialchars($row['gov_name'] ?? '-') ?></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center p-5 text-gray-500 italic">You haven’t made any donations yet.</td>
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
    mark.style.transform = 'translateX(-50%)';
}
</script>

</body>
</html>
