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
  <title>Admin User Management - EmployEase</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900">
  <header class="bg-white border-b sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white font-bold">U</div>
        <div>
          <h1 class="text-xl font-bold">User Management</h1>
          <p class="text-sm text-gray-500">Manage registered users and roles across the platform.</p>
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
          <h2 class="text-lg font-bold">Platform Users</h2>
          <p class="text-sm text-gray-500">Review accounts, update roles, and keep control of access.</p>
        </div>
        <a href="admin-audit-feed" class="inline-flex items-center gap-2 px-4 py-2 border rounded-lg text-sm font-semibold text-blue-600 hover:bg-blue-50 transition">
          View Global Audit Feed
        </a>
      </div>

      <div class="overflow-x-auto">
        <table class="min-w-full text-left text-sm">
          <thead class="bg-gray-50 text-gray-600 uppercase text-xs tracking-wider">
            <tr>
              <th class="px-5 py-4">Name</th>
              <th class="px-5 py-4">Email</th>
              <th class="px-5 py-4">Role</th>
              <th class="px-5 py-4">Created</th>
              <th class="px-5 py-4">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y">
            <?php if (!empty($users)): ?>
              <?php foreach ($users as $user): ?>
                <tr class="hover:bg-gray-50">
                  <td class="px-5 py-4 font-medium"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                  <td class="px-5 py-4 text-gray-600"><?php echo htmlspecialchars($user['email']); ?></td>
                  <td class="px-5 py-4 text-gray-700"><?php echo htmlspecialchars($user['role_name'] ?: 'None'); ?></td>
                  <td class="px-5 py-4 text-gray-500 text-xs"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                  <td class="px-5 py-4">
                    <form action="admin-change-role" method="POST" class="flex flex-wrap items-center gap-2">
                      <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                      <input type="hidden" name="user_id" value="<?php echo (int)$user['id']; ?>">
                      <select name="role_id" class="rounded-lg border-gray-300 text-sm px-3 py-2">
                        <?php foreach ($roles as $role): ?>
                          <option value="<?php echo (int)$role['id']; ?>" <?php echo $role['id'] == $user['role_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($role['role_name']); ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700 transition">Save</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="px-5 py-8 text-center text-gray-500">No registered users found.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</body>
</html>
