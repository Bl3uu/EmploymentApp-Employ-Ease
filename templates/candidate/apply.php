<?php include __DIR__ . '/../partials/header.php'; ?>

<main class="max-w-4xl mx-auto p-6">
    <?php if (isset($_GET['error'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-6">
            <?php echo htmlspecialchars($_GET['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'applied'): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-xl mb-6 shadow-sm flex items-center gap-3">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            <span>Application submitted! Our AI is currently screening your profile.</span>
        </div>
    <?php endif; ?>

    <a href="portal" class="text-sm text-gray-500 hover:text-black flex items-center gap-2 mb-6 transition">
        ← Back to Job List
    </a>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-50 bg-gray-50/50">
            <h1 class="text-3xl font-bold text-gray-900"><?php echo htmlspecialchars($job['title']); ?></h1>
            <p class="text-lg text-gray-600 mt-2"><?php echo htmlspecialchars($job['company']); ?> • <?php echo htmlspecialchars($job['location']); ?></p>
        </div>

        <div class="p-8">
            <h2 class="text-xl font-semibold mb-4">Job Description</h2>
            <div class="text-gray-700 leading-relaxed space-y-4 mb-10">
                <?php echo nl2br(htmlspecialchars($job['description'])); ?>
            </div>

            <hr class="mb-10">

            <h2 class="text-xl font-semibold mb-6">Submit Your Application</h2>
            <form action="submit-application" method="POST" enctype="multipart/form-data" class="space-y-6" id="applyForm">
                <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

                    <div class="bg-blue-50 border-2 border-dashed border-blue-200 rounded-2xl p-8 text-center">
                    <label class="cursor-pointer">
                        <div class="mb-4">
                            <svg class="w-12 h-12 text-blue-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </div>
                        <span class="text-blue-600 font-medium">Click to upload your resume</span>
                        <p class="text-xs text-gray-500 mt-1">PDF format preferred (Max 5MB)</p>
                        <p id="selectedResume" class="text-xs text-gray-600 mt-2 hidden"></p>
                        <input type="file" name="resume" id="resumeInput" class="hidden" required accept=".pdf,.doc,.docx">
                    </label>
                </div>

                <button type="submit" class="w-full bg-black text-white py-4 rounded-2xl font-bold hover:bg-gray-800 transition shadow-xl">
                    Submit Application & Start AI Screening
                </button>
            </form>
        </div>
    </div>
</main>

<script>
    const resumeInput = document.getElementById('resumeInput');
    const selectedResume = document.getElementById('selectedResume');

    resumeInput.addEventListener('change', function () {
        if (!this.files || this.files.length === 0) {
            selectedResume.classList.add('hidden');
            selectedResume.textContent = '';
            return;
        }

        selectedResume.textContent = `Selected file: ${this.files[0].name}`;
        selectedResume.classList.remove('hidden');
    });
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>