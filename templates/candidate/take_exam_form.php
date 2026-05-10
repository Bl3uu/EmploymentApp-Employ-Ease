<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($examData['title']); ?> - Secured Session</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Inject PHP variables into JS
        const EXAM_APP_ID = <?php echo json_encode($app_id); ?>;
        const TOTAL_SECONDS = <?php echo $examData['duration_min'] * 60; ?>;
    </script>
</head>
<body class="bg-gray-100 font-sans select-none" oncontextmenu="return false;">

    <div id="lockdownOverlay" class="fixed inset-0 bg-black/90 z-[9999] flex-col items-center justify-center hidden">
        <div class="text-center p-8 bg-white rounded-3xl max-w-md mx-4">
            <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Session Paused</h2>
            <p id="lockdownMessage" class="text-gray-600 mb-6">AI has detected a security violation. Please return to your seat to continue.</p>
            <span class="text-xs font-bold text-red-500 uppercase tracking-widest animate-pulse">Monitoring Active</span>
        </div>
    </div>

    <header class="bg-white shadow-sm border-b-2 border-black">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-xl font-bold uppercase tracking-tighter">AI Proctor Session</h1>
            <div class="text-sm font-medium text-gray-500">
                Candidate: <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto p-6 grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        <div class="lg:col-span-3 space-y-6">
            <form id="examForm" action="submit-exam" method="POST" class="space-y-6">
                <input type="hidden" name="app_id" value="<?php echo $app_id; ?>">
                <input type="hidden" name="disqualified" id="disqualifiedInput" value="0">
                
                <?php foreach ($examData['questions'] as $index => $q): ?>
                    <div class="bg-white rounded-2xl shadow-sm p-8 border border-gray-100">
                        <p class="text-xs font-bold text-blue-600 mb-2 uppercase">Question <?php echo $index + 1; ?></p>
                        <h3 class="text-lg font-semibold mb-6"><?php echo htmlspecialchars($q['question_text']); ?></h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach(['a', 'b', 'c', 'd'] as $opt): ?>
                                <label class="flex items-center p-4 border rounded-xl hover:bg-gray-50 cursor-pointer transition-all border-gray-200">
                                    <input type="radio" name="q<?php echo $q['id']; ?>" value="<?php echo strtoupper($opt); ?>" class="w-4 h-4 text-black border-gray-300 focus:ring-black" required>
                                    <span class="ml-3 text-sm text-gray-700">
                                        <b class="mr-2"><?php echo strtoupper($opt); ?>.</b> 
                                        <?php echo htmlspecialchars($q['option_'.$opt]); ?>
                                    </span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <button type="submit" id="submitExamBtn" class="w-full bg-black text-white py-4 rounded-2xl font-bold hover:opacity-90 transition-all shadow-lg">
                    Finalise and Submit Exam
                </button>
            </form>
        </div>

        <aside class="space-y-6">
            <div class="bg-gray-900 aspect-video rounded-xl flex items-center justify-center overflow-hidden relative">
                <video id="webcam" autoplay muted class="w-full h-full object-cover"></video>
                
                <canvas id="shutter" class="hidden"></canvas> 
                
                <span id="camPlaceholder" class="absolute text-gray-600 text-xs italic">Camera Initialising...</span>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 text-center">
                <h2 class="text-xs font-bold text-gray-400 uppercase mb-2">Remaining Time</h2>
                <p id="timer" class="text-4xl font-mono font-bold tracking-tight">00:00:00</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <h2 class="font-bold text-sm mb-4 uppercase text-gray-400">Security Status</h2>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-sm text-gray-600">Violations:</span>
                    <span id="warningCount" class="font-bold text-red-600">0 / 3</span>
                </div>
                <ul id="activityLog" class="text-[10px] space-y-1 text-gray-500 overflow-y-auto max-h-32">
                    <li class="text-green-600">• Secure environment established</li>
                </ul>
            </div>
        </aside>
    </div>

    <script>
        // Timer Logic
        let secondsLeft = TOTAL_SECONDS;
        const timerDisplay = document.getElementById('timer');

        function updateTimer() {
            // MODIFIED: If isTerminated OR the overlay is visible, don't count down
            const isLocked = !document.getElementById('lockdownOverlay').classList.contains('hidden');
            if (isTerminated || isLocked) return; 

            let h = Math.floor(secondsLeft / 3600);
            let m = Math.floor((secondsLeft % 3600) / 60);
            let s = secondsLeft % 60;
            timerDisplay.innerText = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
            
            if (secondsLeft <= 0) {
                alert("Time is up! Submitting exam automatically.");
                document.getElementById('examForm').submit();
            }
            secondsLeft--;
        }
        setInterval(updateTimer, 1000);

        // Security logic
        let violations = 0;
        let isTerminated = false; // THE KILL SWITCH

        function reportViolation(type) {
            // If we are already terminating, stop everything else
            if (isTerminated) return; 

            violations++;
            document.getElementById('warningCount').innerText = `${violations} / 3`;
            
            const log = document.getElementById('activityLog');
            log.insertAdjacentHTML('afterbegin', `<li class="text-red-500">• ${new Date().toLocaleTimeString()}: ${type} detected</li>`);

            // Send to backend
            fetch('log-violation', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `type=${encodeURIComponent(type)}&application_id=${EXAM_APP_ID}`
            });

            if (violations >= 3) {
                isTerminated = true; // LOCK THE GATE
                
                // Remove the listeners so they don't fire during the alert/submit process
                window.onblur = null;
                window.onbeforeunload = null; 

                alert("CRITICAL: Maximum violations reached. Exam session terminated.");
                
                document.getElementById('disqualifiedInput').value = "1";
                document.getElementById('examForm').submit();
            }
        }

        // Event Listeners
        document.addEventListener('visibilitychange', () => {
            if (document.visibilityState === 'hidden') reportViolation('Tab Switch');
        });

        window.addEventListener('blur', () => reportViolation('Window Focus Lost'));

        // Prevent Copy-Paste
        document.addEventListener('copy', (e) => { e.preventDefault(); reportViolation('Copy Attempt'); });
        document.addEventListener('paste', (e) => { e.preventDefault(); reportViolation('Paste Attempt'); });   
        // Prevent accidental refresh/close

        // Remove the warning when the form is actually submitted
        document.getElementById('examForm').onsubmit = function() {
            window.onbeforeunload = null;
            const submitBtn = document.getElementById('submitExamBtn');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
            submitBtn.textContent = "Submitting...";
        }; // Added missing closing brace and semicolon here

        // Prevent accidental refresh/close
        window.onbeforeunload = function() {
            return "Are you sure you want to leave? Your progress will be lost and this may be flagged.";
        };

        // --- AI Proctoring Integration with Grace Period ---
        const video = document.getElementById('webcam');
        const canvas = document.getElementById('shutter');
        const context = canvas.getContext('2d');
        const placeholder = document.getElementById('camPlaceholder');

        // New state variables to prevent flickering and false positives
        let consecutiveMisses = 0;
        const MISS_THRESHOLD = 3; // Must miss 3 times in a row (9 seconds) to trigger

        async function startProctoring() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = stream;
                placeholder.style.display = 'none';

                setInterval(async () => {
                    if (isTerminated) return;

                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                    context.drawImage(video, 0, 0, canvas.width, canvas.height);
                    const imageData = canvas.toDataURL('image/jpeg', 0.5);

                    // Determine the API location based on where the site is running
                    const isLocal = window.location.hostname === "localhost" || window.location.hostname === "127.0.0.1";
                    const API_URL = isLocal 
                        ? "http://127.0.0.1:8000/analyze-frame" 
                        : "/analyze-frame"; // On server, use relative path for Nginx

                    try {
                        const response = await fetch(API_URL, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ image: imageData })
                        });

                        const result = await response.json();
                        const overlay = document.getElementById('lockdownOverlay');
                        const msg = document.getElementById('lockdownMessage');

                        // Explicitly handle each status for better state management
                        if (result.status === "normal") {
                            // Immediate unlock upon first "normal" sighting
                            consecutiveMisses = 0; 
                            if (!overlay.classList.contains('hidden')) {
                                overlay.classList.add('hidden');
                                overlay.classList.remove('flex');
                                console.log("AI Status: Normal - Session Resumed");
                            }
                        } 
                        else if (result.status === "missing" || result.status === "multiple") {
                            consecutiveMisses++;
                            console.log(`AI Status: ${result.status} - Consecutive Count: ${consecutiveMisses}`);

                            if (consecutiveMisses >= MISS_THRESHOLD) {
                                overlay.classList.remove('hidden');
                                overlay.classList.add('flex');
                                
                                msg.innerText = result.status === "missing" 
                                    ? "Face not detected. Please ensure you are visible to the camera." 
                                    : "Multiple people detected. Only the candidate should be visible.";

                                // Only log violation to DB once per lockdown session
                                if (consecutiveMisses === MISS_THRESHOLD) {
                                    reportViolation(result.status === "missing" ? "Face Missing" : "Multiple Faces");
                                }
                            }
                        } else {
                            // FACE IS NORMAL: Reset everything
                            consecutiveMisses = 0;
                            overlay.classList.add('hidden');
                            overlay.classList.remove('flex');
                        }

                    } catch (err) {
                        console.warn("AI Service offline.");
                    }
                }, 3000);

            } catch (err) {
                console.error("Webcam access denied:", err);
                reportViolation("Camera Access Denied");
            }
        }

        // Initialise the camera when the page loads
        startProctoring();
    </script>
</body>
</html>