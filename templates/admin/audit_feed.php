<?php
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: /");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Audit Feed - EmployEase</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900">
  <header class="bg-white border-b sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center text-white font-bold">A</div>
        <div>
          <h1 class="text-xl font-bold">Global Audit Feed</h1>
          <p class="text-sm text-gray-500">Review system activity, user actions, and application events in one place.</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <a href="admin-dashboard" class="text-sm px-4 py-2 border rounded-lg hover:bg-gray-100 transition">Back to Dashboard</a>
        <a href="logout" class="text-sm px-4 py-2 border rounded-lg hover:bg-gray-100 transition">Logout</a>
      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto p-6 space-y-6">
    <section class="bg-white rounded-xl shadow-sm border p-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
          <h2 class="text-lg font-bold">Audit activity</h2>
          <p class="text-sm text-gray-500">Track every recorded system event, including application review, proctoring alerts, and account changes.</p>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
            <tr>
              <th class="px-5 py-4">Time</th>
              <th class="px-5 py-4">Actor</th>
              <th class="px-5 py-4">Action</th>
              <th class="px-5 py-4">Context</th>
              <th class="px-5 py-4">Details</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <?php if (!empty($logs)): ?>
              <?php foreach ($logs as $log): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-5 py-4 text-gray-500 text-xs"><?php echo date('M d, Y H:i', strtotime($log['created_at'])); ?></td>
                  <td class="px-5 py-4 text-gray-700"><?php echo htmlspecialchars($log['first_name'] ? $log['first_name'] . ' ' . $log['last_name'] : 'System'); ?></td>
                  <td class="px-5 py-4 font-medium"><?php echo htmlspecialchars($log['action']); ?></td>
                  <td class="px-5 py-4 text-gray-600"><?php echo htmlspecialchars($log['job_title'] ? 'Job: ' . $log['job_title'] : 'System event'); ?></td>
                  <td class="px-5 py-4 text-gray-500"><?php echo htmlspecialchars($log['description']); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="px-5 py-8 text-center text-gray-500">No audit events available.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
