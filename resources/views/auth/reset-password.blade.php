<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set New Password | TechAccessories</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        
        @keyframes pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }
        .animate-pop { animation: pop 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
        .transition-colors-transform { transition: color 0.3s ease, transform 0.3s ease; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-xl shadow-sm border border-gray-100 p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">TechAccessories</h1>
            <h2 class="text-xl font-semibold text-gray-800">Create New Password</h2>
            <p class="text-sm text-gray-500 mt-2">Secure your account with a strong password.</p>
        </div>

        <div id="alert-box" class="hidden mb-6 p-4 rounded-lg text-sm"></div>

        <form id="reset-form" class="space-y-5">
            <!-- New Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <div class="relative">
                    <input type="password" id="password" required
                        class="w-full pl-4 pr-10 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50 focus:bg-white"
                        placeholder="••••••••">
                    <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg id="password-eye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                </div>
                <!-- Validation Checklist -->
                <ul id="password-reqs" class="text-xs text-gray-500 mt-3 space-y-1.5">
                    <li id="req-length" class="flex items-center transition-colors-transform"><svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" fill="currentColor"></circle></svg> At least 8 characters</li>
                    <li id="req-upper" class="flex items-center transition-colors-transform"><svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" fill="currentColor"></circle></svg> 1 uppercase letter</li>
                    <li id="req-lower" class="flex items-center transition-colors-transform"><svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" fill="currentColor"></circle></svg> 1 lowercase letter</li>
                    <li id="req-number" class="flex items-center transition-colors-transform"><svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" fill="currentColor"></circle></svg> 1 number</li>
                    <li id="req-special" class="flex items-center transition-colors-transform"><svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" fill="currentColor"></circle></svg> 1 special character</li>
                </ul>
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <div class="relative">
                    <input type="password" id="password_confirmation" required
                        class="w-full pl-4 pr-10 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50 focus:bg-white"
                        placeholder="••••••••">
                    <button type="button" onclick="togglePassword('password_confirmation')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg id="password_confirmation-eye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    </button>
                </div>
                <p id="match-error" class="hidden text-xs text-red-600 mt-1 transition-all">Passwords do not match.</p>
            </div>

            <button type="submit" id="submit-btn" disabled
                class="w-full bg-gray-900 hover:bg-gray-800 disabled:bg-gray-300 disabled:text-gray-500 disabled:cursor-not-allowed text-white font-medium py-2.5 rounded-lg transition-colors flex justify-center items-center mt-6">
                <span>Update Password</span>
            </button>
        </form>
    </div>

    <script>
        const token = "{{ $token ?? '' }}";
        const email = "{{ $email ?? '' }}";

        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirmation');
        const submitBtn = document.getElementById('submit-btn');
        const matchError = document.getElementById('match-error');

        const reqs = {
            length: document.getElementById('req-length'),
            upper: document.getElementById('req-upper'),
            lower: document.getElementById('req-lower'),
            number: document.getElementById('req-number'),
            special: document.getElementById('req-special')
        };

        const iconEye = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
        const iconEyeSlash = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path>';
        const iconCheck = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>';
        const iconDot = '<circle cx="12" cy="12" r="3" fill="currentColor"></circle>';

        window.togglePassword = function(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(inputId + '-eye');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = iconEyeSlash;
            } else {
                input.type = 'password';
                icon.innerHTML = iconEye;
            }
        };

        function validatePassword() {
            const pw = passwordInput.value;
            const conf = confirmInput.value;

            const rules = {
                length: pw.length >= 8,
                upper: /[A-Z]/.test(pw),
                lower: /[a-z]/.test(pw),
                number: /[0-9]/.test(pw),
                special: /[^A-Za-z0-9]/.test(pw)
            };

            for (const [key, isValid] of Object.entries(rules)) {
                const li = reqs[key];
                const svg = li.querySelector('svg');
                
                if (isValid && !li.dataset.valid) {
                    li.classList.replace('text-gray-500', 'text-green-600');
                    svg.innerHTML = iconCheck;
                    svg.classList.add('animate-pop');
                    li.dataset.valid = 'true';
                } else if (!isValid && li.dataset.valid) {
                    li.classList.replace('text-green-600', 'text-gray-500');
                    svg.innerHTML = iconDot;
                    svg.classList.remove('animate-pop');
                    delete li.dataset.valid;
                }
            }

            const isMatch = pw === conf && conf.length > 0;
            if (conf.length > 0 && !isMatch) {
                matchError.classList.remove('hidden');
                confirmInput.classList.add('border-red-500', 'focus:ring-red-500');
            } else {
                matchError.classList.add('hidden');
                confirmInput.classList.remove('border-red-500', 'focus:ring-red-500');
            }

            const allRulesMet = Object.values(rules).every(Boolean);
            submitBtn.disabled = !(allRulesMet && isMatch);
        }

        passwordInput.addEventListener('input', validatePassword);
        confirmInput.addEventListener('input', validatePassword);

        document.getElementById('reset-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const alertBox = document.getElementById('alert-box');
            
            if (submitBtn.disabled) return;

            if (!token || !email) {
                alertBox.className = 'mb-6 p-4 rounded-lg text-sm bg-red-50 text-red-800 border border-red-200';
                alertBox.textContent = 'Invalid or missing reset token in URL.';
                alertBox.classList.remove('hidden');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            alertBox.classList.add('hidden');

            try {
                const response = await fetch('/api/reset-password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                    body: JSON.stringify({ email, token, password: passwordInput.value, password_confirmation: confirmInput.value })
                });

                const data = await response.json();

                if (response.ok || response.status === 200) {
                    document.getElementById('reset-form').classList.add('hidden');
                    alertBox.className = 'mb-6 p-4 rounded-lg text-sm bg-green-50 text-green-800 border border-green-200 text-center';
                    alertBox.innerHTML = '<svg class="w-8 h-8 text-green-500 mx-auto mb-2 animate-pop" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <p class="font-medium text-green-900 mb-1">Password Reset Successful</p><p class="text-green-700">You can now use your new password to log in.</p><a href="#" class="mt-4 inline-block text-blue-600 font-medium hover:underline">Go to Login</a>';
                    alertBox.classList.remove('hidden');
                } else {
                    throw new Error(data.message || 'The token has expired or is invalid.');
                }
            } catch (err) {
                let msg = err.message;
                if (msg === 'passwords.token') msg = 'This password reset token is invalid or has expired.';
                alertBox.className = 'mb-6 p-4 rounded-lg text-sm bg-red-50 text-red-800 border border-red-200';
                alertBox.textContent = msg;
                alertBox.classList.remove('hidden');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span>Update Password</span>';
            }
        });
    </script>
</body>
</html>
