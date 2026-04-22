<!-- ================= Reports/Feedback Page ================= -->
<div id="manage-reports" class="page-section">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold">Guest Feedback & Reports</h3>
            <button onclick="loadReports()" class="bg-brand-blue text-white px-4 py-2 rounded shadow hover:bg-sky-600">
                ↻ Refresh
            </button>
        </div>
        
        <div id="reports-list" class="grid gap-4">
            <div class="text-center text-gray-400 py-8">Loading reports...</div>
        </div>
    </div>
</div>

<!-- Reply Modal -->
<div id="reply-modal" class="fixed inset-0 bg-black/50 z-[100] hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6">
        <h3 class="text-xl font-bold mb-4">Reply to Guest</h3>
        <input type="hidden" id="reply-report-id">
        <textarea id="reply-message" rows="4" class="w-full border rounded p-2 focus:ring-2 focus:ring-brand-blue outline-none mb-4" placeholder="Type your response here..."></textarea>
        <div class="flex gap-2">
            <button onclick="submitReply()" class="flex-1 bg-brand-blue text-white py-2 rounded font-bold hover:bg-sky-600 transition">Send Reply</button>
            <button onclick="closeReplyModal()" class="flex-1 bg-gray-100 text-gray-600 py-2 rounded font-bold hover:bg-gray-200 transition">Cancel</button>
        </div>
    </div>
</div>

<script>
function loadReports() {
    fetch('../actions/reports_action.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('reports-list');
            
            if (data.success && data.reports.length > 0) {
                container.innerHTML = data.reports.map(report => `
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-50 flex flex-col md:flex-row gap-4 items-start md:items-center">
                        <div class="w-10 h-10 rounded-full ${getReportTypeColor(report.type)} flex items-center justify-center shrink-0">
                            <i class="${getReportTypeIcon(report.type)}"></i>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start mb-1">
                                <h4 class="font-bold text-gray-800">${report.subject || 'No Subject'} 
                                    <span class="text-xs font-normal text-gray-400 ml-2">
                                        ${report.room_name ? 'about ' + report.room_name : ''}
                                    </span>
                                </h4>
                                <span class="px-2 py-1 rounded text-xs font-bold ${getStatusColor(report.status)}">
                                    ${report.status}
                                </span>
                            </div>
                            <p class="text-gray-600 text-sm">${report.message}</p>
                            <p class="text-xs text-gray-400 mt-2">
                                From: ${report.user_name || 'Guest'} | ${report.created_at}
                            </p>
                            ${report.admin_response ? `
                                <div class="mt-3 p-3 bg-blue-50 border-l-4 border-brand-blue rounded-r text-sm">
                                    <strong class="text-brand-blue block mb-1">Your Response:</strong>
                                    <span class="text-gray-700">${report.admin_response}</span>
                                </div>
                            ` : ''}
                        </div>
                        <div class="flex gap-2">
                            <button onclick="openReplyModal(${report.id})" class="text-blue-500 hover:text-blue-700 transition px-2" title="Reply to Guest"><i class="fas fa-reply"></i></button>
                            ${report.status !== 'Resolved' ? `<button onclick="updateReportStatus(${report.id}, 'Resolved')" class="text-green-500 hover:text-green-700 transition px-2" title="Mark as Resolved"><i class="fas fa-check"></i></button>` : ''}
                            ${report.status === 'Resolved' ? `<button onclick="updateReportStatus(${report.id}, 'Pending')" class="text-yellow-500 hover:text-yellow-700 transition px-2" title="Mark as Pending"><i class="fas fa-undo"></i></button>` : ''}
                            <button onclick="deleteReport(${report.id})" class="text-gray-300 hover:text-red-500 transition px-2" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('');
            } else {
                container.innerHTML = '<p class="text-center text-gray-400 py-8">No feedback received yet.</p>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('reports-list').innerHTML = '<p class="text-center text-red-400 py-8">Error loading reports.</p>';
        });
}

function getReportTypeColor(type) {
    const colors = {
        'feedback': 'bg-yellow-100 text-yellow-600',
        'complaint': 'bg-red-100 text-red-600',
        'suggestion': 'bg-blue-100 text-blue-600'
    };
    return colors[type] || 'bg-gray-100 text-gray-600';
}

function getReportTypeIcon(type) {
    const icons = {
        'feedback': 'fas fa-star',
        'complaint': 'fas fa-exclamation-circle',
        'suggestion': 'fas fa-lightbulb'
    };
    return icons[type] || 'fas fa-comment';
}

function getStatusColor(status) {
    const colors = {
        'Pending': 'bg-yellow-100 text-yellow-700',
        'Resolved': 'bg-green-100 text-green-700'
    };
    return colors[status] || 'bg-gray-100 text-gray-700';
}

function updateReportStatus(reportId, status) {
    showConfirm('Mark this report as ' + status + '?', () => {
        fetch('../actions/reports_action.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: reportId, status: status })
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message || 'Status updated!', data.success ? 'success' : 'error');
            if (data.success) loadReports();
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error updating report status', 'error');
        });
    });
}

function deleteReport(reportId) {
    showConfirm('Delete this report?', () => {
        fetch('../actions/reports_action.php?id=' + reportId, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            showToast(data.message || 'Report deleted!', data.success ? 'success' : 'error');
            if (data.success) loadReports();
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error deleting report', 'error');
        });
    });
}

function openReplyModal(reportId) {
    document.getElementById('reply-report-id').value = reportId;
    document.getElementById('reply-message').value = '';
    document.getElementById('reply-modal').classList.remove('hidden');
    document.getElementById('reply-modal').classList.add('flex');
}

function closeReplyModal() {
    document.getElementById('reply-modal').classList.add('hidden');
    document.getElementById('reply-modal').classList.remove('flex');
}

function submitReply() {
    const reportId = document.getElementById('reply-report-id').value;
    const message = document.getElementById('reply-message').value;
    
    if (!message.trim()) {
        showToast('Please enter a message', 'warning');
        return;
    }
    
    fetch('../actions/save_report_reply.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ report_id: reportId, message: message })
    })
    .then(res => res.json())
    .then(data => {
        showToast(data.message || 'Reply sent successfully!', data.success ? 'success' : 'error');
        if (data.success) {
            closeReplyModal();
            loadReports();
        }
    });
}

// Load reports when page loads
document.addEventListener('DOMContentLoaded', loadReports);
</script>
