<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Automated Recruitment Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-gray-100 font-sans min-h-screen flex flex-col justify-center">

    <?php $error = $error ?? null; ?>
    <div class="max-w-md w-full mx-auto p-6">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold tracking-tight">Automated Recruitment Portal</h1>
            <p class="text-sm text-gray-500 mt-2">Sign in to manage your applications</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
            <?php if (!empty($error ?? null)): ?>
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-sm mb-6">
                    <?php 
                        if($error == 'invalid_credentials') echo "Invalid email or password.";
                        elseif($error == 'empty') echo "Please fill in all fields.";
                        elseif($error == 'email_failed') echo "Unable to send verification email. Check SMTP settings and try again.";
                        elseif($error == 'otp_failed') echo "Could not generate verification code. Please try again later.";
                        else echo "An error occurred. Please try again.";
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <div class="bg-green-50 border border-green-200 text-green-600 px-4 py-3 rounded-xl text-sm mb-6">
                    Account created successfully! Please sign in.
                </div>
            <?php endif; ?>
            <form action="login-submit" method="POST" class="space-y-5">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Email Address</label>
                    <input type="email" name="email" required 
                        class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-black transition"
                        placeholder="name@company.com">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Password</label>
                    <input type="password" name="password" required 
                        class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-black transition"
                        placeholder="••••••••">
                </div>

                <button type="submit" class="w-full bg-black text-white py-4 rounded-xl font-bold hover:bg-gray-800 transition shadow-lg mt-4">
                    Sign In
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <p class="text-sm text-gray-500">
                    New to the platform? 
                    <a href="signup" class="text-black font-bold hover:underline">Create an account</a>
                </p>
            </div>
        </div>
        
        <p class="text-center text-gray-400 text-[10px] mt-8 uppercase tracking-widest">
            © 2026 AI-Based Resume Screening System
        </p>
    </div>

</body>
</html>