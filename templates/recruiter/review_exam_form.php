<?php include __DIR__ . '/../partials/header.php'?>

<div class="bg-gray-50 p-8">
    <div class="max-w-4xl mx-auto">
        <header class="mb-8">
            <h1 class="text-2xl font-bold">Exam Review: <?php echo htmlspecialchars(($candidate['first_name'] ?? '') . ' ' . ($candidate['last_name'] ?? '')); ?></h1>
            <p class="text-gray-600"><?php echo htmlspecialchars($candidate['exam_title'] ?? 'N/A'); ?> — Score: <b><?php echo (int)($candidate['score'] ?? $candidate['ai_score'] ?? 0); ?>%</b></p>
        </header>

        <div class="space-y-6">
            <?php foreach ($results as $index => $row): 
                $isCorrect = ($row['selected_option'] === $row['correct_option']);
            ?>
                <div class="bg-white p-6 rounded-2xl shadow-sm border <?php echo $isCorrect ? 'border-green-100' : 'border-red-100'; ?>">
                    <div class="flex justify-between mb-4">
                        <span class="text-sm font-bold text-gray-400 uppercase">Question <?php echo $index + 1; ?></span>
                        <?php if ($isCorrect): ?>
                            <span class="text-green-600 font-bold text-sm">✓ Correct</span>
                        <?php else: ?>
                            <span class="text-red-600 font-bold text-sm">✗ Incorrect</span>
                        <?php endif; ?>
                    </div>
                    
                    <p class="text-lg font-medium text-gray-900 mb-4"><?php echo htmlspecialchars($row['question_text']); ?></p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <?php foreach (['a', 'b', 'c', 'd'] as $opt): 
                            $optionKey = 'option_' . $opt;
                            $isThisSelected = ($row['selected_option'] === strtoupper($opt));
                            $isThisCorrect = ($row['correct_option'] === strtoupper($opt));
                            
                            $bgClass = 'bg-gray-50 border-gray-200';
                            if ($isThisSelected) $bgClass = $isCorrect ? 'bg-green-50 border-green-500 ring-1 ring-green-500' : 'bg-red-50 border-red-500 ring-1 ring-red-500';
                            if (!$isCorrect && $isThisCorrect) $bgClass = 'bg-green-50 border-green-500 italic';
                        ?>
                            <div class="p-3 rounded-xl border <?php echo $bgClass; ?> text-sm">
                                <span class="font-bold mr-2"><?php echo strtoupper($opt); ?>:</span>
                                <?php echo htmlspecialchars($row[$optionKey]); ?>
                                <?php if ($isThisSelected): ?> <small class="block mt-1 font-bold">(Candidate's Choice)</small> <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="mt-8">
            <a href="view-report?id=<?php echo $app_id; ?>" class="text-blue-600 hover:underline">← Back to Proctoring Report</a>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'?>