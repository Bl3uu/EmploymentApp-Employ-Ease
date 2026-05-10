<?php
// Application profile view for admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: /");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Application Profile - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900">

  <!-- Header -->
  <header class="bg-white border-b sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
      <div class="flex items-center gap-4">
        <a href="admin-dashboard" class="text-blue-600 hover:underline">← Back to Dashboard</a>
        <h1 class="text-xl font-bold">Application Profile</h1>
      </div>
    </div>
  </header>

  <main class="max-w-4xl mx-auto p-6 space-y-6">

    <!-- Candidate Information -->
    <section class="bg-white rounded-xl shadow-sm border p-6">
      <h2 class="text-lg font-bold mb-4">Candidate Information</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <p class="text-xs font-medium text-gray-500 uppercase">Full Name</p>
          <p class="text-lg font-semibold mt-1"><?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?></p>
        </div>
        <div>
          <p class="text-xs font-medium text-gray-500 uppercase">Email</p>
          <p class="text-lg font-semibold mt-1"><?php echo htmlspecialchars($application['email']); ?></p>
        </div>
        <div>
          <p class="text-xs font-medium text-gray-500 uppercase">Position</p>
          <p class="text-lg font-semibold mt-1"><?php echo htmlspecialchars($application['title']); ?></p>
        </div>
        <div>
          <p class="text-xs font-medium text-gray-500 uppercase">Company</p>
          <p class="text-lg font-semibold mt-1"><?php echo htmlspecialchars($application['company']); ?></p>
        </div>
        <div>
          <p class="text-xs font-medium text-gray-500 uppercase">Application Status</p>
          <p class="mt-1">
            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
              <?php echo htmlspecialchars($application['status']); ?>
            </span>
          </p>
        </div>
        <div>
          <p class="text-xs font-medium text-gray-500 uppercase">Applied On</p>
          <p class="text-lg font-semibold mt-1"><?php echo date('M d, Y H:i', strtotime($application['applied_at'])); ?></p>
        </div>
      </div>
    </section>

    <!-- AI Analysis -->
    <section class="bg-white rounded-xl shadow-sm border p-6">
      <h2 class="text-lg font-bold mb-4">AI Analysis</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <p class="text-xs font-medium text-gray-500 uppercase">AI Match Score</p>
          <div class="mt-3">
            <div class="text-3xl font-bold text-blue-600"><?php echo $application['ai_score'] ?? 0; ?>%</div>
            <div class="w-full bg-gray-200 h-2 rounded-full mt-2">
              <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo min($application['ai_score'] ?? 0, 100); ?>%"></div>
            </div>
          </div>
        </div>
        <div>
          <p class="text-xs font-medium text-gray-500 uppercase">Summary</p>
          <p class="text-sm text-gray-700 mt-2"><?php echo htmlspecialchars($application['ai_summary'] ?? 'No summary available'); ?></p>
        </div>
      </div>
    </section>

    <!-- Resume -->
    <?php if (!empty($application['resume_path'])): ?>
    <section class="bg-white rounded-xl shadow-sm border p-6">
      <h2 class="text-lg font-bold mb-4">Resume</h2>
      <a href="view-resume?path=<?php echo urlencode($application['resume_path']); ?>" target="_blank" 
         class="inline-block px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
        Download Resume
      </a>
    </section>
    <?php endif; ?>

    <!-- Audit Log / Violations -->
    <section class="bg-white rounded-xl shadow-sm border p-6">
      <h2 class="text-lg font-bold mb-4">Activity & Violations (<?php echo count($violations); ?>)</h2>
      <div class="space-y-3">
        <?php if (!empty($violations)): ?>
          <?php foreach ($violations as $violation): ?>
            <div class="flex items-start gap-4 p-3 bg-gray-50 rounded-lg">
              <div class="<?php 
                $type = strtolower($violation['action']);
                if (strpos($type, 'violation') !== false || strpos($type, 'tab') !== false || strpos($type, 'blur') !== false) {
                  echo 'bg-red-100 text-red-600';
                } elseif (strpos($type, 'status') !== false) {
                  echo 'bg-blue-100 text-blue-600';
                } else {
                  echo 'bg-gray-100 text-gray-600';
                }
              ?> p-2 rounded font-bold text-xs flex-shrink-0 w-12 h-12 flex items-center justify-center text-center">
                <?php echo substr($violation['action'], 0, 3); ?>
              </div>
              <div class="flex-1">
                <p class="text-sm font-semibold"><?php echo htmlspecialchars($violation['action']); ?></p>
                <p class="text-xs text-gray-600 mt-1"><?php echo htmlspecialchars($violation['description']); ?></p>
                <p class="text-xs text-gray-400 mt-1"><?php echo date('M d, Y H:i:s', strtotime($violation['created_at'])); ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="text-gray-500 text-sm">No violations or activity recorded</p>
        <?php endif; ?>
      </div>
    </section>

  </main>
</body>
</html>
