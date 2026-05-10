<?php
// Admin settings view
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
  <title>System Settings - Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans text-gray-900">

  <!-- Header -->
  <header class="bg-white border-b sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
      <div class="flex items-center gap-4">
        <a href="admin-dashboard" class="text-blue-600 hover:underline">← Back to Dashboard</a>
        <h1 class="text-xl font-bold">System Settings</h1>
      </div>
    </div>
  </header>

  <main class="max-w-4xl mx-auto p-6 space-y-6">

    <!-- Session Settings -->
    <section class="bg-white rounded-xl shadow-sm border p-6">
      <h2 class="text-lg font-bold mb-4">Session Management</h2>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Session Timeout (seconds)</label>
          <input type="number" value="900" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="900">
          <p class="text-xs text-gray-500 mt-1">Default: 900 seconds (15 minutes)</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Session Cookie Lifetime</label>
          <input type="number" value="0" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="0">
          <p class="text-xs text-gray-500 mt-1">0 = Session cookie (closes when browser closes)</p>
        </div>
      </div>
    </section>

    <!-- Security Settings -->
    <section class="bg-white rounded-xl shadow-sm border p-6">
      <h2 class="text-lg font-bold mb-4">Security Settings</h2>
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium">Enable Session Binding</p>
            <p class="text-sm text-gray-500">Bind sessions to IP and User-Agent</p>
          </div>
          <input type="checkbox" checked class="w-4 h-4 text-blue-600">
        </div>
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium">Require HTTPS</p>
            <p class="text-sm text-gray-500">Force HTTPS for all connections</p>
          </div>
          <input type="checkbox" class="w-4 h-4 text-blue-600">
        </div>
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium">Enable CSRF Protection</p>
            <p class="text-sm text-gray-500">Protect against Cross-Site Request Forgery</p>
          </div>
          <input type="checkbox" checked class="w-4 h-4 text-blue-600">
        </div>
      </div>
    </section>

    <!-- Proctoring Settings -->
    <section class="bg-white rounded-xl shadow-sm border p-6">
      <h2 class="text-lg font-bold mb-4">AI Proctoring Settings</h2>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Tab Switch Penalty</label>
          <input type="number" value="1" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <p class="text-xs text-gray-500 mt-1">Violation count per tab switch</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Window Blur Penalty</label>
          <input type="number" value="1" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <p class="text-xs text-gray-500 mt-1">Violation count per window blur</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Violation Threshold</label>
          <input type="number" value="5" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <p class="text-xs text-gray-500 mt-1">Maximum violations allowed before exam is flagged</p>
        </div>
      </div>
    </section>

    <!-- Resume Upload Settings -->
    <section class="bg-white rounded-xl shadow-sm border p-6">
      <h2 class="text-lg font-bold mb-4">File Upload Settings</h2>
      <div class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Max Resume Size (MB)</label>
          <input type="number" value="5" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Allowed File Types</label>
          <input type="text" value=".pdf, .doc, .docx" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
      </div>
    </section>

    <!-- NLP Settings -->
    <section class="bg-white rounded-xl shadow-sm border p-6">
      <h2 class="text-lg font-bold mb-4">NLP Analysis Settings</h2>
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <div>
            <p class="font-medium">Enable AI Resume Scoring</p>
            <p class="text-sm text-gray-500">Automatically score resumes against job description</p>
          </div>
          <input type="checkbox" checked class="w-4 h-4 text-blue-600">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Minimum Match Score (%)</label>
          <input type="number" value="60" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
          <p class="text-xs text-gray-500 mt-1">Applications below this score are flagged for review</p>
        </div>
      </div>
    </section>

    <!-- Save Settings -->
    <div class="flex gap-3">
      <button class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
        Save Settings
      </button>
      <a href="admin-dashboard" class="px-6 py-2 border rounded-lg hover:bg-gray-100 transition font-medium">
        Cancel
      </a>
    </div>

  </main>
</body>
</html>
