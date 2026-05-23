document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('adminLoginForm');
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('submitBtn');

    // Toggle Password Visibility
    if (togglePassword) {
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.querySelector('i').classList.toggle('bi-eye');
            togglePassword.querySelector('i').classList.toggle('bi-eye-slash');
        });
    }

    // Form Submission with Scanning Animation
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Start scanning animation
            submitBtn.classList.add('scanning');
            submitBtn.disabled = true;

            const formData = new FormData(loginForm);

            // Simulate a delay for the scanning effect
            setTimeout(() => {
                fetch('login.php', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirect on success
                        window.location.href = data.redirect;
                    } else {
                        // Stop animation and shake on failure
                        submitBtn.classList.remove('scanning');
                        submitBtn.disabled = false;
                        loginForm.classList.add('shake');
                        
                        // Show error alert (optional, already handled by PHP in non-AJAX)
                        setTimeout(() => loginForm.classList.remove('shake'), 500);
                        alert(data.message || 'Access Denied: Invalid Credentials');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    submitBtn.classList.remove('scanning');
                    submitBtn.disabled = false;
                });
            }, 1500); // 1.5s delay for scanning effect
        });
    }
});
