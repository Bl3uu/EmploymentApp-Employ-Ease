<?php
// Manage jobs view for admin
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
  <title>Manage Jobs - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900">

  <!-- Header -->
  <header class="bg-white border-b sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
      <div class="flex items-center gap-4">
        <a href="admin-dashboard" class="text-blue-600 hover:underline">← Back to Dashboard</a>
        <h1 class="text-xl font-bold">Manage Job Postings</h1>
      </div>
    </div>
  </header>

  <main class="max-w-6xl mx-auto p-6">

    <!-- Jobs Table -->
    <section class="bg-white rounded-xl shadow-sm border overflow-hidden">
      <div class="px-6 py-4 border-b">
        <h2 class="text-lg font-bold">All Job Postings</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead class="bg-gray-50">
            <tr class="text-xs font-semibold text-gray-500 uppercase border-b">
              <th class="px-6 py-4 font-medium">Job Title</th>
              <th class="px-6 py-4 font-medium">Company</th>
              <th class="px-6 py-4 font-medium">Recruiter</th>
              <th class="px-6 py-4 font-medium">Location</th>
              <th class="px-6 py-4 font-medium">Type</th>
              <th class="px-6 py-4 font-medium">Status</th>
              <th class="px-6 py-4 font-medium">Posted</th>
              <th class="px-6 py-4 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y text-sm">
            <?php if (!empty($jobs)): ?>
              <?php foreach ($jobs as $job): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($job['title']); ?></td>
                  <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($job['company']); ?></td>
                  <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($job['first_name'] . ' ' . $job['last_name']); ?></td>
                  <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($job['location']); ?></td>
                  <td class="px-6 py-4">
                    <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded-md text-xs font-medium"><?php echo htmlspecialchars($job['type']); ?></span>
                  </td>
                  <td class="px-6 py-4">
                    <span class="px-2 py-1 <?php echo $job['status'] === 'Active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?> rounded-md text-xs font-medium">
                      <?php echo htmlspecialchars($job['status']); ?>
                    </span>
                  </td>
                  <td class="px-6 py-4 text-gray-500 text-xs"><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                  <td class="px-6 py-4 text-right space-x-2">
                    <a href="edit-job?id=<?php echo $job['id']; ?>" class="text-blue-600 hover:underline font-medium text-sm">Edit</a>
                    <button onclick="toggleArchive(<?php echo $job['id']; ?>)" class="text-orange-600 hover:underline font-medium text-sm">
                      <?php echo $job['is_archived'] ? 'Restore' : 'Archive'; ?>
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="px-6 py-4 text-center text-gray-500">No jobs found</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>

  <script>
    function toggleArchive(jobId) {
      if (confirm('Are you sure you want to toggle the archive status of this job?')) {
        // Will implement archive functionality
        alert('Archive feature coming soon');
      }
    }
  </script>
</body>
</html>
