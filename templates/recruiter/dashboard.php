<?php include __DIR__ . '/../partials/header.php'?>

<div class="max-w-7xl mx-auto p-6 space-y-6">
    <?php if (isset($_GET['msg'])): ?>
        <?php
            $msgMap = [
                'created' => 'Job created successfully.',
                'updated' => 'Job updated successfully.',
                'closed' => 'Job was closed.',
                'exam_assigned' => 'Exam was assigned to the applicant.',
                'status_updated' => 'Application status updated.',
                'exam_missing_for_job' => 'No exam exists for that job yet. Create an exam first.',
            ];
            $rawMsg = $_GET['msg'];
            $messageText = $msgMap[$rawMsg] ?? htmlspecialchars($rawMsg);
            $isWarn = in_array($rawMsg, ['exam_missing_for_job']) || strpos($rawMsg, 'Cannot change') !== false || strpos($rawMsg, 'Cannot reassign') !== false;
        ?>
        <div class="<?php echo $isWarn ? 'bg-yellow-100 border-yellow-300 text-yellow-800' : 'bg-green-100 border-green-300 text-green-800'; ?> border px-4 py-3 rounded-xl text-sm">
            <?php echo $messageText; ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <p class="text-sm text-gray-500">Active Job Posts</p>
            <h2 class="text-2xl font-semibold"><?php echo $activeJobCount; ?></h2>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <p class="text-sm text-gray-500">Total Applicants</p>
            <h2 class="text-2xl font-semibold"><?php echo $totalApplicants; ?></h2>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <p class="text-sm text-gray-500">AI Screened Resumes</p>
            <h2 class="text-2xl font-semibold"><?php echo $aiScreenedCount; ?></h2>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-6 border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Top Candidates</p>
            <h2 class="text-2xl font-semibold text-green-600">
                <?php echo ($aiScreenedCount > 0) ? 'Ready' : '0'; ?>
            </h2>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="text-lg font-semibold mb-2 flex items-center gap-2">
            <span>AI Resume Screening (NLP)</span>
            <span class="px-2 py-0.5 text-[10px] bg-blue-100 text-blue-700 rounded-full uppercase tracking-wider"></span>
        </h2>
        <p class="text-sm text-gray-600 leading-relaxed">
            Our system uses <b>Natural Language Processing (NLP)</b> to analyse applicant CVs and resumes. 
            The AI extracts relevant information and compares them with the job requirements.
        </p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="text-lg font-semibold mb-4">Top AI-Matched Candidate</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <?php if ($topMatch): ?>
                <div class="border rounded-xl p-4 border-green-500 bg-green-50 group relative">
                    <p class="text-xs text-green-700 font-bold uppercase tracking-wider">#1 Recommendation</p>
                    <h3 class="font-semibold text-lg">
                        <?php echo htmlspecialchars($topMatch['first_name'] . ' ' . $topMatch['last_name']); ?>
                    </h3>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($topMatch['job_title']); ?></p>
                    <p class="text-green-600 font-bold mt-1">AI Match Score: <?php echo $topMatch['ai_score']; ?>%</p>
                    
                    <a href="view-applicants?job_id=<?php echo (int)$topMatch['job_id']; ?>" 
                    class="mt-3 inline-block text-xs font-bold text-white bg-green-600 px-3 py-1.5 rounded-lg hover:bg-green-700 transition-colors">
                        View All Applicants
                    </a>
                </div>
            <?php else: ?>
                <p class="text-sm text-gray-500 italic">No applicants screened yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold">Your Job Listings</h2>
            <button onclick="window.location.href='post-job'" class="bg-black text-white px-4 py-2 rounded-xl text-sm hover:opacity-90">
                Post New Job
            </button>
        </div>

        <div class="bg-gray-50 rounded-2xl p-4 mb-6">
            <form method="GET" action="dashboard" class="space-y-3 md:space-y-0 md:flex md:items-end md:gap-3 flex-wrap">
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input type="text" name="search" placeholder="Job title or company..." value="<?php echo htmlspecialchars($search); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>

                <div class="w-full md:w-auto">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($location); ?>" placeholder="Search location..." class="w-full md:w-[180px] px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>

                <div class="w-full md:w-auto">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Job Type</label>
                    <select name="type" class="w-full md:w-[150px] px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">All Types</option>
                        <?php foreach ($availableTypes as $t): ?>
                            <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $type === $t ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($t); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="w-full md:w-auto">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full md:w-[130px] px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">All Status</option>
                        <option value="Active" <?php echo $status === 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Closed" <?php echo $status === 'Closed' ? 'selected' : ''; ?>>Closed</option>
                    </select>
                </div>

                <div class="w-full md:w-auto">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Sort</label>
                    <select name="sort" class="w-full md:w-[150px] px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="most_applicants" <?php echo $sort === 'most_applicants' ? 'selected' : ''; ?>>Most Applicants</option>
                        <option value="least_applicants" <?php echo $sort === 'least_applicants' ? 'selected' : ''; ?>>Least Applicants</option>
                    </select>
                </div>

                <button type="submit" class="w-full md:w-auto bg-black text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">
                    Filter
                </button>

                <?php if ($search || $location || $type || $status): ?>
                    <a href="dashboard" class="block md:inline-block text-center w-full md:w-auto px-4 py-2 border border-gray-300 text-gray-700 rounded-lg text-sm hover:bg-gray-100">
                        Clear Filters
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b text-gray-500">
                    <tr>
                        <th class="py-3">Job Title</th>
                        <th>Date Posted</th>
                        <th>Applicants</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($myJobs)): ?>
                        <?php foreach ($myJobs as $job): ?>
                            <tr class="border-b">
                                <td class="py-4 font-medium"><?php echo htmlspecialchars($job['title']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($job['created_at'])); ?></td>
                                <td>
                                    <span class="font-semibold text-blue-600">
                                        <?php echo $job['applicant_count'] ?? 0; ?> 
                                        <span class="text-gray-400 font-normal">/ <?php echo $job['max_applicants']; ?></span>
                                    </span>
                                </td>
                                <td>
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $job['status'] === 'Active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'; ?>">
                                        <?php echo $job['status']; ?>
                                    </span>
                                </td>
                                <td class="space-x-2">
                                    <a href="edit-job?id=<?php echo $job['id']; ?>" class="text-blue-600 hover:underline">Edit</a>
                                    
                                    <a href="view-applicants?job_id=<?php echo $job['id']; ?>" class="text-green-600 hover:underline font-medium">
                                        Applicants
                                    </a>
                                    
                                    <?php if($job['status'] === 'Active'): ?>
                                        <button onclick="closeJob(<?php echo $job['id']; ?>)" class="text-red-500 hover:underline">
                                            Close
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-8 text-center text-gray-500">No job listings found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm p-6">
        <h2 class="text-lg font-semibold mb-4">AI-Screened Applicant Rankings</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm whitespace-nowrap">
                <thead class="border-b text-gray-500">
                    <tr>
                        <th class="py-3 pr-4">Applicant</th>
                        <th class="pr-4">Position</th>
                        <th class="pr-4">AI Match</th>
                        <th class="pr-4">Status</th>
                        <th class="pr-4">Proctoring</th> <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($recentApplicants as $app): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-4 pr-4 font-medium"><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></td>
                        <td class="pr-4"><?php echo htmlspecialchars($app['job_title']); ?></td>
                        <td class="pr-4"><span class="font-semibold"><?php echo $app['ai_score']; ?>%</span></td>
                        <td class="pr-4 text-gray-600"><?php echo htmlspecialchars($app['status']); ?></td>
                        
                        <td class="pr-4">
