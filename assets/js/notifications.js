/**
 * Modern Notification System for Admin Pages
 * Replaces native alert() and confirm() with professional toasts and modals
 */

let toastContainer = null;
let confirmOverlay = null;

// Modern Toast Notification (same as main app)
window.showToast = function(message, type = 'info') {
    if (!document.getElementById('toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.innerHTML = `
            #toast-container { position: fixed; bottom: 20px; right: 20px; z-index: 100001; display: flex; flex-direction: column; gap: 10px; pointer-events: none; }
            .modern-toast { background: #fff; color: #333; padding: 16px 24px; border-radius: 8px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1), 0 8px 10px -6px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 12px; font-family: inherit; font-size: 0.9rem; font-weight: 500; transform: translateX(120%); opacity: 0; transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275), opacity 0.4s ease; pointer-events: auto; border-left: 4px solid #0ea5e9; }
            .modern-toast.show { transform: translateX(0); opacity: 1; }
            .modern-toast.success { border-left-color: #22c55e; }
            .modern-toast.error { border-left-color: #ef4444; }
            .modern-toast.warning { border-left-color: #f59e0b; }
            .modern-toast i { font-size: 1.2rem; }
            .modern-toast.success i { color: #22c55e; }
            .modern-toast.error i { color: #ef4444; }
            .modern-toast.warning i { color: #f59e0b; }
            .modern-toast.info i { color: #0ea5e9; }
        `;
        document.head.appendChild(style);
    }

    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        document.body.appendChild(toastContainer);
    }
    
    const toast = document.createElement('div');
    toast.className = `modern-toast ${type}`;
    
    let icon = 'fa-info-circle';
    if (type === 'success') icon = 'fa-check-circle';
    if (type === 'error') icon = 'fa-exclamation-circle';
    if (type === 'warning') icon = 'fa-exclamation-triangle';
    
    toast.innerHTML = `<i class="fas ${icon}"></i> <span>${message}</span>`;
    toastContainer.appendChild(toast);
    
    // Animate in
    setTimeout(() => toast.classList.add('show'), 10);
    
    // Animate out after 3.5s
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            if (toast.parentNode) toast.remove();
        }, 400);
    }, 3500);
};

// Modern Confirm Dialog (replaces confirm())
window.showConfirm = function(message, onConfirm, options = {}) {
    const title = options.title || 'Confirm Action';
    const confirmLabel = options.confirmLabel || 'Confirm';
    const variantClass = options.variant === 'primary' ? 'btn-confirm-ok--primary' : '';
    if (!confirmOverlay) {
        confirmOverlay = document.createElement('div');
        confirmOverlay.id = 'custom-confirm-overlay';
        document.body.appendChild(confirmOverlay);
    }
    
    confirmOverlay.innerHTML = `
        <style>
            #custom-confirm-overlay {
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
                z-index: 100002; display: flex; align-items: center; justify-content: center;
                opacity: 0; visibility: hidden; transition: all 0.3s ease;
            }
            #custom-confirm-overlay.show { opacity: 1; visibility: visible; }
            .custom-confirm-box {
                background: #fff; padding: 24px; border-radius: 12px;
                box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
                max-width: 420px; width: 90%; transform: scale(0.95);
                transition: transform 0.25s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            }
            #custom-confirm-overlay.show .custom-confirm-box { transform: scale(1); }
            .custom-confirm-box h4 { margin: 0 0 12px 0; color: #1e293b; font-weight: 700; font-size: 1.125rem; }
            .custom-confirm-box p { color: #64748b; margin-bottom: 20px; font-size: 0.95rem; line-height: 1.5; }
            .custom-confirm-buttons { display: flex; gap: 12px; justify-content: flex-end; }
            .custom-confirm-buttons button {
                padding: 10px 20px; border-radius: 8px; font-weight: 600;
                cursor: pointer; transition: all 0.2s; border: none; font-size: 0.9rem;
            }
            .btn-confirm-cancel { background: #f8fafc; color: #475569; }
            .btn-confirm-cancel:hover { background: #f1f5f9; }
            .btn-confirm-ok { background: #ef4444; color: white; }
            .btn-confirm-ok:hover { background: #dc2626; }
            .btn-confirm-ok--primary { background: #6C5DD3; }
            .btn-confirm-ok--primary:hover { background: #5a4bbd; }
            @media (max-width: 480px) { .custom-confirm-buttons { flex-direction: column; gap: 8px; } }
        </style>
        <div class="custom-confirm-box">
            <h4>${title}</h4>
            <p>${message}</p>
            <div class="custom-confirm-buttons">
                <button class="btn-confirm-cancel" onclick="closeConfirm()">Cancel</button>
                <button class="btn-confirm-ok ${variantClass}" id="confirm-ok-btn">${confirmLabel}</button>
            </div>
        </div>
    `;
    
    confirmOverlay.classList.add('show');
    
    return new Promise((resolve) => {
        const okBtn = document.getElementById('confirm-ok-btn');
        okBtn.onclick = () => {
            closeConfirm();
            resolve(true);
            if (onConfirm) onConfirm();
        };
    });
};

window.closeConfirm = function() {
    if (confirmOverlay) {
        confirmOverlay.classList.remove('show');
    }
};

// Auto-remove containers on page unload
window.addEventListener('beforeunload', () => {
    if (toastContainer) toastContainer.remove();
    if (confirmOverlay) confirmOverlay.remove();
});

// Polyfill replacements (optional)
window.nativeConfirm = window.confirm;
window.confirm = function(msg, onConfirm) {
    return showConfirm(msg, onConfirm);
};
window.nativeAlert = window.alert;
window.alert = function(msg, type = 'info') {
    showToast(msg, type);
    return true;
};

// Infer toast type from message
function inferToastType(message) {
    const lower = message.toLowerCase();
    if (lower.includes('success') || lower.includes('updated') || lower.includes('deleted')) return 'success';
    if (lower.includes('error') || lower.includes('failed')) return 'error';
    if (lower.includes('warning') || lower.includes('are you sure')) return 'warning';
    return 'info';
}

// Global showMessage helper
window.showMessage = function(msg, type = null) {
    showToast(msg, type || inferToastType(msg));
};
