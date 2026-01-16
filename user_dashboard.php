<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user's help requests
$requests_stmt = $conn->prepare("
    SELECT hr.*, 
           g.fullname AS gov_name, g.email AS gov_email,
           a.volunteer_id, 
           v.fullname AS volunteer_name, v.email AS volunteer_email
    FROM help_requests hr
    LEFT JOIN gov_employees g ON hr.approved_by = g.id
    LEFT JOIN assignments a ON a.request_id = hr.id
    LEFT JOIN volunteers v ON v.id = a.volunteer_id
    WHERE hr.user_id = ?
    ORDER BY hr.created_at DESC
");

$requests_stmt->bind_param("i", $user_id);
$requests_stmt->execute();
$requests_res = $requests_stmt->get_result();

// Fetch donations for this user's requests
$donations_stmt = $conn->prepare("
    SELECT d.amount, d.created_at, dn.fullname AS donor_name, dn.email AS donor_email, d.request_id
    FROM donations d
    JOIN donors dn ON d.donor_id = dn.id
    WHERE d.request_id IN (SELECT id FROM help_requests WHERE user_id = ?)
    ORDER BY d.created_at DESC
");
$donations_stmt->bind_param("i", $user_id);
$donations_stmt->execute();
$donations_res = $donations_stmt->get_result();

$donations = [];
while ($d = $donations_res->fetch_assoc()) {
    $donations[$d['request_id']][] = $d;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
<script>
function toggleDonations(id){
    const row = document.getElementById('donations-' + id);
    row.style.display = (row.style.display === 'none') ? 'table-row' : 'none';
}
</script>
</head>
<body class="bg-gradient-to-br from-gray-50 to-gray-200 min-h-screen p-6">
    
<!-- Header -->
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-indigo-700">User Dashboard 👤</h1>
    <a href="logout.php" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 transition">Logout</a>
</div>

<!-- Submit Help Request -->
<section class="mb-8 bg-white rounded-2xl shadow-md border p-6">
    <h2 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">🆘 Submit a New Help Request</h2>
    <form method="POST" action="submit_request.php" class="space-y-4">
    <input type="text" name="title" placeholder="Request Title" required class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400">
    <textarea name="description" placeholder="Describe your need..." required class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400"></textarea>
    <input type="number" name="people_required" placeholder="Number of Quantity Required" min="1" required class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400">
<input 
    type="date" 
    name="required_date" 
    required 
    class=" w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 text-gray-600  "
/>    <input type="text" name="location" placeholder="Enter Location" required class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400">
    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg shadow transition">Submit</button>
</form>

</section>

<!-- Your Requests -->
<section class="bg-white shadow-md border rounded-2xl p-6">
    <h2 class="text-xl font-semibold mb-4 text-gray-800 border-b pb-2">📋 Your Requests</h2>

    <div class="overflow-x-auto">
        <table class="min-w-full border-collapse text-sm">
            <thead>
                <tr class="bg-indigo-100 text-gray-800">
                    <th class="p-3 border">Title</th>
                    <th class="p-3 border">Description</th>
                    <th class="p-3 border">Quantity Required</th>
                    <th class="p-3 border">Required Date</th>
                    <th class="p-3 border">Location</th>
                    <th class="p-3 border">Status</th>
                    <th class="p-3 border">Approved By</th>
                    <th class="p-3 border">Donations</th>
                    <th class="p-3 border">View</th>
                    <th class="p-3 border text-center">Edit</th>
                </tr>
            </thead>
            <tbody>
            <?php if($requests_res->num_rows > 0): ?>
                <?php while($row = $requests_res->fetch_assoc()): ?>
                <tr class="hover:bg-indigo-50 transition">
                    <td class="p-3 border font-medium"><?= htmlspecialchars($row['title']) ?></td>
                    <td class="p-3 border"><?= htmlspecialchars($row['description']) ?></td>
                    <td class="p-3 border text-center text-gray-700"><?= htmlspecialchars($row['people_required'] ?? '-') ?></td>
                    <td class="p-3 border text-gray-600"><?= htmlspecialchars($row['required_date']) ?></td>
                    <td class="p-3 border text-gray-600"><?= htmlspecialchars($row['location']) ?></td>
                    <!-- STATUS column with hover tooltip (Option 2) -->
                    <?php
                        // Build tooltip only if volunteer_name exists (i.e., request has assignment)
                        $tooltip = '';
                        if(!empty($row['volunteer_name'])){
                            $parts = [];
                            $parts[] = 'Volunteer: '. $row['volunteer_name'];
                            if(!empty($row['volunteer_phone'])) $parts[] = 'Phone: '. $row['volunteer_phone'];
                            if(!empty($row['assigned_at'])) $parts[] = 'Assigned: '. date("d M Y, H:i", strtotime($row['assigned_at']));
                            $tooltip = implode(' · ', $parts);
                        }
                        // choose color by status
                        $statusClass = ($row['status']=='Pending') ? 'text-yellow-600' : (($row['status']=='Approved') ? 'text-green-600' : (($row['status']=='Rejected') ? 'text-red-600' : 'text-gray-700'));
                    ?>
                    <td class="p-3 border text-center">
    <?php if($row['status'] === 'Approved' && !empty($row['volunteer_name'])): ?>
        <span 
            title="Volunteer: <?= htmlspecialchars($row['volunteer_name']) ?>&#10;
Email: <?= htmlspecialchars($row['volunteer_email']) ?>"
            class="text-green-600 font-semibold cursor-pointer"
        >
            Approved
        </span>
    <?php elseif($row['status'] === 'Approved'): ?>
        <span class="text-green-600 font-semibold">Approved</span>
    <?php elseif($row['status'] === 'Rejected'): ?>
        <span 
            title="<?= htmlspecialchars($row['feedback'] ?? '') ?>" 
            class="text-red-600 font-semibold cursor-pointer"
        >
            Rejected
        </span>
    <?php else: ?>
        <span class="text-yellow-600 font-semibold">
            <?= htmlspecialchars($row['status']) ?>
        </span>
    <?php endif; ?>
</td>

                    <td class="p-3 border text-sm text-gray-700">
                        <?= htmlspecialchars($row['gov_name'] ?? '-') ?><br>
                        <span class="text-gray-500 text-xs"><?= htmlspecialchars($row['gov_email'] ?? '-') ?></span>
                    </td>
                    <td class="p-3 border text-center">
                        <?= isset($donations[$row['id']]) ? count($donations[$row['id']]) . ' donation(s)' : 'No donations' ?>
                    </td>
                    <td class="p-3 border text-center">
                        <?php if(isset($donations[$row['id']])): ?>
                            <button onclick="toggleDonations(<?= $row['id'] ?>)" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-xs">View</button>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td class="p-3 border text-center">
                        <a href="edit_request.php?id=<?= $row['id'] ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md text-xs">Edit</a>
                    </td>
                </tr>

                <?php if(isset($donations[$row['id']])): ?>
                <tr id="donations-<?= $row['id'] ?>" style="display:none" class="bg-gray-50">
                    <td colspan="9" class="p-3 border">
                        <?php foreach($donations[$row['id']] as $d): ?>
                            <div class="border-b py-1 text-gray-700">
                                💰 <b><?= htmlspecialchars($d['donor_name']) ?></b> (<?= htmlspecialchars($d['donor_email']) ?>)
                                donated <span class="font-semibold text-green-700">₹<?= htmlspecialchars($d['amount']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="text-center p-6 text-gray-500 italic">No requests submitted yet.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<!-- Subtle watermark -->
<div style="
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
