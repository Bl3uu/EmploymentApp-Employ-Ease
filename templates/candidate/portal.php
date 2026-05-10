<?php 
// Note: Logic for fetching $jobs should ideally move to a Controller later
include __DIR__ . '/../partials/header.php'; 
?>

<main class="max-w-7xl mx-auto p-6">

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'exam_completed'): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            Congratulations! You completed the exam with a score of <strong><?php echo $_GET['score']; ?>%</strong>.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'voided'): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            Your exam was terminated due to security violations.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error']) && $_GET['error'] === 'exam_not_ready'): ?>
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded mb-6">
            Exam not found for this application yet. Please wait for recruiter setup or assignment.
        </div>
    <?php endif; ?>

    <?php
        $progressSteps = ['Applied', 'Screened', 'Exam Assigned', 'Exam Completed', 'Interviewing', 'Offered'];
        $latestApp = $myApplications[0] ?? null;
        $currentProgressIndex = $latestApp ? array_search($latestApp['status'], $progressSteps) : 0;
    ?>

    <?php if ($latestApp): ?>
        <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 mb-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Latest Application: <?php echo htmlspecialchars($latestApp['title']); ?></h2>
                    <p class="text-sm text-gray-500">Status: <strong><?php echo htmlspecialchars($latestApp['status']); ?></strong></p>
                </div>
                <a href="progress" class="text-sm px-4 py-2 bg-black text-white rounded-xl hover:bg-gray-800 transition-colors">View Full Progress</a>
            </div>
            <div class="flex items-center justify-between">
                <?php foreach ($progressSteps as $index => $step): ?>
                    <?php $active = $index <= $currentProgressIndex; ?>
                    <div class="flex flex-col items-center">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold <?php echo $active ? 'bg-black text-white' : 'bg-gray-200 text-gray-500'; ?>">
                            <?php echo $index + 1; ?>
                        </span>
                        <p class="text-xs mt-1 <?php echo $active ? 'text-gray-900' : 'text-gray-400'; ?>"><?php echo htmlspecialchars($step); ?></p>
                    </div>
                    <?php if ($index < count($progressSteps) - 1): ?>
                        <div class="flex-1 h-1 mx-2 <?php echo $index < $currentProgressIndex ? 'bg-black' : 'bg-gray-200'; ?>"></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
            <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold">Open Jobs</p>
            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo count($jobs); ?></p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
            <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold">My Applications</p>
            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo count($myApplications); ?></p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
            <p class="text-xs uppercase tracking-wider text-gray-400 font-semibold">Exam Assigned</p>
            <p class="text-2xl font-bold text-orange-600 mt-1">
                <?php echo count(array_filter($myApplications, function ($a) { return ($a['status'] ?? '') === 'Exam Assigned'; })); ?>
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <aside class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm p-6 text-center">
                <div class="w-20 h-20 bg-blue-100 text-blue-600 rounded-full mx-auto mb-4 flex items-center justify-center text-2xl font-bold">
                    <?php echo substr($_SESSION['first_name'], 0, 1); ?>
                </div>
                <h2 class="font-semibold text-lg"><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></h2>
                <p class="text-sm text-gray-500">Candidate Account</p>
                <a href="portal" class="mt-6 block w-full bg-black text-white py-2 rounded-xl hover:opacity-90 transition-all text-sm font-medium">
                    Refresh Portal
                </a>
                <a href="profile" class="mt-3 block w-full border border-gray-200 text-gray-900 py-2 rounded-xl hover:bg-gray-50 transition-all text-sm font-medium text-center">
                    Manage Profile Skills
                </a>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-4">My Applications</h2>
                
                <?php if (empty($myApplications)): ?>
                    <p class="text-xs text-gray-500 italic">No applications yet.</p>
                <?php else: ?>  
                    <div class="space-y-4">
                        <?php foreach ($myApplications as $app): ?>
                            <div class="border-b border-gray-50 pb-4 last:border-0 last:pb-0">
                                <h3 class="text-sm font-bold text-gray-900 leading-tight"><?php echo htmlspecialchars($app['title']); ?></h3>
                                <p class="text-[10px] text-gray-400 mb-2"><?php echo htmlspecialchars($app['company']); ?></p>
                                
                                <div class="flex flex-wrap items-center gap-2">
                                    <?php 
                                        $statusClasses = [
                                            'Applied'       => 'bg-blue-50 text-blue-600',
                                            'Screened'      => 'bg-yellow-50 text-yellow-600',
                                            'Exam Assigned' => 'bg-orange-50 text-orange-600',
                                            'Interviewing'  => 'bg-purple-50 text-purple-600',
                                            'Offered'       => 'bg-green-50 text-green-600',
                                            'Rejected'      => 'bg-red-50 text-red-600'
                                        ];
                                        $class = $statusClasses[$app['status']] ?? 'bg-gray-50 text-gray-600';
                                    ?>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold <?php echo $class; ?>">
                                        <?php echo $app['status']; ?>
                                    </span>

                                    <?php if ($app['status'] === 'Exam Assigned'): ?>
                                        <button
                                            type="button"
                                            class="text-[10px] font-bold text-orange-600 hover:underline animate-pulse"
                                            onclick="openExamRulesModal(<?php echo (int)$app['id']; ?>, '<?php echo htmlspecialchars(addslashes($app['title'])); ?>')"
                                        >
                                            Start Technical Exam →
                                        </button>
                                    <?php endif; ?>

                                    <?php 
                                    // Logic check: If they are Interviewing or Offered, they likely passed an exam
                                    // We can also check if exam_id exists for this job in your query
                                    if (!empty($app['exam_id']) && in_array($app['status'], ['Interviewing', 'Offered', 'Rejected'])): ?>
                                        <a href="view-results?exam_id=<?php echo (int)$app['exam_id']; ?>" class="text-[10px] font-bold text-blue-600 hover:underline">
                                            View Exam Score 📊
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($app['status'] === 'Interviewing'): ?>
                                        <?php $confirmedSlot = $interviewConfirmations[$app['id']] ?? null; ?>
                                        <?php if ($confirmedSlot): ?>
                                            <div class="w-full rounded-2xl bg-green-50 border border-green-100 p-3 text-sm text-green-700 mt-3">
                                                Interview confirmed: <?php echo htmlspecialchars($confirmedSlot); ?>
                                            </div>
                                        <?php else: ?>
                                            <button
                                                type="button"
                                                class="text-[10px] font-bold text-purple-600 hover:underline mt-3"
                                                onclick="openInterviewModal(<?php echo (int)$app['id']; ?>, '<?php echo htmlspecialchars(addslashes($app['title'])); ?>')"
                                            >
                                                Confirm interview slot →
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

        <section class="lg:col-span-3 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm p-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <input type="text" id="searchInput" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>" placeholder="Search job titles, companies, or descriptions..." 
                            class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-black outline-none">
                    </div>
                    <div class="ml-4 flex items-center gap-2">
                        <button type="button" id="filterBtn" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            Filters
                        </button>
                        <button type="submit" class="bg-black text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition">Search</button>
                        <?php if (!empty($search) || !empty($location) || !empty($type) || ($sort ?? '') !== 'newest'): ?>
                            <a href="portal" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition">Clear</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Filter Modal -->
            <div id="filterModal" class="fixed inset-0 bg-black/50 hidden z-50 items-center justify-center p-4">
                <div class="bg-white w-full max-w-md rounded-2xl shadow-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h2 class="text-lg font-bold text-gray-900">Filter Jobs</h2>
                    </div>
                    <form action="portal" method="GET" class="p-6 space-y-4">
                        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <input type="text" name="location" value="<?php echo htmlspecialchars($location ?? ''); ?>" placeholder="e.g. New York, Remote..." class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-black outline-none">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Job Type</label>
                            <select name="type" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-black outline-none">
                                <option value="">All types</option>
                                <?php foreach ($types as $jobType): ?>
                                    <option value="<?php echo htmlspecialchars($jobType); ?>" <?php echo ($type === $jobType) ? 'selected' : ''; ?>><?php echo htmlspecialchars($jobType); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Sort</label>
                            <select name="sort" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-black outline-none">
                                <option value="newest" <?php echo ($sort ?? 'newest') === 'newest' ? 'selected' : ''; ?>>Newest first</option>
                                <option value="oldest" <?php echo ($sort ?? '') === 'oldest' ? 'selected' : ''; ?>>Oldest first</option>
                                <option value="most_applicants" <?php echo ($sort ?? '') === 'most_applicants' ? 'selected' : ''; ?>>Most applicants</option>
                                <option value="least_applicants" <?php echo ($sort ?? '') === 'least_applicants' ? 'selected' : ''; ?>>Least applicants</option>
                            </select>
                        </div>
                        
                        <div class="flex gap-3 pt-4">
                            <button type="submit" class="flex-1 bg-black text-white py-2 rounded-lg font-semibold hover:bg-gray-800 transition">Apply Filters</button>
                            <button type="button" onclick="closeFilterModal()" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-lg font-semibold hover:bg-gray-200 transition">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="jobList" class="space-y-4">
                <h2 class="text-2xl font-bold text-gray-900 px-2">Available Jobs</h2>
                <?php if (empty($jobs)): ?>
                    <div class="bg-white rounded-2xl shadow-sm p-6 text-sm text-gray-500">
                        No open jobs are available right now. Please check again later.
                    </div>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                        <div class="job-card bg-white rounded-2xl shadow-sm p-6 flex justify-between items-center hover:shadow-md transition-shadow">
                            <div>
                                <h3 class="font-semibold text-lg"><?php echo htmlspecialchars($job['title']); ?></h3>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($job['company']); ?> • <?php echo htmlspecialchars($job['location']); ?></p>
                            </div>
                            <a href="apply?id=<?php echo (int)$job['id']; ?>" class="bg-black text-white px-6 py-2 rounded-xl text-sm hover:bg-gray-800 transition">
                                View & Apply
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>

