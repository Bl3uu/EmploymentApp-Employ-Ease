<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Authenticator | EmployEase</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<main class="min-h-screen bg-slate-50 py-16">
    <div class="mx-auto max-w-lg px-4">
        <div class="bg-white border border-gray-200 shadow-lg rounded-[2rem] overflow-hidden">
            <div class="px-8 py-10 text-center bg-gray-50">
                <h1 class="text-3xl font-bold text-gray-900">Set Up App 2FA</h1>
                <p class="mt-3 text-sm text-gray-500">Scan the QR code below with Google Authenticator or Authy.</p>
            </div>

            <div class="px-8 py-10 flex flex-col items-center">
                <!-- 1. UPDATED: Changed img tag to a div for JS rendering -->
                <div class="bg-white p-4 border rounded-2xl shadow-sm mb-6">
                    <div id="qrcode"></div> <!-- QR code will be generated here -->
                </div>

                <div class="w-full space-y-6">
                    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 text-xs text-blue-800">
                        <strong>Manual Entry Key:</strong><br>
                        <code class="font-mono text-sm"><?php echo $qrData['secret']; ?></code>
                    </div>

                    <form action="verify-otp-submit" method="POST" class="space-y-6">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Enter the 6-digit code from the app</label>
                            <input type="text" name="otp_code" required maxlength="6" inputmode="numeric" pattern="[0-9]{6}"
                                class="w-full rounded-3xl border border-gray-200 bg-gray-50 px-5 py-4 text-center text-2xl tracking-[0.35em] text-gray-900 focus:border-black focus:ring-2 focus:ring-black/10 outline-none"
                                placeholder="000000" autocomplete="one-time-code">
                        </div>

                        <button type="submit" class="w-full rounded-3xl bg-black px-6 py-4 text-sm font-semibold text-white hover:bg-gray-800 transition shadow-lg">
                            Verify and Link App
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- 2. ADDED: QRCode.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<!-- 3. ADDED: Script to generate the QR Code -->
<script>
    // Get the otpauth string from PHP
    const otpauth = "<?php echo $qrData['otpauth']; ?>";

    // Generate the QR Code
    new QRCode(document.getElementById("qrcode"), {
        text: otpauth,
        width: 192, // Matching your original w-48 (48 * 4 = 192px)
        height: 192,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.M
    });
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>