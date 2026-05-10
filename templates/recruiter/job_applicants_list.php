<?php include __DIR__ . '/../partials/header.php'?>

<div class="max-w-7xl mx-auto p-6">
    <?php if (isset($_GET['msg'])): ?>
        <?php
            $msgMap = [
                'status_updated' => 'Application status updated.',
                'exam_missing_for_job' => 'No exam exists for this job yet. Create one first.',
                'exam_not_allowed' => 'Cannot reassign exam. Candidate has already passed this exam.',
            ];
            $rawMsg = $_GET['msg'];
            $messageText = $msgMap[$rawMsg] ?? htmlspecialchars($rawMsg);
            $isWarn = in_array($rawMsg, ['exam_missing_for_job', 'exam_not_allowed']) || strpos($rawMsg, 'Cannot change') !== false;
        ?>
        <div class="<?php echo $isWarn ? 'bg-yellow-100 border-yellow-300 text-yellow-800' : 'bg-green-100 border-green-300 text-green-800'; ?> border px-4 py-3 rounded-xl text-sm mb-4">
            <?php echo $messageText; ?>
        </div>
    <?php endif; ?>

    <div class="mb-8 flex justify-between items-end">
        <div>
            <a href="dashboard" class="text-sm text-gray-500 hover:underline">← Back to Dashboard</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Applicants for: <?php echo htmlspecialchars($job['title']); ?></h1>
        </div>
        <div class="text-sm text-gray-500">
            Total Candidates: <span class="font-bold text-black"><?php echo count($applicants); ?></span>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <form id="bulkForm" action="bulk-update-status" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
            <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
            <div class="bg-gray-50 border-b border-gray-100 px-6 py-4 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" id="selectAll" class="rounded">
                        Select All
                    </label>
                </div>
                <div class="flex items-center gap-2">
                    <select name="bulk_status" class="px-3 py-1 text-sm border border-gray-200 rounded-lg">
                        <option value="">Bulk Action</option>
                        <option value="Screened">Screen</option>
                        <option value="Rejected">Reject</option>
                        <option value="Passed">Pass</option>
                        <option value="Failed">Fail</option>
                    </select>
                    <button type="submit" class="px-4 py-1 text-sm bg-black text-white rounded-lg hover:bg-gray-800">Apply</button>
                </div>
            </div>
        </form>

        <table class="w-full text-left">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider w-12">
                        <input type="checkbox" id="selectAllHeader">
                    </th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Candidate</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">AI Score</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Exam Score</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Notes</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                <?php foreach ($applicants as $app): ?>
                <tr class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-6 py-4">
                        <input type="checkbox" form="bulkForm" name="app_ids[]" value="<?php echo $app['app_id']; ?>" class="bulk-checkbox rounded">
                    </td>
                    <td class="px-6 py-4 max-w-md">
                        <div class="flex items-center gap-2 mb-2">
                            <div class="font-bold text-gray-900">
                                <?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?>
                            </div>
                            <?php if ($app['has_violations']): ?>
                                <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 1.944A11.954 11.954 0 012.166 5C2.056 5.649 2 6.319 2 7c0 5.225 3.34 9.67 8 11.317C14.66 16.67 18 12.225 18 7c0-.682-.057-1.351-.166-2A11.954 11.954 0 0110 1.944zM11 14a1 1 0 11-2 0 1 1 0 012 0zm0-7a1 1 0 10-2 0v3a1 1 0 102 0V7z" clip-rule="evenodd"></path>
                                </svg>
                            <?php endif; ?>
                        </div>
                        <div class="text-xs text-gray-400 mb-2">
                            <?php echo htmlspecialchars($app['email']); ?>
                        </div>

                        <?php if (!empty($app['profile_skills'])): ?>
                            <div class="mb-3">
                                <div class="text-[10px] text-gray-500 font-semibold mb-1">Profile Skills:</div>
                                <div class="flex flex-wrap gap-1.5">
                                    <?php foreach (explode(', ', $app['profile_skills']) as $skill): ?>
                                        <span class="text-[10px] bg-gray-100 text-gray-700 rounded-full px-2.5 py-1"><?php echo htmlspecialchars($skill); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($app['ai_summary'])): ?>
                            <button type='button' class='text-xs font-semibold text-purple-700 border border-purple-200 bg-purple-50 rounded-full px-3 py-1 hover:bg-purple-100 transition-colors' 
                                onmouseenter="showAiTooltip(this, <?php echo htmlspecialchars(json_encode($app['ai_summary']), ENT_QUOTES, 'UTF-8'); ?>)" 
                                onfocus="showAiTooltip(this, <?php echo htmlspecialchars(json_encode($app['ai_summary']), ENT_QUOTES, 'UTF-8'); ?>)" 
                                onmouseleave='hideAiTooltip()' 
                                onblur='hideAiTooltip()'>
                                <span class='inline-flex items-center gap-1'>
                                    <svg class='w-3.5 h-3.5' viewBox='0 0 24 24' fill='currentColor'><path d='M12 2a10 10 0 100 20 10 10 0 000-20zm.75 15a.75.75 0 01-1.5 0v-.75a.75.75 0 011.5 0V17zm.75-4.75a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h.75V7.75a.75.75 0 011.5 0v4.5z'/></svg>
                                    AI Insights
                                </span>
                            </button>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-600">
                            <?php echo $app['status']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <div class="inline-block px-3 py-1 rounded-lg bg-purple-50 text-purple-700 font-black">
                            <?php echo $app['ai_score']; ?>%
                        </div>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <?php if ($app['exam_score'] !== null): ?>
                            <div class="inline-block px-3 py-1 rounded-lg bg-green-50 text-green-700 font-black">
                                <?php echo $app['exam_score']; ?>%
                            </div>
                        <?php else: ?>
                            <span class="text-xs text-gray-300 italic font-medium">Pending</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-6 py-4 text-center">
                        <button type="button" onclick="openNotesModal(<?php echo $app['app_id']; ?>, '<?php echo addslashes($app['recruiter_notes'] ?? ''); ?>')" class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                            <?php echo !empty($app['recruiter_notes']) ? 'Edit' : 'Add'; ?> Notes
                        </button>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <?php 
                            $status = $app['status'];
                            $hasExam = !empty($app['exam_id']);
                            
                            // Action buttons based on current status
                            switch ($status) {
                                case 'Applied':
                                    // Screen the candidate
                                    echo '<form action="update-application-status" method="POST" class="inline">
                                        <input type="hidden" name="app_id" value="'.$app['app_id'].'">
                                        <input type="hidden" name="job_id" value="'.$job_id.'">
                                        <input type="hidden" name="csrf_token" value="'.($_SESSION['csrf_token'] ?? '').'">
                                        <input type="hidden" name="status" value="Screened">
                                        <button type="submit" class="px-2 py-1 text-xs bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200">Screen</button>
                                    </form>';
                                    // Assign exam if exists
                                    if ($hasExam) {
                                        echo '<form action="update-application-status" method="POST" class="inline">
                                            <input type="hidden" name="app_id" value="'.$app['app_id'].'">
                                            <input type="hidden" name="job_id" value="'.$job_id.'">
                                            <input type="hidden" name="csrf_token" value="'.($_SESSION['csrf_token'] ?? '').'">
                                            <input type="hidden" name="status" value="Exam Assigned">
                                            <button type="submit" class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">Assign Exam</button>
                                        </form>';
                                    }
                                    // Reject
                                    echo '<form action="update-application-status" method="POST" class="inline">
                                        <input type="hidden" name="app_id" value="'.$app['app_id'].'">
                                        <input type="hidden" name="job_id" value="'.$job_id.'">
                                        <input type="hidden" name="csrf_token" value="'.($_SESSION['csrf_token'] ?? '').'">
                                        <input type="hidden" name="status" value="Rejected">
                                        <button type="submit" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded-lg hover:bg-red-200">Reject</button>
                                    </form>';
                                    break;
                                    
                                case 'Screened':
                                    // Assign exam
                                    if ($hasExam) {
                                        echo '<form action="update-application-status" method="POST" class="inline">
                                            <input type="hidden" name="app_id" value="'.$app['app_id'].'">
                                            <input type="hidden" name="job_id" value="'.$job_id.'">
                                            <input type="hidden" name="csrf_token" value="'.($_SESSION['csrf_token'] ?? '').'">
                                            <input type="hidden" name="status" value="Exam Assigned">
                                            <button type="submit" class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">Assign Exam</button>
                                        </form>';
                                    }
                                    // Move back to Applied
                                    echo '<form action="update-application-status" method="POST" class="inline">
                                        <input type="hidden" name="app_id" value="'.$app['app_id'].'">
                                        <input type="hidden" name="job_id" value="'.$job_id.'">
                                        <input type="hidden" name="csrf_token" value="'.($_SESSION['csrf_token'] ?? '').'">
                                        <input type="hidden" name="status" value="Applied">
                                        <button type="submit" class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Reset</button>
                                    </form>';
                                    // Reject
                                    echo '<form action="update-application-status" method="POST" class="inline">
                                        <input type="hidden" name="app_id" value="'.$app['app_id'].'">
                                        <input type="hidden" name="job_id" value="'.$job_id.'">
                                        <input type="hidden" name="csrf_token" value="'.($_SESSION['csrf_token'] ?? '').'">
                                        <input type="hidden" name="status" value="Rejected">
                                        <button type="submit" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded-lg hover:bg-red-200">Reject</button>
                                    </form>';
                                    break;
                                    
                                case 'Exam Assigned':
                                    // View Exam (if completed)
                                    if (!empty($app['exam_score'])) {
                                        echo '<a href="review-exam?id='.$app['app_id'].'" class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-lg hover:bg-green-200">View Exam</a>';
                                    }
                                    // Reset to Applied (clears exam data)
                                    echo '<form action="update-application-status" method="POST" class="inline">
                                        <input type="hidden" name="app_id" value="'.$app['app_id'].'">
                                        <input type="hidden" name="job_id" value="'.$job_id.'">
                                        <input type="hidden" name="csrf_token" value="'.($_SESSION['csrf_token'] ?? '').'">
                                        <input type="hidden" name="action" value="reset">
                                        <button type="submit" class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Reset</button>
                                    </form>';
                                    // Reject
                                    echo '<form action="update-application-status" method="POST" class="inline">
                                        <input type="hidden" name="app_id" value="'.$app['app_id'].'">
                                        <input type="hidden" name="job_id" value="'.$job_id.'">
                                        <input type="hidden" name="csrf_token" value="'.($_SESSION['csrf_token'] ?? '').'">
                                        <input type="hidden" name="status" value="Rejected">
                                        <button type="submit" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded-lg hover:bg-red-200">Reject</button>
                                    </form>';
                                    break;
                                    
                                case 'Exam Completed':
                                    // Pass manually
                                    echo '<form action="update-application-status" method="POST" class="inline">
                                        <input type="hidden" name="app_id" value="'.$app['app_id'].'">
                                        <input type="hidden" name="job_id" value="'.$job_id.'">
                                        <input type="hidden" name="csrf_token" value="'.($_SESSION['csrf_token'] ?? '').'">
                                        <input type="hidden" name="status" value="Passed">
                                        <button type="submit" class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-lg hover:bg-green-200">Pass</button>
                                    </form>';
                                    // Fail manually
                                    echo '<form action="update-application-status" method="POST" class="inline">
                                        <input type="hidden" name="app_id" value="'.$app['app_id'].'">
                                        <input type="hidden" name="job_id" value="'.$job_id.'">
                                        <input type="hidden" name="csrf_token" value="'.($_SESSION['csrf_token'] ?? '').'">
                                        <input type="hidden" name="status" value="Failed">
                                        <button type="submit" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded-lg hover:bg-red-200">Fail</button>
                                    </form>';
                                    // View Exam
                                    echo '<a href="review-exam?id='.$app['app_id'].'" class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">View</a>';
                                    break;
                                    
                                case 'Passed':
                                    // Move to Interviewing
                                    echo '<form action="update-application-status" method="POST" class="inline">
                                        <input type="hidden" name="app_id" value="'.$app['app_id'].'">
                                        <input type="hidden" name="job_id" value="'.$job_id.'">
                                        <input type="hidden" name="csrf_token" value="'.($_SESSION['csrf_token'] ?? '').'">
                                        <input type="hidden" name="status" value="Interviewing">
                                        <button type="submit" class="px-2 py-1 text-xs bg-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-200">Interview</button>
                                    </form>';
                                    // Reject
                                    echo '<form action="update-application-status" method="POST" class="inline">
                                        <input type="hidden" name="app_id" value="'.$app['app_id'].'">
                                        <input type="hidden" name="job_id" value="'.$job_id.'">
                                        <input type="hidden" name="csrf_token" value="'.($_SESSION['csrf_token'] ?? '').'">
                                        <input type="hidden" name="status" value="Rejected">
                                        <button type="submit" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded-lg hover:bg-red-200">Reject</button>
                                    </form>';
                                    break;
                                    
                                case 'Failed':
                                    // Retake Exam - use assign-exam route
                                    if ($hasExam) {
                                        echo '<a href="assign-exam?app_id='.$app['app_id'].'" class="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200">Retake</a>';
                                    }
                                    // Reconsider - reset to Applied (clears exam data)
                                    echo '<form action="update-application-status" method="POST" class="inline">
                                        <input type="hidden" name="app_id" value="'.$app['app_id'].'">
                                        <input type="hidden" name="job_id" value="'.$job_id.'">
                                        <input type="hidden" name="csrf_token" value="'.($_SESSION['csrf_token'] ?? '').'">
                                        <input type="hidden" name="action" value="reset">
                                        <button type="submit" class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Reset</button>
                                    </form>';
                                    break;
                                    
                                case 'Interviewing':
                                    // Offer position
                                    echo '<form action="update-application-status" method="POST" class="inline">
                                        <input type="hidden" name="app_id" value="'.$app['app_id'].'">
                                        <input type="hidden" name="job_id" value="'.$job_id.'">
                                        <input type="hidden" name="csrf_token" value="'.($_SESSION['csrf_token'] ?? '').'">
                                        <input type="hidden" name="status" value="Offered">
                                        <button type="submit" class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-lg hover:bg-green-200">Offer</button>
                                    </form>';
                                    // Reject
                                    echo '<form action="update-application-status" method="POST" class="inline">
                                        <input type="hidden" name="app_id" value="'.$app['app_id'].'">
                                        <input type="hidden" name="job_id" value="'.$job_id.'">
                                        <input type="hidden" name="csrf_token" value="'.($_SESSION['csrf_token'] ?? '').'">
                                        <input type="hidden" name="status" value="Rejected">
                                        <button type="submit" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded-lg hover:bg-red-200">Reject</button>
                                    </form>';
                                    break;
                                    
                                case 'Offered':
                                    // Reject (withdraw offer)
                                    echo '<form action="update-application-status" method="POST" class="inline">
                                        <input type="hidden" name="app_id" value="'.$app['app_id'].'">
                                        <input type="hidden" name="job_id" value="'.$job_id.'">
                                        <input type="hidden" name="csrf_token" value="'.($_SESSION['csrf_token'] ?? '').'">
                                        <input type="hidden" name="status" value="Rejected">
                                        <button type="submit" class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded-lg hover:bg-red-200">Withdraw</button>
                                    </form>';
                                    break;
                                    
                                case 'Rejected':
                                    // Reconsider - reset to Applied (clears exam data)
                                    echo '<form action="update-application-status" method="POST" class="inline">
                                        <input type="hidden" name="app_id" value="'.$app['app_id'].'">
                                        <input type="hidden" name="job_id" value="'.$job_id.'">
                                        <input type="hidden" name="csrf_token" value="'.($_SESSION['csrf_token'] ?? '').'">
                                        <input type="hidden" name="action" value="reset">
                                        <button type="submit" class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Reconsider</button>
                                    </form>';
                                    break;
                                    
                                default:
                                    echo '<span class="text-xs text-gray-400">'.$status.'</span>';
                            }
                            ?>
                            
                            <a href="view-resume?path=<?php echo $app['resume_path']; ?>" target="_blank" 
                            class="p-2 text-gray-400 hover:text-black transition-colors" title="View Resume">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div id="aiTooltipPortal" class="fixed hidden z-50 max-w-xs p-4 bg-white border border-gray-200 rounded-2xl shadow-2xl text-sm text-gray-700"></div>

    <!-- Notes Modal -->
    <div id="notesModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Recruiter Notes</h3>
            <form id="notesForm" action="update-notes" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <input type="hidden" name="app_id" id="notesAppId">
                <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">
                <textarea name="notes" id="notesTextarea" rows="4" class="w-full px-3 py-2 border border-gray-200 rounded-lg" placeholder="Add internal notes..."></textarea>
                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" onclick="closeNotesModal()" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-black text-white rounded-lg hover:bg-gray-800">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleTooltip(event, appId) {
    event.stopPropagation();
    const tooltip = document.getElementById('tooltip-' + appId);
    // Hide all other tooltips
    document.querySelectorAll('[id^="tooltip-"]').forEach(t => {
        if (t !== tooltip) t.classList.add('hidden');
    });
    tooltip.classList.toggle('hidden');
}

