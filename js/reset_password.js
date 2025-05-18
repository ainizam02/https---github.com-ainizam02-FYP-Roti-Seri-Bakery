function togglePassword(inputId) {
    const passwordInput = document.getElementById(inputId);
    const button = passwordInput.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
} 

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.reset-password-form');
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const passwordError = document.getElementById('password-error');
    const confirmError = document.getElementById('confirm-error');

    function validatePassword(password) {
        const minLength = 8;
        const hasUpperCase = /[A-Z]/.test(password);
        const hasLowerCase = /[a-z]/.test(password);
        const hasNumbers = /\d/.test(password);
        const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

        let errorMessage = [];

        if (password.length < minLength) {
            errorMessage.push("Password must be at least 8 characters long");
        }
        if (!hasUpperCase) {
            errorMessage.push("Include at least one uppercase letter");
        }
        if (!hasLowerCase) {
            errorMessage.push("Include at least one lowercase letter");
        }
        if (!hasNumbers) {
            errorMessage.push("Include at least one number");
        }
        if (!hasSpecialChar) {
            errorMessage.push("Include at least one special character");
        }

        return errorMessage;
    }

    newPassword.addEventListener('input', function() {
        const errors = validatePassword(this.value);
        if (errors.length > 0) {
            passwordError.innerHTML = errors.join('<br>');
            passwordError.style.display = 'block';
            this.classList.add('invalid');
        } else {
            passwordError.style.display = 'none';
            this.classList.remove('invalid');
        }
    });

    confirmPassword.addEventListener('input', function() {
        if (this.value !== newPassword.value) {
            confirmError.textContent = 'Passwords do not match';
            confirmError.style.display = 'block';
            this.classList.add('invalid');
        } else {
            confirmError.style.display = 'none';
            this.classList.remove('invalid');
        }
    });

    form.addEventListener('submit', function(e) {
        const passwordErrors = validatePassword(newPassword.value);
        const passwordsMatch = newPassword.value === confirmPassword.value;

        if (passwordErrors.length > 0 || !passwordsMatch) {
            e.preventDefault();
            
            if (passwordErrors.length > 0) {
                passwordError.innerHTML = passwordErrors.join('<br>');
                passwordError.style.display = 'block';
                newPassword.classList.add('invalid');
            }
            
            if (!passwordsMatch) {
                confirmError.textContent = 'Passwords do not match';
                confirmError.style.display = 'block';
                confirmPassword.classList.add('invalid');
            }
        }
    });
}); 