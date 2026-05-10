<?php include __DIR__ . '/../partials/header.php'?>

    <div class="max-w-5xl mx-auto p-8">
        <nav class="mb-4 text-sm text-gray-500">
            <a href="dashboard" class="hover:underline">Dashboard</a> / Proctoring Report
        </nav>
        
        <div class="flex justify-between items-start mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">
                    <?php echo htmlspecialchars(($candidate['first_name'] ?? '') . ' ' . ($candidate['last_name'] ?? '')); ?>
                </h1>
                <p class="text-gray-500">Exam: <?php echo htmlspecialchars($candidate['exam_title'] ?? 'N/A'); ?></p>
            </div>
            
            <?php if ($totalViolations >= 3): ?>
                <span class="bg-red-100 text-red-700 px-4 py-2 rounded-xl text-sm font-bold border border-red-200 uppercase tracking-wide">
                    Security review recommended
                </span>
            <?php else: ?>
                <span class="bg-green-100 text-green-700 px-4 py-2 rounded-xl text-sm font-bold border border-green-200">
                    No critical security flags
                </span>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase mb-1">AI Resume Score</p>
                <p class="text-3xl font-mono text-indigo-600">
                    <?php echo isset($candidate['ai_score']) ? $candidate['ai_score'] . '%': 'N/A'; ?>
                </p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase mb-1">Exam Result</p>
                <p class="text-3xl font-mono text-gray-800">
                    <?php echo isset($candidate['exam_score']) ? $candidate['exam_score'] . '%' : 'Pending'; ?>
                </p>
                <p class="text-xs uppercase tracking-widest text-gray-500 mt-2">
                    <?php
                        $examStatus = $candidate['exam_result_status'] ?? 'Pending';
                        if ($examStatus === 'Flagged' || $examStatus === 'Disqualified') {
                            $examStatus = 'Needs Review';
                        }
                        echo htmlspecialchars($examStatus);
                    ?>
                </p>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <p class="text-xs font-bold text-gray-400 uppercase mb-1">Application Status</p>
                <p class="text-3xl font-mono text-gray-800"><?php echo $candidate['status']; ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">AI Metrics Summary</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Resume Score</span>
                        <span class="font-bold"><?php echo isset($candidate['ai_score']) ? $candidate['ai_score'] . '%' : 'N/A'; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">AI Summary</span>
                        <span class="text-xs text-gray-500 max-w-xs truncate"><?php echo htmlspecialchars($candidate['ai_summary'] ?? 'No summary available'); ?></span>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Exam Performance</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Exam Score</span>
                        <span class="font-bold"><?php echo isset($candidate['exam_score']) ? $candidate['exam_score'] . '%' : 'Pending'; ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Exam Status</span>
                        <span class="text-sm <?php echo ($candidate['exam_result_status'] ?? '') === 'Passed' ? 'text-green-600' : 'text-red-600'; ?>">
                            <?php echo htmlspecialchars($candidate['exam_result_status'] ?? 'Pending'); ?>
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-gray-600">Proctoring Violations</span>
                        <span class="font-bold <?php echo $totalViolations > 0 ? 'text-red-600' : 'text-green-600'; ?>"><?php echo $totalViolations; ?></span>
                    </div>
                </div>
            </div>
        </div>
            <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50">
                <h2 class="font-bold text-gray-700">Detailed Security Timeline</h2>
            </div>
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-xs font-bold text-gray-400 uppercase tracking-wider">
                        <th class="px-6 py-4">Time</th>
                        <th class="px-6 py-4">Event Type</th>
                        <th class="px-6 py-4">Detailed Observation</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if ($totalViolations > 0): ?>
                        <?php foreach ($logs as $row): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 font-mono text-sm text-gray-500">
                                    <?php echo date('H:i:s', strtotime($row['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php 
                                        $action = $row['action'];
                                        if ($action === 'DISQUALIFIED') {
                                            $displayAction = 'Flagged';
                                        } elseif ($action === 'MISSING_FACE') {
                                            $displayAction = 'Face Missing';
                                        } elseif ($action === 'MULTIPLE_FACES') {
                                            $displayAction = 'Multiple Faces';
                                        } else {
                                            $displayAction = ucwords(strtolower(str_replace('_', ' ', $action)));
                                        }
                                        $badgeClass = 'bg-orange-50 text-orange-600';
                                        if (strpos($action, 'MISSING') !== false) {
                                            $badgeClass = 'bg-red-50 text-red-600';
                                        }
                                        if ($action === 'DISQUALIFIED') {
                                            $badgeClass = 'bg-black text-white';
                                        }
                                    ?>
                                    <span class="px-2 py-1 rounded text-[10px] font-bold uppercase <?php echo $badgeClass; ?>">
                                        <?php echo htmlspecialchars($displayAction); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <?php echo htmlspecialchars($row['description']); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-gray-400 italic">
                                No security violations detected during this session.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-8 flex justify-start gap-4 pl-6 ml-2">
            <button onclick="window.print()" class="bg-white border border-gray-300 px-6 py-2 rounded-xl text-sm font-bold hover:bg-gray-50">
                Print Report
            </button>
            <a href="review-exam?id=<?php echo $app_id; ?>" class="bg-black text-white px-6 py-2 rounded-xl text-sm font-bold hover:opacity-90">
                Review Exam Answers
            </a>
        </div>
    </div>

<?php include __DIR__ . '/../partials/footer.php'?>