<div id="examRulesModal" class="fixed inset-0 bg-black/50 hidden z-50 items-center justify-center p-4">
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-xl font-bold text-gray-900">Before You Start the Exam</h2>
            <p id="examRulesTitle" class="text-sm text-gray-500 mt-1"></p>
        </div>

        <div class="p-6 space-y-5 max-h-[70vh] overflow-y-auto">
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-sm text-blue-800">
                    This exam is AI-proctored. Make sure your environment is ready before continuing.
                </p>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Precautions</h3>
                <ul class="text-sm text-gray-700 space-y-1">
                    <li>- Use a stable internet connection and a charged device.</li>
                    <li>- Allow camera access before starting.</li>
                    <li>- Stay in a quiet room with good lighting.</li>
                    <li>- Keep only one browser tab and one monitor active.</li>
                </ul>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Exam Rules</h3>
                <ul class="text-sm text-gray-700 space-y-1">
                    <li>- Do not refresh, close, or navigate away from the exam page.</li>
                    <li>- Do not switch tabs or open other windows/applications.</li>
                    <li>- Do not copy/paste any exam content.</li>
                    <li>- Stay visible in front of the camera at all times.</li>
                </ul>
            </div>

            <div>
                <h3 class="font-semibold text-gray-900 mb-2">Violations That Are Monitored</h3>
                <ul class="text-sm text-gray-700 space-y-1">
                    <li>- Tab switch / window focus loss</li>
                    <li>- Face missing from webcam</li>
                    <li>- Multiple faces detected</li>
                    <li>- Copy/paste attempts</li>
                </ul>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <p class="text-sm text-yellow-800">
                    Maximum allowed violations: <strong>3</strong>. Reaching the limit may auto-terminate your exam attempt.
                </p>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex gap-3">
            <button type="button" class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-200 transition" onclick="closeExamRulesModal()">
                Cancel
            </button>
            <a id="proceedExamLink" href="#" class="flex-1 text-center bg-black text-white py-3 rounded-xl font-semibold hover:bg-gray-800 transition">
                I Understand, Start Exam
            </a>
        </div>
    </div>
