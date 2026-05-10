<?php include __DIR__ . '/../partials/header.php'?>

<div class="max-w-6xl mx-auto p-6">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Exam Management</h1>
        <button onclick="document.getElementById('newExamModal').classList.toggle('hidden')" class="bg-black text-white px-6 py-2 rounded-xl font-semibold hover:opacity-90">
            + Create New Exam
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($filteredExams as $exam): ?>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between">
                <div>
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-widest"><?php echo $exam['job_title']; ?></span>
                    <h3 class="text-xl font-bold mt-1 text-gray-800"><?php echo $exam['title']; ?></h3>
                    <div class="mt-4 space-y-2 text-sm text-gray-500">
                        <p class="flex items-center">⏱ <?php echo $exam['duration_min']; ?> Minutes</p>
                        <p class="flex items-center">🎯 Passing Mark: <?php echo $exam['passing_mark']; ?>%</p>
                    </div>
                </div>
                
                <div class="mt-6 flex gap-2">
                    <a href="edit-exam-questions?id=<?php echo $exam['id']; ?>" class="flex-1 text-center bg-gray-50 text-gray-700 py-2 rounded-lg text-sm font-bold hover:bg-gray-100">
                        Manage Questions
                    </a>
                    <a href="edit-exam-settings?id=<?php echo $exam['id']; ?>" class="p-2 text-gray-400 hover:text-black">
                        ⚙️
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="newExamModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl">
            <h2 class="text-2xl font-bold mb-4">New Assessment</h2>
            <form action="process-exam" method="POST" class="space-y-4">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Link to Job</label>
                    <select name="job_id" required class="w-full mt-1 p-3 border border-gray-200 rounded-xl">
                        <?php foreach ($availableJobs as $job): ?>
                            <option value="<?php echo $job['id']; ?>"><?php echo $job['title']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Exam Title</label>
                    <input type="text" name="title" placeholder="e.g. SQL Intermediate Test" required class="w-full mt-1 p-3 border border-gray-200 rounded-xl">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Duration (Min)</label>
                        <input type="number" name="duration" value="30" required class="w-full mt-1 p-3 border border-gray-200 rounded-xl">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Pass Mark (%)</label>
                        <input type="number" name="pass_mark" value="70" required class="w-full mt-1 p-3 border border-gray-200 rounded-xl">
                    </div>
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 bg-black text-white py-3 rounded-xl font-bold">Create Exam</button>
                    <button type="button" onclick="document.getElementById('newExamModal').classList.add('hidden')" class="flex-1 bg-gray-100 py-3 rounded-xl">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'?>