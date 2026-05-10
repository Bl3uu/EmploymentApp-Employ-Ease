<?php include __DIR__ . '/../partials/header.php'?>

<div class="max-w-3xl mx-auto p-6">
    <div class="mb-4">
        <a href="dashboard" class="text-sm text-gray-500 hover:text-black flex items-center transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Dashboard
        </a>
    </div>

    <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
        <h2 class="text-2xl font-bold mb-6"><?php echo $jobData ? 'Edit Job Listing' : 'Post a New Job'; ?></h2>
        
<form action="process-job" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <?php if ($jobData): ?>
                <input type="hidden" name="job_id" value="<?php echo $jobData['id']; ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Job Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($jobData['title'] ?? ''); ?>" required class="w-full mt-1 p-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-black focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Company</label>
                    <input type="text" name="company" value="<?php echo htmlspecialchars($jobData['company'] ?? ''); ?>" required class="w-full mt-1 p-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-black focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($jobData['location'] ?? ''); ?>" required class="w-full mt-1 p-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-black focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Type</label>
                    <select name="type" class="w-full mt-1 p-2 border border-gray-200 rounded-xl">
                        <option value="Full-time" <?php echo ($jobData['type'] ?? '') === 'Full-time' ? 'selected' : ''; ?>>Full-time</option>
                        <option value="Remote" <?php echo ($jobData['type'] ?? '') === 'Remote' ? 'selected' : ''; ?>>Remote</option>
                        <option value="Contract" <?php echo ($jobData['type'] ?? '') === 'Contract' ? 'selected' : ''; ?>>Contract</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" class="w-full mt-1 p-2 border border-gray-200 rounded-xl">
                        <option value="Active" <?php echo ($jobData['status'] ?? '') === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Draft" <?php echo ($jobData['status'] ?? '') === 'Draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="Closed" <?php echo ($jobData['status'] ?? '') === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Max Applicants</label>
                    <input type="number" name="max_applicants" value="<?php echo $jobData['max_applicants'] ?? 50; ?>" class="w-full mt-1 p-2 border border-gray-200 rounded-xl">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" rows="5" required class="w-full mt-1 p-2 border border-gray-200 rounded-xl focus:ring-2 focus:ring-black focus:outline-none"><?php echo htmlspecialchars($jobData['description'] ?? ''); ?></textarea>
            </div>

            <div class="flex flex-col md:flex-row gap-3 pt-4">
                <button type="submit" class="flex-1 bg-black text-white py-3 rounded-xl font-semibold hover:bg-gray-800 transition-colors">
                    <?php echo $jobData ? 'Update Job Listing' : 'Publish Job Listing'; ?>
                </button>
                <a href="dashboard" class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-xl font-semibold text-center hover:bg-gray-200 transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../partials/footer.php'?>