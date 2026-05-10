<?php include __DIR__ . '/../partials/header.php'?>

<div class="max-w-5xl mx-auto p-6">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <a href="manage-exams" class="text-sm text-gray-500 hover:underline">← Back to Exams</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-2">Questions for: <?php echo $exam['title']; ?></h1>
            <p class="text-gray-500 text-sm">Job: <?php echo $exam['job_title']; ?></p>
        </div>
        <button onclick="document.getElementById('addQuestionModal').classList.remove('hidden')" class="bg-black text-white px-5 py-2 rounded-xl font-bold text-sm">
            + Add Question
        </button>
    </div>

    <div class="space-y-4">
        <?php if (empty($questions)): ?>
            <div class="bg-white p-12 text-center rounded-3xl border border-dashed border-gray-300">
                <p class="text-gray-400">No questions added yet. Click "+ Add Question" to start.</p>
            </div>
        <?php else: ?>
            <?php foreach ($questions as $index => $q): ?>
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-start mb-3">
                        <span class="text-xs font-bold text-gray-400 uppercase">Question <?php echo $index + 1; ?></span>
                        <div class="flex gap-2">
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded font-bold">Correct: <?php echo $q['correct_answer']; ?></span>
                            
                            <a href="javascript:void(0)" 
                                onclick="confirmDeleteQuestion(<?php echo $q['id']; ?>, <?php echo $exam_id; ?>)" 
                                class="text-gray-300 hover:text-red-600 transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <p class="font-medium text-gray-800 mb-4"><?php echo htmlspecialchars($q['question_text']); ?></p>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div class="p-2 border rounded-lg <?php echo $q['correct_answer'] == 'A' ? 'bg-green-50 border-green-200' : 'bg-gray-50'; ?>"><b>A:</b> <?php echo htmlspecialchars($q['option_a']); ?></div>
                        <div class="p-2 border rounded-lg <?php echo $q['correct_answer'] == 'B' ? 'bg-green-50 border-green-200' : 'bg-gray-50'; ?>"><b>B:</b> <?php echo htmlspecialchars($q['option_b']); ?></div>
                        <div class="p-2 border rounded-lg <?php echo $q['correct_answer'] == 'C' ? 'bg-green-50 border-green-200' : 'bg-gray-50'; ?>"><b>C:</b> <?php echo htmlspecialchars($q['option_c']); ?></div>
                        <div class="p-2 border rounded-lg <?php echo $q['correct_answer'] == 'D' ? 'bg-green-50 border-green-200' : 'bg-gray-50'; ?>"><b>D:</b> <?php echo htmlspecialchars($q['option_d']); ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="addQuestionModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-3xl p-8 max-w-2xl w-full shadow-2xl">
            <h2 class="text-2xl font-bold mb-6">Add New Question</h2>
<form action="process-question" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Question Text</label>
                    <textarea name="question_text" required class="w-full mt-1 p-3 border rounded-xl" rows="3"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <input type="text" name="option_a" placeholder="Option A" required class="p-3 border rounded-xl">
                    <input type="text" name="option_b" placeholder="Option B" required class="p-3 border rounded-xl">
                    <input type="text" name="option_c" placeholder="Option C" required class="p-3 border rounded-xl">
                    <input type="text" name="option_d" placeholder="Option D" required class="p-3 border rounded-xl">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Correct Answer</label>
                    <select name="correct_answer" class="w-full mt-1 p-3 border rounded-xl font-bold">
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-black text-white py-3 rounded-xl font-bold">Save Question</button>
                    <button type="button" onclick="document.getElementById('addQuestionModal').classList.add('hidden')" class="flex-1 bg-gray-100 py-3 rounded-xl font-bold">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function confirmDeleteQuestion(questionId, examId) {
    if(confirm('Are you sure you want to delete this question? This will also remove any candidate answers associated with it.')) {
        window.location.href = 'delete-question?id=' + questionId + '&exam_id=' + examId;
    }
}
</script>

<?php include __DIR__ . '/../partials/footer.php'?>