<?php if ($app['status'] === 'Exam Submitted' || $app['status'] === 'Exam Completed' || $app['status'] === 'Interviewing'): ?>
                                <a href="view-report?id=<?php echo (int)$app['id']; ?>" class="group flex items-center gap-1">
                                    <span class="px-2 py-1 rounded text-[10px] font-bold uppercase bg-gray-100 text-gray-700 group-hover:bg-black group-hover:text-white transition-all">
                                        View Report
                                    </span>
                                </a>
                            <?php else: ?>
                                <span class="text-[10px] text-gray-400 italic">N/A</span>
                            <?php endif; ?>
                        </td>

                        <!-- FIXED ACTION BUTTONS HERE -->
                        <td class="py-4 space-x-2 text-right">
                            <?php if ($app['status'] === 'Applied' || $app['status'] === 'Screened'): ?>
                                <?php if (!empty($app['exam_id'])): ?>
                                    <button onclick="confirmAssignExam(<?php echo (int)$app['id']; ?>)" 
                                            class="text-xs font-bold text-blue-600 hover:text-blue-800">
                                        Assign Exam
                                    </button>
                                <?php else: ?>
                                    <a href="manage-exams" class="text-xs font-bold text-orange-600 hover:text-orange-700">
                                        Create Exam First
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <!-- For Reject, we can just add a quick case to your router later -->
                            <button onclick="confirmReject(<?php echo (int)$app['id']; ?>)" 
                                    class="text-xs font-bold text-red-500 hover:text-red-700">
                                Reject
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function confirmAssignExam(appId) {
    if (confirm('Assign exam to this applicant now?')) {
        window.location.href = 'assign-exam?app_id=' + appId;
    }
}

function confirmReject(appId) {
    if (confirm('Mark this applicant as Rejected?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'update-application-status';

        const appInput = document.createElement('input');
        appInput.type = 'hidden';
        appInput.name = 'app_id';
        appInput.value = appId;
        form.appendChild(appInput);

        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = 'Rejected';
        form.appendChild(statusInput);

        const jobInput = document.createElement('input');
        jobInput.type = 'hidden';
        jobInput.name = 'job_id';
        jobInput.value = '';
        form.appendChild(jobInput);

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = 'csrf_token';
        csrfInput.value = '<?php echo $_SESSION['csrf_token'] ?? ''; ?>';
        form.appendChild(csrfInput);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>
                        
<?php include __DIR__ . '/../partials/footer.php'?>