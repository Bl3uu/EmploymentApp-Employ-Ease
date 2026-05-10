<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - AI Recruitment</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="bg-gray-100 font-sans min-h-screen flex flex-col justify-center py-12">

    <div class="max-w-lg w-full mx-auto p-6">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold tracking-tight">Join the Network</h1>
            <p class="text-sm text-gray-500 mt-2">Start your career journey today</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-10">
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-xl text-sm mb-6">
                    <?php 
                        if($error == 'exists') echo "This email is already registered.";
                        elseif($error == 'registration_failed') echo "Registration failed. Please try again.";
                        else echo "Please check your details and try again.";
                    ?>
                </div>
            <?php endif; ?>
            <form action="signup-submit" method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-400 mb-2">First Name</label>
                        <input type="text" name="first_name" required 
                            class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-black transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Last Name</label>
                        <input type="text" name="last_name" required 
                            class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-black transition">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Email Address</label>
                    <input type="email" name="email" required 
                        class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-black transition">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Phone Number</label>
                    <input type="text" name="phone" required 
                        class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-black transition"
                        placeholder="+44 000 000 000">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-400 mb-2">Password</label>
                        <input type="password" name="password" required minlength="8"
                            class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-black transition">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-400 mb-2">I am a...</label>
                        <select name="role_id" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 focus:ring-2 focus:ring-black transition appearance-none">
                            <option value="2">Candidate</option>
                            <option value="1">Recruiter</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="w-full bg-black text-white py-4 rounded-xl font-bold hover:bg-gray-800 transition shadow-lg mt-4">
                    Create Account
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <p class="text-sm text-gray-500">
                    Already have an account? 
                    <a href="login" class="text-black font-bold hover:underline">Log in here</a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>