<?php
// Admin dashboard template - requires admin access
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: /");
    exit;
}

// Regenerate CSRF token
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
  <title>Admin Dashboard - EmployEase</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900">

  <!-- Header -->
  <header class="bg-white border-b sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold">E</div>
        <h1 class="text-xl font-bold tracking-tight">EmployEase Admin Portal</h1>
      </div>
      <div class="flex items-center gap-4">
        <span class="text-sm text-gray-500"><?php echo htmlspecialchars($_SESSION['first_name'] ?? 'Admin'); ?> (System Admin)</span>
        <a href="logout" class="text-sm px-4 py-2 border rounded-lg hover:bg-gray-100 transition">Logout</a>
      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto p-6 space-y-6">

    <!-- Success/Error Messages -->
    <?php if (isset($_GET['msg'])): ?>
      <div class="<?php echo strpos($_GET['msg'], 'success') !== false ? 'bg-green-100 border-green-400 text-green-700' : 'bg-yellow-100 border-yellow-400 text-yellow-700'; ?> px-4 py-3 border rounded-lg mb-4" role="alert">
        <p class="font-medium"><?php echo htmlspecialchars($_GET['msg']); ?></p>
      </div>
    <?php endif; ?>

    <!-- 1. DATA ANALYTICS (Overview Cards) -->
    <section class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-white rounded-xl shadow-sm border p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Total Applicants</p>
        <h2 class="text-3xl font-bold mt-1"><?php echo $stats['total_applicants'] ?? 0; ?></h2>
      </div>
      <div class="bg-white rounded-xl shadow-sm border p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Avg. AI Match Score</p>
        <h2 class="text-3xl font-bold mt-1"><?php echo $stats['avg_ai_score'] ?? 0; ?>%</h2>
        <div class="w-full bg-gray-200 h-1.5 rounded-full mt-3">
          <div class="bg-blue-600 h-1.5 rounded-full" style="width: <?php echo min($stats['avg_ai_score'] ?? 0, 100); ?>%"></div>
        </div>
      </div>
      <div class="bg-white rounded-xl shadow-sm border p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Flagged for Review</p>
        <h2 class="text-3xl font-bold mt-1 text-orange-500"><?php echo $stats['flagged_count'] ?? 0; ?></h2>
        <p class="text-xs text-gray-400 mt-2">Requires manual audit</p>
      </div>
      <div class="bg-white rounded-xl shadow-sm border p-5">
        <p class="text-xs font-medium text-gray-500 uppercase tracking-wider">Integrity Rate</p>
        <h2 class="text-3xl font-bold mt-1"><?php echo $stats['integrity_rate'] ?? 'N/A'; ?></h2>
        <p class="text-xs text-blue-600 mt-2">Verified by AI Proctor</p>
      </div>
    </section>

    <!-- 2. LIVE APPLICATIONS MONITORING -->
    <section class="bg-white rounded-xl shadow-sm border overflow-hidden">
      <div class="px-6 py-4 border-b flex justify-between items-center">
        <h2 class="text-lg font-bold">Live Application Monitoring</h2>
        <span class="px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded animate-pulse">LIVE UPDATES</span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead class="bg-gray-50">
            <tr class="text-xs font-semibold text-gray-500 uppercase border-b">
              <th class="px-6 py-4 font-medium">Candidate</th>
              <th class="px-6 py-4 font-medium">Position</th>
              <th class="px-6 py-4 font-medium">AI Score</th>
              <th class="px-6 py-4 font-medium">Status</th>
              <th class="px-6 py-4 font-medium">Applied</th>
              <th class="px-6 py-4 font-medium">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y text-sm">
            <?php if (!empty($recentApplications)): ?>
              <?php foreach ($recentApplications as $app): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></td>
                  <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($app['position']); ?></td>
                  <td class="px-6 py-4">
                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md font-bold"><?php echo $app['ai_score'] ?? 'N/A'; ?>%</span>
                  </td>
                  <td class="px-6 py-4">
                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-md text-xs font-medium"><?php echo htmlspecialchars($app['status']); ?></span>
                  </td>
                  <td class="px-6 py-4 text-gray-500 text-xs"><?php echo date('M d, Y', strtotime($app['applied_at'])); ?></td>
                  <td class="px-6 py-4 text-right">
                    <a href="view-application-profile?id=<?php echo $app['id']; ?>" class="text-blue-600 hover:underline font-medium">View</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No recent applications</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- 3. GENERATED REPORTS & VIOLATIONS -->
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-lg font-bold mb-4">Recent Proctoring Violations</h2>
        <div class="space-y-4">
          <?php if (!empty($recentViolations)): ?>
            <?php foreach ($recentViolations as $violation): ?>
              <div class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg">
                <div class="bg-red-100 p-2 rounded text-red-600 font-bold text-xs">ALERT</div>
                <div class="flex-1">
                  <p class="text-sm font-semibold"><?php echo htmlspecialchars($violation['action']); ?></p>
                  <p class="text-xs text-gray-500">Candidate: <?php echo htmlspecialchars($violation['first_name'] . ' ' . $violation['last_name']); ?></p>
                  <p class="text-xs text-gray-400 mt-1"><?php echo htmlspecialchars($violation['description']); ?></p>
                  <p class="text-xs text-gray-400"><?php echo date('M d, Y H:i', strtotime($violation['created_at'])); ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-gray-500 text-sm">No recent violations</p>
          <?php endif; ?>
        </div>
        <form action="generate-audit-report" method="POST" class="mt-6">
          <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
          <button type="submit" class="w-full py-2 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg hover:bg-gray-200 transition">
            Generate Full System Audit Report (.PDF)
          </button>
        </form>
      </div>

      <div class="bg-white rounded-xl shadow-sm border p-6">
        <h2 class="text-lg font-bold mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 gap-3">
          <a href="admin-manage-jobs" class="text-left px-4 py-3 border rounded-lg hover:bg-blue-50 hover:border-blue-200 transition group">
            <p class="font-semibold text-sm group-hover:text-blue-700">Manage Job Postings</p>
            <p class="text-xs text-gray-500">View all recruiter listings</p>
          </a>
          <a href="admin-manage-exams" class="text-left px-4 py-3 border rounded-lg hover:bg-blue-50 hover:border-blue-200 transition group">
            <p class="font-semibold text-sm group-hover:text-blue-700">Manage Exams</p>
            <p class="text-xs text-gray-500">Configure exam question banks</p>
          </a>
          <a href="admin-manage-users" class="text-left px-4 py-3 border rounded-lg hover:bg-blue-50 hover:border-blue-200 transition group">
            <p class="font-semibold text-sm group-hover:text-blue-700">Manage Users</p>
            <p class="text-xs text-gray-500">Control account roles and access</p>
          </a>
          <a href="admin-skill-tags" class="text-left px-4 py-3 border rounded-lg hover:bg-blue-50 hover:border-blue-200 transition group">
            <p class="font-semibold text-sm group-hover:text-blue-700">Manage Skill Tags</p>
            <p class="text-xs text-gray-500">Maintain candidate/recruiter skill taxonomy</p>
          </a>
          <a href="admin-audit-feed" class="text-left px-4 py-3 border rounded-lg hover:bg-blue-50 hover:border-blue-200 transition group">
            <p class="font-semibold text-sm group-hover:text-blue-700">Global Audit Feed</p>
            <p class="text-xs text-gray-500">Inspect system activity and events</p>
          </a>
          <a href="admin-settings" class="text-left px-4 py-3 border rounded-lg hover:bg-blue-50 hover:border-blue-200 transition group">
            <p class="font-semibold text-sm group-hover:text-blue-700">System Settings</p>
            <p class="text-xs text-gray-500">Configure system parameters</p>
          </a>
        </div>
      </div>
    </section>

  </main>
</body>
</html>
