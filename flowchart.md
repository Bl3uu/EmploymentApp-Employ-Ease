flowchart TD
    Start([Start]) --> Session{Session + Role Guard}

    Session -->|role_id=1 Recruiter| RecruiterDash[GET /dashboard]
    Session -->|role_id=2 Candidate| CandidatePortal[GET /portal]
    Session -->|is_admin| AdminDash[GET /admin-dashboard]

    %% =====================
    %% Recruiter flow
    %% =====================
    RecruiterDash --> PostJob[POST /process-job (post-job or edit-job)]
    PostJob --> JobStore[DB: jobs CRUD]

    RecruiterDash --> ManageExams[GET /manage-exams]
    ManageExams --> CreateExam[POST /process-exam (create exam)]
    CreateExam --> ExamStore[DB: exams INSERT]

    CreateExam --> AddQuestions[POST /process-question]
    AddQuestions --> QuestionsStore[DB: questions INSERT]

    ManageExams --> UpdateSettings[POST /update-exam-settings]
    UpdateSettings --> SettingsStore[DB: exams UPDATE]

    RecruiterDash --> ViewApplicants[GET /view-applicants?job_id=...]
    ViewApplicants --> ApplicantsQuery[DB: applications + users + jobs + exams]

    ViewApplicants --> AssignExam[GET /assign-exam?app_id=...]
    AssignExam --> AssignStore[DB: applications.status='Exam Assigned']
    AssignExam --> AuditStatusLog[DB: audit_logs action='Status Update']
    AssignExam --> Notify[NotificationService: email + in-app notification]

    ViewApplicants --> UpdateStatus[POST /update-application-status]
    UpdateStatus --> BulkUpdate[POST /bulk-update-status]
    UpdateStatus --> StatusStore[DB: applications.status update]
    StatusStore --> AuditStatusLog2[DB: audit_logs 'Status Update']
    UpdateStatus --> NotesUpdate[POST /update-notes (recruiter_notes)]

    %% Recruiter review/report
    ViewApplicants --> ReviewExam[GET /review-exam?id=app_id]
    ReviewExam --> ReviewData[DB: exam_answers + exam_results + users]

    ReviewExam --> ViewReport[GET /view-report?id=app_id]
    ViewReport --> ReportData[DB: applications + exam_results + audit_logs]

    %% =====================
    %% Candidate flow
    %% =====================
    CandidatePortal --> BrowseJobs[GET /portal (filter/search)]
    BrowseJobs --> ViewJobsList[DB: jobs WHERE status='Active']

    BrowseJobs --> Apply[GET /apply?id=job_id]
    Apply --> SubmitApp[POST /submit-application]
    SubmitApp --> ApplyStore[DB: applications INSERT status='Applied']

    %% Exam gate: candidate can take only when status in allowed set
    SubmitApp --> Progress[GET /progress (status timeline)]
    Progress --> ExamAssignedGate{applications.status in {'Exam Assigned','Screened','Failed'}}

    ExamAssignedGate -->|YES| TakeExam[GET /take-exam?app_id=application_id (or take-exam form)]
    ExamAssignedGate -->|NO| Wait[Portal/Progress until recruiter assigns]

    TakeExam --> ExamJS[render take_exam_form.php (timer + listeners + webcam)]

    %% AI + JS proctoring
    ExamJS --> AIService{POST /analyze-frame (Python FastAPI)}
    AIService --> AIResult[status: normal|missing|multiple]

    AIResult --> LockdownOverlay{missing/multiple >= 3 in a row?}
    LockdownOverlay -->|YES| ViolationJS[reportViolation('Face Missing'|'Multiple Faces')]
    LockdownOverlay -->|NO| Continue[keep session]

    ExamJS --> TabBlur[visibilitychange hidden? / window blur?]
    TabBlur --> ViolationJS2[reportViolation('Tab Switch'|'Window Focus Lost')]

    ExamJS --> CopyPaste[copy/paste prevented]
    CopyPaste --> ViolationJS3[reportViolation('Copy Attempt'|'Paste Attempt')]

    ViolationJS --> LogViolationAPI[POST /log-violation (application_id,type)]
    LogViolationAPI --> AuditViolationStore[DB: audit_logs action='EXAM_VIOLATION']

    AuditViolationStore --> ViolationCounter{violations >= 3?}
    ViolationCounter -->|YES| Disqualify[set disqualified=1 + auto-submit]
    ViolationCounter -->|NO| ExamContinue[continue answering]

    %% Submission + scoring
    Disqualify --> SubmitExam[POST /submit-exam]
    ExamContinue --> SubmitExam

    SubmitExam --> SaveAnswers[DB: exam_answers]
    SaveAnswers --> SaveResults[DB: exam_results (score,status)]
    SaveResults --> UpdateAppStatus[DB: applications.status='Exam Completed']

    UpdateAppStatus --> RedirectPortal[portal?msg=exam_completed|voided]

    %% Candidate results page (used by template)
    RedirectPortal --> ViewResults[GET /candidate/view-results?exam_id=...]
    ViewResults --> End([End])

    %% =====================
    %% Admin flow
    %% =====================
    AdminDash --> AuditPDF[POST /generate-audit-report (PDF of audit_logs)]


