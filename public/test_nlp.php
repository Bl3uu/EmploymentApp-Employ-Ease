<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$pythonPath = "python"; // Start with 'python', we will change this if it fails
$scriptPath = realpath("../src/AI/test_bridge.py");
$testArgs = escapeshellarg("test_resume.pdf") . " " . escapeshellarg("Job Desc");

// The '2>&1' is MAGIC—it redirects Python errors so PHP can see them
$command = "$pythonPath \"$scriptPath\" $testArgs 2>&1";

echo "<h1>Debugging NLP Bridge</h1>";
echo "<b>Attempting Command:</b> <br><code>$command</code><hr>";

exec($command, $output, $return_val);

echo "<b>Return Code:</b> " . $return_val . " (0 means success, 127 means 'command not found')<br>";
echo "<b>Command Output:</b> <pre>";
print_r($output);
echo "</pre>";

if ($return_val !== 0) {
    echo "<p style='color:red;'><b>DIAGNOSIS:</b><br>";
    if ($return_val === 127) {
        echo "PHP cannot find 'python'. You MUST use the full path to python.exe (e.g., C:\\Python39\\python.exe).";
    } else {
        echo "Python found the file but crashed. Look at the Command Output above for the Python error.";
    }
    echo "</p>";
} else {
    echo "<p style='color:green;'>SUCCESS! Bridge is active.</p>";
}   