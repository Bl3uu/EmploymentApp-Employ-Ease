<?php include __DIR__ . '/../partials/header.php'; ?>

<main class="max-w-4xl mx-auto p-6">
    <div class="flex items-center justify-between mb-6">
        <a href="portal" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 bg-gray-100 px-4 py-2 rounded-xl hover:bg-gray-200 transition-colors">
            &larr; Back to dashboard
        </a>
    </div>
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Application Progress</h1>

        <?php if (empty($myApplications)): ?>
            <p class="text-sm text-gray-500">No applications yet.</p>
        <?php else: ?>
            <?php foreach ($myApplications as $app): ?>
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4"><?php echo htmlspecialchars($app['title']); ?></h2>
                    <p class="text-sm text-gray-500 mb-4">Current Status: <strong><?php echo htmlspecialchars($app['status']); ?></strong></p>

                    <?php
                        $progressSteps = ['Applied', 'Screened', 'Exam Assigned', 'Exam Completed', 'Interviewing', 'Offered'];
                        $currentProgressIndex = array_search($app['status'], $progressSteps);
                    ?>

                    <div class="space-y-4">
                        <?php foreach ($progressSteps as $index => $step): ?>
                            <?php $active = $index <= $currentProgressIndex; ?>
                            <div class="flex items-center gap-4">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full text-sm font-bold <?php echo $active ? 'bg-black text-white' : 'bg-gray-100 text-gray-500'; ?>">
                                    <?php echo $index + 1; ?>
                                </span>
                                <div>
                                    <p class="text-base font-semibold <?php echo $active ? 'text-gray-900' : 'text-gray-400'; ?>"><?php echo htmlspecialchars($step); ?></p>
                                    <?php if ($index === $currentProgressIndex): ?>
                                        <p class="text-xs text-gray-500 mt-1">Current stage</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>