<?php include __DIR__ . '/../partials/header.php'; ?>

<main class="max-w-3xl mx-auto p-6">
    <div class="mb-6">
        <a href="portal" class="text-sm text-gray-500 hover:underline">← Back to Portal</a>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 text-center <?php echo $hasPassed ? 'bg-green-50' : 'bg-gray-50'; ?>">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4 <?php echo $hasPassed ? 'bg-green-100 text-green-600' : 'bg-gray-200 text-gray-600'; ?>">
                <?php echo $hasPassed ? '✓' : 'ℹ'; ?>
            </div>
            <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($result['title']); ?></h1>
            <p class="text-gray-500">Result for <?php echo htmlspecialchars($_SESSION['first_name']); ?></p>
        </div>

        <div class="p-8 space-y-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-widest">AI Resume Score</p>
                    <p class="text-4xl font-black text-indigo-600 mt-3"><?php echo $result['ai_score'] ?? 0; ?>%</p>
                </div>
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-widest">Exam Score</p>
                    <p class="text-4xl font-black mt-3 <?php echo $hasPassed ? 'text-green-600' : 'text-red-600'; ?>">
                        <?php echo $result['score']; ?>%
                    </p>
                </div>
                <div class="bg-white rounded-3xl p-5 shadow-sm border border-gray-100">
                    <p class="text-xs text-gray-400 uppercase font-bold tracking-widest">Required</p>
                    <p class="text-4xl font-black mt-3 text-gray-900"><?php echo $result['passing_mark']; ?>%</p>
                </div>
            </div>

            <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100">
                <h3 class="font-bold text-gray-900 mb-2">
                    Status: <?php echo $hasPassed ? 'Passed' : 'Completed'; ?>
                </h3>
                <p class="text-sm text-gray-600 leading-relaxed">
                    <?php if ($hasPassed): ?>
                        Congratulations! You have successfully met the technical requirements for this position. Our recruitment team will review your proctoring logs and full application before reaching out for an interview.
                    <?php else: ?>
                        Thank you for completing the assessment. While your score did not meet the immediate passing threshold, your profile remains in our database for future opportunities that may be a better fit.
                    <?php endif; ?>
                </p>
            </div>

            <div class="flex gap-3">
                <a href="portal" class="inline-flex justify-center items-center px-5 py-3 rounded-xl bg-black text-white text-sm font-semibold hover:opacity-90 transition">
                    Return to Portal
                </a>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>