<?php
// We NO LONGER fetch data here. 
// The RecruiterController has already fetched $exams and $availableJobs for us.

// All we do now is include the actual HTML template
include __DIR__ . '/../../../templates/recruiter/manage_exams_form.php';
?>