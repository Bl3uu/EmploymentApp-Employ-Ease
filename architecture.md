classDiagram
    %% ===============================
    %% Employment_APP (current code)
    %% ===============================


    class Database {
        +getConnection() PDO
    }

    %% -------------------------------
    %% Routing / Gatekeeper
    %% -------------------------------
    class Router {
        <<public/index.php>>
        +routes[]
        +sessionGuard()
        +csrf_token()
    }

    %% -------------------------------
    %% Controllers
    %% -------------------------------
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
        +manageExams()
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

    %% -------------------------------
    %% Exam entry/orchestration (legacy + views)
    %% -------------------------------
    class ExamController {
        +showExam(job_id)
        +submitExam()
    }

    %% -------------------------------
    %% Models
    %% -------------------------------
    class UserAuth {
        +register(...)
        +login(...)
        +generate2FA(...)
        +verify2FA(...)
        +generateTOTPSecret(...)
        +verifyTOTP(...)
        +logout()
    }

    class Job {
        +getActiveJobsFiltered(...)
        +getUniqueLocationsForActiveJobs()
        +getUniqueTypesForActiveJobs()
        +getJobById()
        +getJobsByRecruitor(...)
        +createJob()
        +updateJob()
        +closeJob()
    }

    class Application {
        +applyForJob(user_id, job_id, resume_path)
        +hasApplied(user_id, job_id)
        +getApplicationId(user_id, job_id)
        +getCandidateApplications(user_id)
        +getCandidateApplications(user_id)
        +updateStatus(id, status, user_id)
        +getById(id)
        +assignExam(application_id)
        +clearExamData(application_id)
        +getApplicantsByJob(job_id)
        +getExamReviewSummary(application_id)
        +getExamAnswers(application_id)
        +getRecruiterReportData(recruiter_id)
        +getCandidateForReport(application_id)
        +updateNotes(application_id, notes)
    }

    class ExamModel {
        +create(job_id, title, duration, passing_mark)
        +getByJobId(job_id)
        +getByApplicationId(application_id)
        +getQuestions(exam_id)
        +addQuestion(exam_id, ...)
        +updateSettings(exam_id, ...)
        +deleteExam(exam_id)
        +deleteQuestion(question_id, exam_id)
        +getAllExamsWithJobs()
        +getAvailableJobsForExam(...)
        +getExamWithJob(exam_id)
        +getAllQuestions(exam_id)
        +getExamResultsWithUser(exam_id)
    }

    class AuditModel {
        +logAction(user_id, action, description, application_id, ip_address)
        +logStatusChange(...)
        +logProctorViolation(...)
        +getLogsByApplication(application_id)
        +getLatestInterviewConfirmation(application_id)
        +hasExamViolations(application_id)
    }

    class Notification {
        +create(user_id, title, message)
        +getForUser(user_id, limit)
        +markAllRead(user_id)
    }

    class UserProfile {
        +getUserById(user_id)
        +updateBio(user_id, bio)
        +getUserSkillIds(user_id)
        +getUserSkills(user_id)
        +saveUserSkills(user_id, skill_ids)
    }

    %% -------------------------------
    %% Services / proctoring / utilities
    %% -------------------------------
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

    class NotificationService {
        +sendExamAssignmentEmail(application_id)
        +sendStatusChangeEmail(application_id, status)
    }

    %% -------------------------------
    %% Endpoints / handlers
    %% -------------------------------
    class LogViolationAPI {
        <<API/log_violation.php>>
        +POST application_id, type
    }

    class SubmitExamHandler {
        <<src/Handler/submit_exam.php>>
        +POST app_id, disqualified, q{question_id}
    }

    class ViewResumeHandler {
        <<src/Handler/view_resume.php>>
        +GET path (pdf)
    }

    %% -------------------------------
    %% Proctor python service
    %% -------------------------------
    class ProctorPython {
        <<FastAPI /analyze-frame>>
        +POST image (base64)
        +returns status: normal|missing|multiple
    }

    %% -------------------------------
    %% Views (rendered by controllers)
    %% -------------------------------
    class CandidateViews {
        <<templates/candidate>>
        +portal.php
        +apply.php
        +profile.php
        +progress.php
        +take_exam_form.php
        +view_results.php
        +notifications.php
    }

    class RecruiterViews {
        <<templates/recruiter>>
        +dashboard.php
        +post_job.php
        +manage_exams.php
        +edit_exam_questions.php
        +edit_exam_settings.php
        +applicants.php
        +review_exam.php
        +view_report.php
    }

    class AdminViews {
        <<templates/admin>>
        +dashboard.php
        +user_management.php
        +manage_jobs.php
        +manage_exams.php
    }

    %% ===============================
    %% Relationships
    %% ===============================
    Database <.. Router

    Router --> AuthController : routes /login-submit /verify-otp-submit /setup-2fa
    Router --> CandidateController : routes /portal /apply /profile /progress /confirm-interview
    Router --> ApplicationController : route /submit-application
    Router --> RecruiterController : routes recruiter actions + exam setup
    Router --> AdminController : routes /admin-*

    AuthController --> UserAuth : uses 2FA/TOTP + sessions
    CandidateController --> Job : browse jobs + get job details
    CandidateController --> Application : candidate application lists/status
    CandidateController --> UserProfile : manage profile & skills
    CandidateController --> AuditModel : interview confirmation audit lookup
    CandidateController --> Notification : candidate notifications

    RecruiterController --> Job : create/update/close jobs
    RecruiterController --> Application : update statuses, notes, report feeds
    RecruiterController --> ExamModel : create/update/delete exams/questions/settings
    RecruiterController --> ExamModel : exam assignment prerequisite checks
    RecruiterController --> AuditModel : write status changes
    RecruiterController --> NotificationService : email status change + exam assignment
    RecruiterController --> Notification : create in-app notifications

    ExamHandler --> ProctorEngine : proctor integration for exam attempts (calculation + flags)
    SubmitExamHandler --> ExamHandler : scoring + proctor-aware result labeling
    SubmitExamHandler --> AuditModel : (indirect) violations are logged via API during attempt

    LogViolationAPI --> ProctorEngine : persist audit_logs EXAM_VIOLATION
    LogViolationAPI --> AuditModel : (via DB audit_logs)

    ProctorPython ..> ProctorEngine : informs violations (via reportViolation() => API)

    NotificationService --> Notification : build & deliver status notifications

    ViewResumeHandler --> Database : serves resume PDF with ownership checks (by path)

    CandidateViews <.. CandidateController
    RecruiterViews <.. RecruiterController
    AdminViews <.. AdminController

