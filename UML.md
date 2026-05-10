UML

## Class Diagram (current code)

```mermaid
classDiagram
    class Database {
        +getConnection() PDO
    }

    class AuthController {
        +handleLogin()
        +handleSignup()
        +handleVerify2FA()
        +getQRData()
        +handleLogout()
    }

    class CandidateController {
        +portal()
        +notifications()
        +notificationsJson()
        +apply()
        +profile()
        +updateProfile()
        +progress()
        +confirmInterviewSlot()
    }

    class ApplicationController {
        +handleSubmitApplication()
    }

    class RecruiterController {
        +processJob()
        +assignExam()
        +updateApplicationStatus()
        +bulkUpdateStatus()
        +updateNotes()
        +processExam()
        +processQuestion()
        +updateExamSettings()
        +deleteQuestion()
        +deleteExam()
    }

    class AdminController {
        +dashboard()
        +skillManagement()
        +handleSkillAction()
        +viewApplicationProfile()
        +userManagement()
        +auditFeed()
        +generateAuditReport()
        +manageJobs()
        +manageExams()
        +settings()
    }

    class ExamController {
        +showExam(job_id)
        +submitExam()
    }

    class UserAuth {
        +register(...)
        +generateTOTPSecret(...)
        +verifyTOTP(...)
        +verify2FA(...)
        +generate2FA(...)
        +logout()
    }

    class Job {
        +getActiveJobsFiltered(...)
        +getJobById(id)
        +createJob(...)
        +updateJob(...)
        +closeJob(id)
    }

    class Application {
        +applyForJob(user_id, job_id, resume_path)
        +hasApplied(user_id, job_id)
        +getApplicationId(user_id, job_id)
        +updateStatus(id, status, user_id)
        +getCandidateApplications(user_id)
        +getApplicantsByJob(job_id)
        +getCandidateForReport(application_id)
        +getExamReviewSummary(application_id)
        +getExamAnswers(application_id)
        +updateNotes(application_id, notes)
    }

    class ExamModel {
        +create(job_id, title, duration, passing_mark)
        +addQuestion(exam_id, ...)
        +updateSettings(exam_id, ...)
        +deleteExam(exam_id)
    }

    class AuditModel {
        +logAction(...)
        +logStatusChange(...)
        +logProctorViolation(...)
        +getLogsByApplication(application_id)
        +getLatestInterviewConfirmation(application_id)
    }

    class Notification {
        +create(user_id, title, message)
        +getForUser(user_id, limit)
        +markAllRead(user_id)
    }

    class ProctorEngine {
        +logViolation(application_id, user_id, type, details)
        +isCandidateFlagged(application_id)
        +getViolations(application_id)
    }

    class ExamHandler {
        +getExamData(job_id)
        +getExamByApplication(application_id)
        +calculateResult(user_id, exam_id, app_id, answers)
    }

    AuthController --> UserAuth : uses
    CandidateController --> Job
    CandidateController --> Application
    CandidateController --> Notification

    RecruiterController --> Job
    RecruiterController --> Application
    RecruiterController --> ExamModel
    RecruiterController --> AuditModel
    RecruiterController --> Notification

    ExamController --> ExamHandler
    ExamHandler --> ProctorEngine : uses for attempt/proctor flags

    ProctorEngine --> AuditModel : writes audit_logs
    Notification --> Notification

    Database <.. AuthController
    Database <.. CandidateController
    Database <.. RecruiterController
    Database <.. AdminController
    Database <.. ExamHandler
    Database <.. ProctorEngine
```

## Sequence Diagram - Candidate: Apply + Start Exam

