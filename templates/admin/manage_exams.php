<?php
// Manage exams view for admin
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
  <title>Manage Exams - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900">

  <!-- Header -->
  <header class="bg-white border-b sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
      <div class="flex items-center gap-4">
        <a href="admin-dashboard" class="text-blue-600 hover:underline">← Back to Dashboard</a>
        <h1 class="text-xl font-bold">Manage Exams</h1>
      </div>
    </div>
  </header>

  <main class="max-w-6xl mx-auto p-6">

    <!-- Exams Table -->
    <section class="bg-white rounded-xl shadow-sm border overflow-hidden">
      <div class="px-6 py-4 border-b">
        <h2 class="text-lg font-bold">All Exams</h2>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left">
          <thead class="bg-gray-50">
            <tr class="text-xs font-semibold text-gray-500 uppercase border-b">
              <th class="px-6 py-4 font-medium">Exam Title</th>
              <th class="px-6 py-4 font-medium">Job Title</th>
              <th class="px-6 py-4 font-medium">Company</th>
              <th class="px-6 py-4 font-medium">Duration (min)</th>
              <th class="px-6 py-4 font-medium">Passing Mark</th>
              <th class="px-6 py-4 font-medium">Questions</th>
              <th class="px-6 py-4 font-medium">Created</th>
              <th class="px-6 py-4 font-medium">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y text-sm">
            <?php if (!empty($exams)): ?>
              <?php foreach ($exams as $exam): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-6 py-4 font-medium"><?php echo htmlspecialchars($exam['title']); ?></td>
                  <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($exam['title']); ?></td>
                  <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($exam['company'] ?? 'N/A'); ?></td>
                  <td class="px-6 py-4"><?php echo $exam['duration_min']; ?> min</td>
                  <td class="px-6 py-4"><?php echo $exam['passing_mark']; ?>%</td>
                  <td class="px-6 py-4">
                    <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-md text-xs font-medium">View Questions</span>
                  </td>
                  <td class="px-6 py-4 text-gray-500 text-xs"><?php echo date('M d, Y', strtotime($exam['id'])); ?></td>
<td class="px-6 py-4 text-right space-x-2">
                    <a href="edit-exam-settings?id=<?php echo $exam['id']; ?>" class="text-blue-600 hover:underline font-medium text-sm">Edit</a>
                    <button onclick="viewQuestions(<?php echo $exam['id']; ?>)" class="text-green-600 hover:underline font-medium text-sm">View</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" class="px-6 py-4 text-center text-gray-500">No exams found</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>

  <script>
    function viewQuestions(examId) {
window.location.href = 'edit-exam-questions?id=' + examId;
    }
  </script>
</body>
</html>
