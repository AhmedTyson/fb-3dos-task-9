<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | TechAccessories</title>
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
            <h2 class="text-xl font-semibold text-gray-800">Reset your password</h2>
            <p class="text-sm text-gray-500 mt-2">Enter your email address and we'll send you a link to reset your password.</p>
        </div>

        <div id="alert-box" class="hidden mb-6 p-4 rounded-lg text-sm"></div>

        <form id="forgot-form" class="space-y-6">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" id="email" name="email" required 
                    class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors bg-gray-50 focus:bg-white"
                    placeholder="you@example.com">
            </div>

            <button type="submit" id="submit-btn" 
                class="w-full bg-gray-900 hover:bg-gray-800 text-white font-medium py-2.5 rounded-lg transition-colors flex justify-center items-center">
                <span>Send Reset Link</span>
            </button>
        </form>
        
        <div class="mt-6 text-center">
            <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Return to login</a>
        </div>
    </div>

    <script>
        document.getElementById('forgot-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('submit-btn');
            const alertBox = document.getElementById('alert-box');
            const email = document.getElementById('email').value;

            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
            alertBox.classList.add('hidden');

            try {
                const response = await fetch('/api/forgot-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ email })
                });

                const data = await response.json();

                if (response.ok || response.status === 200) {
                    alertBox.className = 'mb-6 p-4 rounded-lg text-sm bg-green-50 text-green-800 border border-green-200';
                    alertBox.textContent = 'If an account exists with that email, a reset link has been sent.';
                    alertBox.classList.remove('hidden');
                } else {
                    throw new Error(data.message || 'Something went wrong.');
                }
            } catch (err) {
                alertBox.className = 'mb-6 p-4 rounded-lg text-sm bg-red-50 text-red-800 border border-red-200';
                alertBox.textContent = err.message;
                alertBox.classList.remove('hidden');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<span>Send Reset Link</span>';
            }
        });
    </script>
</body>
</html>