// Close tooltips when clicking outside
document.addEventListener('click', function(e) {
    const button = e.target.closest('button[onclick^="toggleTooltip"]');
    const tooltip = e.target.closest('[id^="tooltip-"]');
    if (!button && !tooltip) {
        document.querySelectorAll('[id^="tooltip-"]').forEach(t => t.classList.add('hidden'));
    }
});

// Bulk select functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.bulk-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
});

document.getElementById('selectAllHeader').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.bulk-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    document.getElementById('selectAll').checked = this.checked;
});

// Notes modal functionality
function openNotesModal(appId, notes) {
    document.getElementById('notesAppId').value = appId;
    document.getElementById('notesTextarea').value = notes;
    document.getElementById('notesModal').classList.remove('hidden');
}

function closeNotesModal() {
    document.getElementById('notesModal').classList.add('hidden');
}

function showAiTooltip(button, summary) {
    const portal = document.getElementById('aiTooltipPortal');
    portal.innerHTML = `<p class="text-sm leading-relaxed">${summary}</p>`;
    const rect = button.getBoundingClientRect();
    const width = 320;
    let left = rect.right + 12;
    let top = rect.top;

    if (left + width > window.innerWidth - 20) {
        left = rect.left - width - 12;
    }
    if (left < 20) {
        left = 20;
    }
    if (top + 120 > window.innerHeight - 20) {
        top = window.innerHeight - 140;
    }

    portal.style.left = `${left}px`;
    portal.style.top = `${top}px`;
    portal.style.width = `${width}px`;
    portal.classList.remove('hidden');
}

function hideAiTooltip() {
    const portal = document.getElementById('aiTooltipPortal');
    portal.classList.add('hidden');
}
</script>

<?php include __DIR__ . '/../partials/footer.php'?>