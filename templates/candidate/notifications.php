<?php include __DIR__ . '/../partials/header.php'; ?>

<main class="max-w-4xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
        <a href="portal" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 bg-gray-100 px-4 py-2 rounded-xl hover:bg-gray-200 transition-colors">
            &larr; Back to dashboard
        </a>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Notifications</h1>
        <div class="space-y-4">
            <?php if (empty($notifications)): ?>
                <p class="text-sm text-gray-500">No notifications yet. Important updates will appear here as your application moves forward.</p>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <p class="text-xs uppercase tracking-widest text-gray-400">Notification</p>
                        <p class="text-sm font-semibold text-gray-900 mt-1"><?php echo htmlspecialchars($notification['title'] ?? 'Update'); ?></p>
                        <p class="text-sm text-gray-600 mt-2"><?php echo htmlspecialchars($notification['message'] ?? 'No details available.'); ?></p>
                        <p class="text-[10px] text-gray-400 mt-2"><?php echo date('M j, H:i', strtotime($notification['created_at'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>