```mermaid
sequenceDiagram
    participant Candidate
    participant Portal as CandidateController
    participant AppCtrl as ApplicationController
    participant Application as Application model
    participant Job as Job model

    Candidate->>Portal: GET /portal (search/filter)
    Portal->>Job: getActiveJobsFiltered(...)
    Job-->>Portal: jobs list
    Portal-->>Candidate: jobs + application statuses

    Candidate->>Portal: GET /apply?id=job_id
    Portal->>Job: getJobById(job_id)
    Job-->>Portal: job details
    Portal-->>Candidate: apply form

    Candidate->>AppCtrl: POST /submit-application (resume, job_id)
    AppCtrl->>Application: applyForJob(user_id, job_id, resume_path)
    Application-->>AppCtrl: application_id
    AppCtrl->>Application: (update AI score/summary if implemented)
    AppCtrl-->>Candidate: redirect to portal (Exam Assigned availability)

    Candidate->>Portal: Start Technical Exam (only when status=Exam Assigned)
    Candidate->>CandidateController: take-exam?app_id=application_id
    CandidateController-->>Candidate: take_exam_form.php (includes proctoring JS)
```

## Sequence Diagram - Candidate: Proctored Exam Attempt

```mermaid
sequenceDiagram
    participant Candidate
    participant ExamPage as take_exam_form.php (JS)
    participant API as /log-violation (log_violation.php)
    participant Proctor as ProctorEngine
    participant DB as MySQL (audit_logs)
    participant Submit as submit_exam.php
    participant ExamHandler as ExamHandler

    Candidate->>ExamPage: Page loads (camera + timer + listeners)
    ExamPage->>ExamPage: AI analysis loop (/analyze-frame)

    ExamPage-->>API: POST /log-violation (type, application_id)
    API->>Proctor: logViolation(application_id, user_id, type, details)
    Proctor->>DB: INSERT audit_logs (EXAM_VIOLATION)
    Proctor-->>API: flagged? (maxViolations=3)
    API-->>ExamPage: {success, flagged}

    ExamPage->>Submit: Submit exam (POST /submit-exam)
    Submit->>ExamHandler: getExamByApplication(app_id)
    Submit->>DB: INSERT exam_answers + INSERT exam_results + UPDATE applications.status
    Submit-->>Candidate: redirect portal (msg=exam_completed or voided)

    Note over ExamPage, Candidate: If JS local violations reach 3, disqualifiedInput=1 then auto-submit.
```

## Sequence Diagram - Recruiter: Exam Setup + Applicant Review

```mermaid
sequenceDiagram
    participant Recruiter
    participant RC as RecruiterController
    participant Job as Job model
    participant App as Application model
    participant EM as ExamModel
    participant Audit as AuditModel
    participant NotifSvc as NotificationService

    Recruiter->>RC: POST /process-job (create/update)
    RC->>Job: createJob/updateJob/closeJob
    Job-->>RC: ok
    RC-->>Recruiter: redirect dashboard

    Recruiter->>RC: GET /assign-exam?app_id=application_id
    RC->>App: getById(app_id), clearExamData() if reassignment
    RC->>App: assignExam(application_id)
    RC->>Audit: logStatusChange(...,'Exam Assigned')
    RC->>NotifSvc: sendExamAssignmentEmail(app_id)
    RC-->>Recruiter: redirect dashboard

    Recruiter->>RC: Manage exams (/manage-exams, /process-exam, /process-question, /update-exam-settings)
    RC->>EM: create/updateQuestions/settings/delete

    Recruiter->>RC: View applicants (/view-applicants?job_id=...)
    RC->>App: getApplicantsByJob(job_id)
    App-->>RC: applicant list + exam_scores/status
    RC-->>Recruiter: render applicants view

    Recruiter->>RC: Update status / notes (/update-application-status, /update-notes)
    RC->>App: updateStatus/updateNotes
    RC->>Audit: writes audit_logs via Application::updateStatus and ProctorEngine::logViolation
    RC-->>Recruiter: refresh view

    Recruiter->>RC: Review exam answers & Proctoring report (review-exam/view-report)
    RC->>App: getExamAnswers / getCandidateForReport
    App-->>RC: review data
    RC-->>Recruiter: render review/report templates
```

