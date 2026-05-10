<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Login | EmployEase</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<main class="min-h-screen bg-slate-50 py-16">
    <div class="mx-auto max-w-lg px-4">
        <div class="bg-white border border-gray-200 shadow-lg rounded-[2rem] overflow-hidden">
            <div class="px-8 py-10 text-center bg-gray-50">
                <h1 class="text-3xl font-bold text-gray-900">Verify Your Login</h1>
                <p class="mt-3 text-sm text-gray-500">
                    <?php if (($_SESSION['2fa_method'] ?? '') === 'totp'): ?>
                        Enter the 6-digit code from your <strong>Authenticator App</strong>.
                    <?php else: ?>
                        Enter the 6-digit code sent to your <strong>registered email</strong>.
                    <?php endif; ?>
                </p>
                <?php if (isset($error) && $error === 'invalid_code'): ?>
                    <div class="mt-5 rounded-2xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                        The code you entered is incorrect or expired. Please try again.
                    </div>
                <?php elseif (isset($error) && $error === 'email_failed'): ?>
                    <div class="mt-5 rounded-2xl bg-yellow-50 border border-yellow-200 px-4 py-3 text-sm text-yellow-700">
                        We could not send the verification email. Please check SMTP settings or try again later.
                    </div>
                <?php endif; ?>
            </div>

            <div class="px-8 py-10">
                <form action="verify-otp-submit" method="POST" class="space-y-6">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">6-Digit Verification Code</label>
                        <input type="text" name="otp_code" required maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
                            class="w-full rounded-3xl border border-gray-200 bg-gray-50 px-5 py-4 text-center text-2xl tracking-[0.35em] text-gray-900 focus:border-black focus:ring-2 focus:ring-black/10 outline-none"
                            placeholder="000000">
                        <p class="text-xs text-gray-400 mt-3">
                            <?php if (($_SESSION['2fa_method'] ?? '') === 'totp'): ?>
                                Open your Authenticator app (Google Authenticator, Authy, etc.) to get your code.
                            <?php else: ?>
                                Check your email inbox and spam folder. If email delivery fails, update SMTP settings.
                            <?php endif; ?>
                        </p>
                    </div>

                    <button type="submit" class="w-full rounded-3xl bg-black px-6 py-4 text-sm font-semibold text-white hover:bg-gray-800 transition shadow-lg">
                        Verify Account
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-gray-500">
                    <a href="login" class="font-semibold text-black hover:text-gray-700">Back to login</a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include __DIR__ . '/../partials/footer.php'; ?>