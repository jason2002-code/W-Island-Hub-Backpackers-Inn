/**
 * EMAIL VERIFICATION MODULE
 * Specifically handles verifying users when clicking the Google account button
 * Sends a 6-digit verification code & link to the user's Gmail to verify valid accounts.
 */

let emailVerificationState = {
    email: '',
    name: '',
    provider: 'Google',
    verifiedEmails: new Set(),
    pollInterval: null,
    onSuccessCallback: null
};

/**
 * Checks if a given email is already verified in this session or server
 * @param {string} email
 * @returns {Promise<boolean>}
 */
async function checkEmailVerified(email) {
    if (!email) return false;
    const cleanEmail = email.trim().toLowerCase();
    
    // Check in-memory cache first
    if (emailVerificationState.verifiedEmails.has(cleanEmail)) {
        return true;
    }

    try {
        const res = await fetch(`actions/verify_email_action.php?action=check_status&email=${encodeURIComponent(cleanEmail)}`);
        const data = await res.json();
        if (data.success && data.verified) {
            emailVerificationState.verifiedEmails.add(cleanEmail);
            return true;
        }
    } catch (e) {
        console.error('Error checking email verification status:', e);
    }
    return false;
}

/**
 * Triggers the verification flow when clicking the Google account button
 * @param {'google'} context
 * @param {string} providedEmail
 * @param {string} providedName
 */
async function triggerEmailVerification(context = 'google', providedEmail = '', providedName = '') {
    const email = (providedEmail || emailVerificationState.email || '').trim().toLowerCase();
    const name = providedName || emailVerificationState.name || 'Google User';

    if (!email) {
        window.showToast('Please sign in with Google to verify your Gmail account.', 'warning');
        return;
    }

    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        window.showToast('Please enter a valid Gmail address.', 'warning');
        return;
    }

    emailVerificationState.email = email;
    emailVerificationState.name = name;

    // Check if already verified
    const isAlreadyVerified = await checkEmailVerified(email);
    if (isAlreadyVerified) {
        if (typeof emailVerificationState.onSuccessCallback === 'function') {
            const cb = emailVerificationState.onSuccessCallback;
            emailVerificationState.onSuccessCallback = null;
            cb();
        }
        return;
    }

    // Open modal and send code
    openEmailVerificationModal(email);
    sendVerificationCode(email, name);
}

/**
 * Sends OTP code and verification link to Gmail via API
 */
async function sendVerificationCode(email, name = 'Google User') {
    const feedbackEl = document.getElementById('verify-modal-feedback');
    const submitBtn = document.getElementById('btn-submit-verification-code');
    const resendBtn = document.getElementById('btn-resend-verification-code');

    if (feedbackEl) {
        feedbackEl.className = 'text-xs text-center p-2.5 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 block';
        feedbackEl.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Sending verification code to your Gmail...';
    }

    if (resendBtn) resendBtn.disabled = true;

    try {
        const response = await fetch('actions/verify_email_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'send_code',
                email: email,
                name: name
            })
        });

        const data = await response.json();

        if (data.success) {
            if (data.already_verified) {
                emailVerificationState.verifiedEmails.add(email.toLowerCase());
                closeEmailVerificationModal();
                window.showToast('Google account verified successfully!', 'success');
                if (typeof emailVerificationState.onSuccessCallback === 'function') {
                    const cb = emailVerificationState.onSuccessCallback;
                    emailVerificationState.onSuccessCallback = null;
                    cb();
                }
                return;
            }

            if (feedbackEl) {
                feedbackEl.className = 'text-xs text-center p-2.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 block';
                let msg = `<i class="fas fa-paper-plane text-emerald-600 mr-1"></i> Code sent to your Gmail! Check your inbox or spam folder.`;
                if (data.debug_code) {
                    msg += `<div class="mt-1 text-[11px] font-mono text-emerald-700 bg-white/80 p-1.5 rounded border border-emerald-300">Test Code: <strong>${data.debug_code}</strong></div>`;
                }
                feedbackEl.innerHTML = msg;
            }

            window.showToast('Verification code & link sent to ' + email, 'success');
            startEmailPolling(email);
        } else {
            if (feedbackEl) {
                feedbackEl.className = 'text-xs text-center p-2.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 block';
                feedbackEl.innerHTML = `<i class="fas fa-exclamation-circle text-rose-500 mr-1"></i> ${data.message || 'Failed to send verification code.'}`;
            }
        }
    } catch (err) {
        console.error('Error sending verification code:', err);
        if (feedbackEl) {
            feedbackEl.className = 'text-xs text-center p-2.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 block';
            feedbackEl.innerHTML = 'Network error while sending verification code. Please try again.';
        }
    } finally {
        if (resendBtn) resendBtn.disabled = false;
    }
}

/**
 * Submits the 6-digit verification code entered by user
 */
