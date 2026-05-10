function handleLogout() {
    if(confirm("Are you sure you want to log out?")) {
        window.location.href = "logout"; 
    }
}

function closeJob(jobId) {
    if (confirm("Are you sure you want to close this job? It will no longer accept new applicants.")) {
        // Use BACKTICKS here so ${jobId} is evaluated
        window.location.href = `process-job?action=close&id=${jobId}`;
    }
}

function confirmAssignExam(appId) {
    if (confirm("Assign the technical exam to this candidate?")) {
        window.location.href = `assign-exam?app_id=${appId}`;
    }
}

function moveToInterview(appId) {
    if (confirm("Move this candidate to the interview stage?")) {
        window.location.href = `process-application?action=interview&app_id=${appId}`;
    }
}

function rejectCandidate(appId) {
    if (confirm("Are you sure you want to reject this applicant? This action cannot be undone.")) {
        window.location.href = `process-application?action=reject&app_id=${appId}`;
    }
}