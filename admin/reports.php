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

<script>
function loadReports() {
    fetch('actions/reports_action.php')
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
                        </div>
                        <button onclick="deleteReport(${report.id})" class="text-gray-300 hover:text-red-500 transition px-2">
                            <i class="fas fa-trash"></i>
                        </button>
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

function deleteReport(reportId) {
    if (!confirm('Delete this report?')) {
        return;
    }
    
    fetch('actions/reports_action.php?id=' + reportId, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            loadReports();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting report');
    });
}

// Load reports when page loads
document.addEventListener('DOMContentLoaded', loadReports);
</script>