</div>

<div id="interviewModal" class="fixed inset-0 bg-black/50 hidden z-50 items-center justify-center p-4">
    <div class="bg-white w-full max-w-xl rounded-2xl shadow-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-xl font-bold text-gray-900">Confirm Interview Slot</h2>
            <p id="interviewModalTitle" class="text-sm text-gray-500 mt-1"></p>
        </div>

        <form id="interviewForm" action="confirm-interview" method="POST">
            <?php echo csrf_token_field(); ?>
            <input type="hidden" name="app_id" id="interviewAppId" value="">

            <div class="p-6 space-y-5">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <?php
                        $slots = [
                            'Monday, 10:00 AM',
                            'Wednesday, 2:00 PM',
                            'Friday, 11:00 AM',
                        ];
                    ?>
                    <?php foreach ($slots as $slot): ?>
                        <label class="cursor-pointer rounded-2xl border border-gray-200 p-4 hover:border-black transition">
                            <input type="radio" name="slot" value="<?php echo htmlspecialchars($slot); ?>" class="mr-2" required>
                            <span class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($slot); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="text-sm text-gray-500">
                    If these slots do not work, follow up with your recruiter directly for an alternate time.
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 flex gap-3">
                <button type="button" class="flex-1 bg-gray-100 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-200 transition" onclick="closeInterviewModal()">
                    Cancel
                </button>
                <button type="submit" class="flex-1 bg-black text-white py-3 rounded-xl font-semibold hover:bg-gray-900 transition">
                    Confirm Interview
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openExamRulesModal(appId, jobTitle) {
        const modal = document.getElementById('examRulesModal');
        const title = document.getElementById('examRulesTitle');
        const proceedLink = document.getElementById('proceedExamLink');

        title.textContent = `Assessment for: ${jobTitle}`;
        proceedLink.href = `take-exam?app_id=${appId}`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeExamRulesModal() {
        const modal = document.getElementById('examRulesModal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    function openInterviewModal(appId, jobTitle) {
        const modal = document.getElementById('interviewModal');
        const title = document.getElementById('interviewModalTitle');
        const input = document.getElementById('interviewAppId');

        title.textContent = `Confirm a slot for: ${jobTitle}`;
        input.value = appId;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeInterviewModal() {
        const modal = document.getElementById('interviewModal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    function openFilterModal() {
        const modal = document.getElementById('filterModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeFilterModal() {
        const modal = document.getElementById('filterModal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    // Add event listener to filter button
    document.getElementById('filterBtn').addEventListener('click', openFilterModal);

    async function fetchCandidateNotifications() {
        try {
            const response = await fetch('notifications');
            if (!response.ok) {
                return;
            }
            const data = await response.json();
            const container = document.getElementById('notificationFeed');
            const count = document.getElementById('notificationCount');

            if (!container || !count) {
                return;
            }

            count.textContent = `${data.notifications.length} recent`;

            if (data.notifications.length === 0) {
                container.innerHTML = '<p class="text-sm text-gray-500">No notifications yet. Important updates will appear here as your application moves forward.</p>';
                return;
            }

            container.innerHTML = data.notifications.map(notification => {
                return `
                    <div class="rounded-2xl border border-gray-100 bg-gray-50 p-4">
                        <p class="text-xs uppercase tracking-widest text-gray-400">${notification.job_title}</p>
                        <p class="text-sm font-semibold text-gray-900 mt-1">${notification.action}</p>
                        <p class="text-sm text-gray-600 mt-2">${notification.description}</p>
                        <p class="text-[10px] text-gray-400 mt-2">${new Date(notification.created_at).toLocaleString()}</p>
                    </div>
                `;
            }).join('');
        } catch (err) {
            console.warn('Unable to refresh notifications', err);
        }
    }

    if (window.location.pathname.endsWith('portal') || window.location.pathname.endsWith('portal')) {
        fetchCandidateNotifications();
        setInterval(fetchCandidateNotifications, 30000);
    }
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>