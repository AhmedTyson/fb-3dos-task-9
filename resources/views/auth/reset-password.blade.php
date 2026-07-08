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
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full bg-white rounded-xl shadow-sm border border-gray-100 p-8">
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">TechAccessories</h1>
            <h2 class="text-xl font-semibold text-gray-800">Create New Password</h2>
            <p class="text-sm text-gray-500 mt-2">Your new password must be at least 8 characters long.</p>
        </div>

        <div id="alert-box" class="hidden mb-6 p-4 rounded-lg text-sm"></div>

        <form id="reset-form" class="space-y-5">
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                <input type="password" id="password" required minlength="8"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50 focus:bg-white"
                    placeholder="••••••••">
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                <input type="password" id="password_confirmation" required minlength="8"
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50 focus:bg-white"
                    placeholder="••••••••">
                <p id="match-error" class="hidden text-xs text-red-600 mt-1">Passwords do not match.</p>
            </div>

            <button type="submit" id="submit-btn" 
                class="w-full bg-gray-900 hover:bg-gray-800 text-white font-medium py-2.5 rounded-lg transition-colors flex justify-center items-center mt-6">
                <span>Update Password</span>
            </button>
        </form>
    </div>

    <script>
        // Extract token and email from URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');
        const email = urlParams.get('email');

        document.getElementById('reset-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const btn = document.getElementById('submit-btn');
            const alertBox = document.getElementById('alert-box');
            const matchError = document.getElementById('match-error');
            
            const password = document.getElementById('password').value;
            const passwordConf = document.getElementById('password_confirmation').value;

            // Frontend Password Confirmation Validation (Backend ignores confirmation)
            if (password !== passwordConf) {
                matchError.classList.remove('hidden');
                document.getElementById('password_confirmation').classList.add('border-red-500', 'focus:ring-red-500');
                return;
            }

            matchError.classList.add('hidden');
            document.getElementById('password_confirmation').classList.remove('border-red-500', 'focus:ring-red-500');

            if (!token || !email) {
                alertBox.className = 'mb-6 p-4 rounded-lg text-sm bg-red-50 text-red-800 border border-red-200';
                alertBox.textContent = 'Invalid or missing reset token in URL.';
                alertBox.classList.remove('hidden');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            alertBox.classList.add('hidden');

            try {
                const response = await fetch('/api/reset-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        email, 
                        token, 
                        password 
                        // Notice: NO password_confirmation sent to the backend!
                    })
                });

                const data = await response.json();

                if (response.ok || response.status === 200) {
                    document.getElementById('reset-form').classList.add('hidden');
                    alertBox.className = 'mb-6 p-4 rounded-lg text-sm bg-green-50 text-green-800 border border-green-200 text-center';
                    alertBox.innerHTML = '<svg class="w-8 h-8 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> <p class="font-medium text-green-900 mb-1">Password Reset Successful</p><p class="text-green-700">You can now use your new password to log in.</p><a href="#" class="mt-4 inline-block text-blue-600 font-medium hover:underline">Go to Login</a>';
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
                btn.disabled = false;
                btn.innerHTML = '<span>Update Password</span>';
            }
        });
    </script>
</body>
</html>