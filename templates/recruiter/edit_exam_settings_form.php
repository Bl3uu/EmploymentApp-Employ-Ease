<?php include __DIR__ . '/../partials/header.php'?>

<div class="max-w-2xl mx-auto p-6">
    <div class="mb-8">
        <a href="manage-exams" class="text-sm text-gray-500 hover:underline">← Back to Manage Exams</a>
        <h1 class="text-3xl font-bold text-gray-900 mt-2">Exam Settings</h1>
        <p class="text-gray-600">Adjusting settings for: <span class="font-semibold"><?php echo htmlspecialchars($exam['job_title']); ?></span></p>
    </div>

<form action="update-exam-settings" method="POST" class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 space-y-6">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
        <input type="hidden" name="exam_id" value="<?php echo $exam['id']; ?>">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Exam Display Title</label>
            <input type="text" name="title" value="<?php echo htmlspecialchars($exam['title']); ?>" required 
                   class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-black focus:outline-none">
            <p class="text-xs text-gray-400 mt-1">This is what the candidate sees when they start the test.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Duration (Minutes)</label>
                <input type="number" name="duration_min" value="<?php echo $exam['duration_min']; ?>" required 
                       class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-black focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Passing Mark (%)</label>
                <input type="number" name="passing_mark" value="<?php echo $exam['passing_mark']; ?>" required 
                       class="w-full p-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-black focus:outline-none">
            </div>
        </div>

        <div class="pt-4 flex flex-col gap-3">
            <button type="submit" class="w-full bg-black text-white py-4 rounded-2xl font-bold hover:opacity-90 transition-opacity">
                Save Changes
            </button>
            
            <button type="button" onclick="confirmDelete()" class="w-full bg-red-50 text-red-600 py-3 rounded-2xl font-semibold hover:bg-red-100 transition-colors">
                Delete Exam
            </button>
        </div>
    </form>
</div>

<script>
function confirmDelete() {
    if(confirm('Are you sure you want to delete this entire exam? All questions and candidate scores for this exam will be permanently removed.')) {
        window.location.href = 'delete-exam?id=<?php echo $exam['id']; ?>';
    }
}
</script>

<?php include __DIR__ . '/../partials/footer.php'?>