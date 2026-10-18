/**
 * EMAIL VERIFICATION MODULE (DEPRECATED & DISABLED)
 * Verification code modal and functions removed per user request.
 */
window.checkEmailVerified = async function() { return true; };
window.triggerEmailVerification = function() {};
window.openEmailVerificationModal = function() {};
window.closeEmailVerificationModal = function() {};
window.submitEmailVerificationCode = function() {};
window.resendEmailVerificationCode = function() {};
window.emailVerificationState = { email: '', verifiedEmails: new Set() };
