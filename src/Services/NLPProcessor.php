<?php
// No namespace as requested

class NLPProcessor {
    private $pythonPath;

    public function __construct() {
        // Detect OS: Windows local vs DigitalOcean Linux
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->pythonPath = '"C:\\Users\\Dustin\\AppData\\Local\\Programs\\Python\\Python313\\python.exe"';
        } else {
            // On DigitalOcean, we use the system python3
            $this->pythonPath = 'python3';
        }
    }

    public function calculateMatchScore($resumePath, $jobDescription) {
        // 1. Resolve Script Path: Goes up from 'Services' then into 'AI'
        $scriptPath = realpath(__DIR__ . '/../AI/nlp_engine.py');
        
        if (!$scriptPath || !file_exists($scriptPath)) {
            error_log("AI Error: nlp_engine.py not found at expected location.");
            return ['score' => 0, 'summary' => 'AI Engine script missing.'];
        }

        // 2. Resolve Resume Path: Ensure Python gets an absolute path
        // If $resumePath is relative (e.g. 'uploads/file.pdf'), realpath makes it absolute.
        $absoluteResumePath = realpath($resumePath);
        if (!$absoluteResumePath) {
            $absoluteResumePath = $resumePath; // Fallback to raw path if realpath fails
        }

        $escapedPath = escapeshellarg($absoluteResumePath);
        $escapedDesc = escapeshellarg($jobDescription);

        // 3. Build Command (2>&1 redirects errors so we can see them in $output)
        $command = "{$this->pythonPath} \"$scriptPath\" $escapedPath $escapedDesc 2>&1";

        $output = [];
        $return_var = 0;

        // 4. Execute
        exec($command, $output, $return_var);

        if ($return_var !== 0) {
            error_log("AI Exec Failed: " . implode("\n", $output));
            return ['score' => 0, 'summary' => 'AI execution error on server.'];
        }

        // 5. Extract JSON from Output 
        // We loop because Python might print "FutureWarnings" before the JSON
        $jsonResult = null;
        foreach ($output as $line) {
            $trimmedLine = trim($line);
            // Look for a line that starts with { and ends with }
            if (strpos($trimmedLine, '{') === 0) {
                $decoded = json_decode($trimmedLine, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['score'])) {
                    $jsonResult = $decoded;
                    break;
                }
            }
        }

        if (!$jsonResult) {
            error_log("AI Parse Error. Raw Output: " . implode("\n", $output));
            return ['score' => 0, 'summary' => 'Could not parse AI results.'];
        }

        return [
            'score'   => $jsonResult['score'] ?? 0,
            'summary' => $jsonResult['summary'] ?? 'Summary unavailable.'
        ];
    }
}