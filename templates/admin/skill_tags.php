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
  <title>Admin Skill Tags - EmployEase</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900">
  <header class="bg-white border-b sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold">S</div>
        <div>
          <h1 class="text-xl font-bold">Skill Tag Management</h1>
          <p class="text-sm text-gray-500">Manage the skills candidates can tag when applying.</p>
        </div>
      </div>
      <div class="flex items-center gap-3">
        <a href="admin-dashboard" class="text-sm px-4 py-2 border rounded-lg hover:bg-gray-100 transition">Back to Dashboard</a>
        <a href="logout" class="text-sm px-4 py-2 border rounded-lg hover:bg-gray-100 transition">Logout</a>
      </div>
    </div>
  </header>

  <main class="max-w-7xl mx-auto p-6 space-y-6">
    <?php if (isset($_GET['msg'])): ?>
      <div class="bg-green-100 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
        <?php echo htmlspecialchars($_GET['msg']); ?>
      </div>
    <?php endif; ?>

    <section class="bg-white rounded-xl shadow-sm border p-6">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
          <h2 class="text-lg font-bold">Skill Tags</h2>
          <p class="text-sm text-gray-500">Add, rename, or remove tags used to describe candidate experience.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="space-y-4">
          <form action="admin-skill-action" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="action" value="add">
            <label class="block">
              <span class="text-sm font-medium text-gray-700">New Skill Tag</span>
              <input type="text" name="skill_name" class="mt-2 w-full rounded-lg border border-gray-300 px-4 py-3" placeholder="e.g. React, Python, SQL" required>
            </label>
            <button type="submit" class="px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Add Skill Tag</button>
          </form>
        </div>

        <div class="bg-gray-50 rounded-2xl p-4">
          <p class="text-sm text-gray-500">Tip: keep tags concise and standardized for better filtering.</p>
        </div>
      </div>

      <div class="mt-6 overflow-x-auto">
        <table class="min-w-full text-sm text-left">
          <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
            <tr>
              <th class="px-5 py-4">Skill</th>
              <th class="px-5 py-4">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <?php if (!empty($skills)): ?>
              <?php foreach ($skills as $skill): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-5 py-4 font-medium"><?php echo htmlspecialchars($skill['name']); ?></td>
                  <td class="px-5 py-4">
                    <form action="admin-skill-action" method="POST" class="inline-flex gap-2">
                      <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="skill_id" value="<?php echo (int)$skill['id']; ?>">
                      <button type="submit" class="px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="2" class="px-5 py-8 text-center text-gray-500">No skill tags defined yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