async function submitEmailVerificationCode() {
    const otpInput = document.getElementById('verify-modal-otp-input');
    const code = otpInput?.value?.trim();
    const email = emailVerificationState.email;
    const feedbackEl = document.getElementById('verify-modal-feedback');
    const submitBtn = document.getElementById('btn-submit-verification-code');

    if (!code || code.length < 6) {
        window.showToast('Please enter the 6-digit verification code sent to your Gmail.', 'warning');
        otpInput?.focus();
        return;
    }

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Verifying Code...';
    }

    try {
        const response = await fetch('actions/verify_email_action.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'verify_code',
                email: email,
                code: code
            })
        });

        const data = await response.json();

        if (data.success) {
            emailVerificationState.verifiedEmails.add(email.toLowerCase());
            stopEmailPolling();

            if (feedbackEl) {
                feedbackEl.className = 'text-xs text-center p-2.5 rounded-xl bg-emerald-50 text-emerald-800 border border-emerald-200 block';
                feedbackEl.innerHTML = '<i class="fas fa-check-circle text-emerald-600 mr-1"></i> Gmail verified successfully!';
            }

            window.showToast('Gmail verified successfully! Confirmed as valid user.', 'success');

            setTimeout(() => {
                closeEmailVerificationModal();
                if (typeof emailVerificationState.onSuccessCallback === 'function') {
                    const cb = emailVerificationState.onSuccessCallback;
                    emailVerificationState.onSuccessCallback = null;
                    cb();
                }
            }, 700);
        } else {
            if (feedbackEl) {
                feedbackEl.className = 'text-xs text-center p-2.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 block';
                feedbackEl.innerHTML = `<i class="fas fa-times-circle text-rose-500 mr-1"></i> ${data.message || 'Invalid code.'}`;
            }
            window.showToast(data.message || 'Invalid code. Please check your Gmail.', 'error');
            if (otpInput) {
                otpInput.value = '';
                otpInput.focus();
            }
        }
    } catch (err) {
        console.error('Error verifying code:', err);
        window.showToast('Network error while verifying code.', 'error');
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Verify Code &amp; Continue';
        }
    }
}

/**
 * Resends the verification code
 */
function resendEmailVerificationCode() {
    if (!emailVerificationState.email) return;
    const otpInput = document.getElementById('verify-modal-otp-input');
    if (otpInput) otpInput.value = '';
    sendVerificationCode(emailVerificationState.email, emailVerificationState.name);
}

/**
 * Starts background polling in case the user clicked the direct email link
 */
function startEmailPolling(email) {
    stopEmailPolling();
    emailVerificationState.pollInterval = setInterval(async () => {
        try {
            const res = await fetch(`actions/verify_email_action.php?action=check_status&email=${encodeURIComponent(email)}`);
            const data = await res.json();
            if (data.success && data.verified) {
                stopEmailPolling();
                emailVerificationState.verifiedEmails.add(email.toLowerCase());
                closeEmailVerificationModal();
                window.showToast('Gmail verified via link! Confirmed as valid user.', 'success');
                if (typeof emailVerificationState.onSuccessCallback === 'function') {
                    const cb = emailVerificationState.onSuccessCallback;
                    emailVerificationState.onSuccessCallback = null;
                    cb();
                }
            }
        } catch (e) {
            // Ignore polling errors
        }
    }, 3500);
}

function stopEmailPolling() {
    if (emailVerificationState.pollInterval) {
        clearInterval(emailVerificationState.pollInterval);
        emailVerificationState.pollInterval = null;
    }
}

/**
 * Opens verification modal
 */
function openEmailVerificationModal(email, onSuccess = null) {
    const modal = document.getElementById('email-verification-modal');
    const emailDisplay = document.getElementById('verify-modal-email-display');
    const otpInput = document.getElementById('verify-modal-otp-input');
    const feedbackEl = document.getElementById('verify-modal-feedback');

    if (onSuccess) {
        emailVerificationState.onSuccessCallback = onSuccess;
    }

    if (emailDisplay) emailDisplay.textContent = email;
    if (otpInput) {
        otpInput.value = '';
        setTimeout(() => otpInput.focus(), 250);
    }
    if (feedbackEl) {
        feedbackEl.className = 'hidden text-xs text-center p-2 rounded-xl';
        feedbackEl.innerHTML = '';
    }

    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
}

/**
 * Closes verification modal
 */
function closeEmailVerificationModal() {
    const modal = document.getElementById('email-verification-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
    stopEmailPolling();
}

/**
 * Initializes listeners on the OTP input
 */
function initEmailVerificationListeners() {
    const otpInput = document.getElementById('verify-modal-otp-input');
    if (otpInput && !otpInput.dataset.bound) {
        otpInput.dataset.bound = 'true';
        otpInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitEmailVerificationCode();
            }
        });
        otpInput.addEventListener('input', () => {
            if (otpInput.value.length === 6) {
                submitEmailVerificationCode();
            }
        });
    }
}

// Attach to global window
window.checkEmailVerified = checkEmailVerified;
window.triggerEmailVerification = triggerEmailVerification;
window.openEmailVerificationModal = openEmailVerificationModal;
window.closeEmailVerificationModal = closeEmailVerificationModal;
window.submitEmailVerificationCode = submitEmailVerificationCode;
window.resendEmailVerificationCode = resendEmailVerificationCode;
window.emailVerificationState = emailVerificationState;

// Init on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEmailVerificationListeners);
} else {
    initEmailVerificationListeners();
}
