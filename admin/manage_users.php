<!-- ================= Manage Users Page ================= -->
<div id="manage-users" class="page-section">
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-2xl font-bold">User Management</h3>
            <button onclick="loadUsers()" class="bg-brand-blue text-white px-4 py-2 rounded shadow hover:bg-sky-600">
                ↻ Refresh
            </button>
        </div>
        
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[800px]">
                    <thead>
                        <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <th class="p-4">ID</th>
                            <th class="p-4">Name</th>
                            <th class="p-4">Email</th>
                            <th class="p-4">Role</th>
                            <th class="p-4">Created At</th>
                            <th class="p-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body" class="text-sm">
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-400">Loading users...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function loadUsers() {
    fetch('actions/users_action.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById('users-table-body');
            
            if (data.success && data.users.length > 0) {
                tbody.innerHTML = data.users.map(user => `
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-4 font-mono">${user.id}</td>
                        <td class="p-4 font-bold">${user.name}</td>
                        <td class="p-4">${user.email}</td>
                        <td class="p-4">
                            <span class="px-2 py-1 rounded text-xs ${user.role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700'}">
                                ${user.role}
                            </span>
                        </td>
                        <td class="p-4">${user.created_at}</td>
                        <td class="p-4">
                            ${user.role !== 'admin' ? `<button onclick="deleteUser(${user.id})" class="text-red-600 hover:underline">Delete</button>` : '<span class="text-gray-400">-</span>'}
                        </td>
                    </tr>
                `).join('');
            } else {
                tbody.innerHTML = '<tr><td colspan="6" class="p-4 text-center text-gray-400">No users found.</td></tr>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('users-table-body').innerHTML = 
                '<tr><td colspan="6" class="p-4 text-center text-red-400">Error loading users. Make sure the database is set up.</td></tr>';
        });
}

function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user?')) {
        return;
    }
    
    fetch('actions/users_action.php?id=' + userId, {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            loadUsers();
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting user');
    });
}

// Load users when page loads
document.addEventListener('DOMContentLoaded', loadUsers);
</script>
