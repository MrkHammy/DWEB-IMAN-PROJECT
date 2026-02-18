/**
 * Fox Lab – Password Checker JS
 * Real-time password strength analysis
 */

// Load weak passwords from database on page load
window._weakPasswords = [];
fetch('../api/weak_passwords.php')
    .then(res => res.json())
    .then(data => { window._weakPasswords = data; })
    .catch(err => console.error('Failed to load weak passwords:', err));

// Real-time analysis on input
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('passwordInput');
    if (input) {
        input.addEventListener('input', () => {
            updateStrengthIndicators(input.value);
        });
    }
});

/**
 * Toggle password visibility
 */
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const icon = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

/**
 * Update criteria indicators in real-time
 */
function updateStrengthIndicators(password) {
    const criteria = {
        length:  password.length >= 8,
        upper:   /[A-Z]/.test(password),
        lower:   /[a-z]/.test(password),
        number:  /[0-9]/.test(password),
        symbol:  /[^A-Za-z0-9]/.test(password)
    };

    // Update dots and status text
    Object.keys(criteria).forEach(key => {
        const dot = document.getElementById('dot-' + key);
        const status = document.getElementById('status-' + key);
        if (dot && status) {
            if (password.length === 0) {
                dot.className = 'criteria-dot';
                status.className = 'criteria-status';
                status.textContent = 'PENDING';
            } else if (criteria[key]) {
                dot.className = 'criteria-dot pass';
                status.className = 'criteria-status pass';
                status.textContent = 'PASS';
            } else {
                dot.className = 'criteria-dot fail';
                status.className = 'criteria-status fail';
                status.textContent = 'FAIL';
            }
        }
    });

    // Calculate overall strength
    const passedCount = Object.values(criteria).filter(Boolean).length;
    const strengthBar = document.getElementById('strengthBar');
    const strengthStatus = document.getElementById('strengthStatus');

    if (password.length === 0) {
        strengthBar.style.width = '0%';
        strengthBar.style.background = '#e9ecef';
        strengthStatus.textContent = 'Waiting for Input...';
        strengthStatus.style.color = '#6c757d';
        return;
    }

    let level, color, width;
    if (passedCount <= 1) {
        level = 'Very Weak';
        color = '#e74c3c';
        width = '15%';
    } else if (passedCount === 2) {
        level = 'Weak';
        color = '#e67e22';
        width = '35%';
    } else if (passedCount === 3) {
        level = 'Fair';
        color = '#f39c12';
        width = '55%';
    } else if (passedCount === 4) {
        level = 'Strong';
        color = '#27ae60';
        width = '75%';
    } else {
        level = 'Very Strong';
        color = '#2ecc71';
        width = '95%';
    }

    // Bonus for length
    if (password.length >= 16 && passedCount >= 4) {
        level = 'Excellent';
        color = '#2ecc71';
        width = '100%';
    }

    strengthBar.style.width = width;
    strengthBar.style.background = `linear-gradient(135deg, ${color}, ${color}dd)`;
    strengthStatus.textContent = level;
    strengthStatus.style.color = color;
}

/**
 * Analyze password and log metadata to backend
 */
function analyzePassword() {
    const password = document.getElementById('passwordInput').value;
    
    if (password.length === 0) {
        alert('Please enter a password to analyze.');
        return;
    }

    updateStrengthIndicators(password);

    // Determine strength level
    const criteria = {
        length: password.length >= 8,
        upper: /[A-Z]/.test(password),
        lower: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        symbol: /[^A-Za-z0-9]/.test(password)
    };

    const passedCount = Object.values(criteria).filter(Boolean).length;
    let strengthLevel;
    if (passedCount <= 1) strengthLevel = 'Very Weak';
    else if (passedCount === 2) strengthLevel = 'Weak';
    else if (passedCount === 3) strengthLevel = 'Fair';
    else if (passedCount === 4) strengthLevel = 'Strong';
    else strengthLevel = 'Very Strong';

    // Check against known breached passwords (loaded from DB)
    const compromiseStatus = document.getElementById('compromiseStatus');
    const isCompromised = window._weakPasswords && window._weakPasswords.includes(password.toLowerCase());
    
    if (isCompromised) {
        compromiseStatus.innerHTML = '<span style="color:#e74c3c;font-weight:600;">⚠ This password has been found in known breach databases!</span>';
    } else {
        compromiseStatus.innerHTML = '<span style="color:#27ae60;font-weight:600;">✓ No matches found in known breach databases.</span>';
    }

    // Log metadata to backend (NOT the password itself)
    const formData = new FormData();
    formData.append('action', 'log_strength');
    formData.append('strength_level', strengthLevel);
    formData.append('char_count', password.length);
    formData.append('has_uppercase', criteria.upper ? 1 : 0);
    formData.append('has_lowercase', criteria.lower ? 1 : 0);
    formData.append('has_numbers', criteria.number ? 1 : 0);
    formData.append('has_symbols', criteria.symbol ? 1 : 0);
    formData.append('is_compromised', isCompromised ? 1 : 0);

    fetch('checker.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        console.log('Password strength metadata logged:', data);
    })
    .catch(err => {
        console.error('Failed to log metadata:', err);
    });
}
