<!-- ================= Manage Users Page ================= -->
<div id="manage-users" class="page-section">
    <div class="p-6">
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <div>
                <p class="text-sm text-gray-400">Archive an account to hide it from the active list without losing its bookings. Permanently delete only from the Archive.</p>
            </div>
            <button onclick="loadUsers()" class="bg-brand-blue text-white px-4 py-2 rounded shadow hover:bg-sky-600 flex items-center gap-2">
                <i class="fas fa-rotate-right"></i> Refresh
            </button>
        </div>

        <!-- View Tabs -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-4">
            <div class="inline-flex bg-gray-100 rounded-xl p-1">
                <button type="button" data-users-view="active" onclick="switchUsersView('active')"
                        class="users-tab flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition">
                    <i class="fas fa-user-check"></i>
                    Active Users
                    <span id="users-active-count" class="text-xs font-bold px-2 py-0.5 rounded-full bg-white text-gray-500">0</span>
                </button>
                <button type="button" data-users-view="archived" onclick="switchUsersView('archived')"
                        class="users-tab flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold transition">
                    <i class="fas fa-box-archive"></i>
                    Archive
                    <span id="users-archived-count" class="text-xs font-bold px-2 py-0.5 rounded-full bg-white text-gray-500">0</span>
                </button>
            </div>

            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                <input type="text" id="users-search-input" oninput="filterUsers()" placeholder="Search name, email or ID..."
                       class="pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-brand-purple/20 w-64 transition">
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead id="users-table-head">
                        <tr class="bg-gray-50 text-xs text-gray-500 uppercase"></tr>
                    </thead>
                    <tbody id="users-table-body" class="text-sm">
                        <tr>
                            <td colspan="8" class="p-6 text-center text-gray-400">Loading users...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="users-table-footer" class="px-4 py-3 border-t border-gray-100 text-xs text-gray-400"></div>
        </div>
    </div>
</div>
<script>
/* ============ User Management (Active / Archive) ============ */
window.usrState = window.usrState || {
    view: 'active',
    users: [],
    counts: { active: 0, archived: 0 },
    requestId: 0,
    loaded: false
};

const USER_TABLE_COLUMNS = {
    active: ['ID', 'User', 'Email', 'Phone', 'Role', 'Bookings', 'Joined', 'Actions'],
    archived: ['ID', 'User', 'Email', 'Phone', 'Role', 'Bookings', 'Archived On', 'Actions']
};

/* ---------- Data loading ---------- */

function loadUsers(view) {
    if (typeof view === 'string') {
        usrState.view = view === 'archived' ? 'archived' : 'active';
    }

    const requestId = ++usrState.requestId;
    usrState.loaded = false;

    applyTabStyles();
    showUsersTableMessage('Loading users...');

    fetch('../actions/users_action.php?view=' + usrState.view)
        .then(response => response.json())
        .then(data => {
            if (requestId !== usrState.requestId) return;
            if (!data.success) throw new Error(data.message || 'Unable to load users');

            usrState.users = Array.isArray(data.users) ? data.users : [];
            usrState.counts = data.counts || { active: usrState.users.length, archived: 0 };
            usrState.loaded = true;

            renderUserCounts();
            renderUsers();
        })
        .catch(error => {
            if (requestId !== usrState.requestId) return;
            usrState.users = [];
            usrState.loaded = false;
            renderUserCounts();
            showUsersTableMessage(
                error.message || 'Error loading users. Make sure the database is set up.',
                true
            );
        });
}

function switchUsersView(view) {
    if (usrState.view === view) return;

    usrState.view = view === 'archived' ? 'archived' : 'active';

    const searchInput = document.getElementById('users-search-input');
    if (searchInput) searchInput.value = '';

    loadUsers();
}

function filterUsers() {
    if (usrState.loaded) renderUsers();
}

/* ---------- Rendering ---------- */

function renderUsers() {
    renderTableHead();

    const visibleUsers = getVisibleUsers();
    const tbody = document.getElementById('users-table-body');
    const footer = document.getElementById('users-table-footer');
    if (!tbody) return;

    tbody.textContent = '';

    if (visibleUsers.length === 0) {
        tbody.appendChild(buildEmptyRow());
        if (footer) footer.textContent = '';
        return;
    }

    visibleUsers.forEach(user => tbody.appendChild(buildUserRow(user)));

    if (footer) footer.textContent = buildFooterMessage(visibleUsers.length);
}

function renderTableHead() {
    const headRow = document.querySelector('#users-table-head tr');
    if (!headRow) return;

    headRow.textContent = '';

    USER_TABLE_COLUMNS[usrState.view].forEach(label => {
        const th = document.createElement('th');
        th.className = 'p-4';
        th.textContent = label;
        headRow.appendChild(th);
    });
}

function renderUserCounts() {
    const activeBadge = document.getElementById('users-active-count');
    const archivedBadge = document.getElementById('users-archived-count');

    if (activeBadge) activeBadge.textContent = usrState.counts.active;
    if (archivedBadge) archivedBadge.textContent = usrState.counts.archived;
}

function applyTabStyles() {
    document.querySelectorAll('.users-tab').forEach(tab => {
        const isActive = tab.dataset.usersView === usrState.view;

        tab.classList.toggle('bg-white', isActive);
        tab.classList.toggle('text-brand-purple', isActive);
        tab.classList.toggle('shadow-sm', isActive);
        tab.classList.toggle('text-gray-500', !isActive);
    });
}

function showUsersTableMessage(message, isError = false) {
    const tbody = document.getElementById('users-table-body');
    const footer = document.getElementById('users-table-footer');
    if (!tbody) return;

    tbody.textContent = '';

    const row = document.createElement('tr');
    const cell = document.createElement('td');
    cell.colSpan = USER_TABLE_COLUMNS[usrState.view].length;
    cell.className = 'p-6 text-center ' + (isError ? 'text-red-400' : 'text-gray-400');
    cell.textContent = message;

    row.appendChild(cell);
    tbody.appendChild(row);

    if (footer) footer.textContent = '';
}

/* ---------- Row builders (DOM based, no HTML injection) ---------- */

function buildUserRow(user) {
    const row = document.createElement('tr');
    row.className = 'border-t hover:bg-gray-50';

    row.appendChild(buildCell(String(user.id), 'p-4 font-mono text-gray-500'));
    row.appendChild(buildIdentityCell(user));
    row.appendChild(buildCell(user.email || '—', 'p-4 text-gray-600'));
    row.appendChild(buildCell(user.phone || '—', 'p-4 text-gray-600'));
    row.appendChild(buildRoleCell(user));
    row.appendChild(buildCell(String(user.bookings_count), 'p-4 text-gray-600'));
    row.appendChild(buildCell(
        formatUserDate(usrState.view === 'archived' ? user.archived_at : user.created_at),
        'p-4 text-gray-500'
    ));
    row.appendChild(buildActionsCell(user));

    return row;
}

function buildCell(text, className) {
    const cell = document.createElement('td');
    cell.className = className;
    cell.textContent = text;
    return cell;
}

function buildIdentityCell(user) {
    const cell = document.createElement('td');
    cell.className = 'p-4';

    const wrapper = document.createElement('div');
    wrapper.className = 'flex items-center gap-3';

    wrapper.appendChild(createAvatar(user));

    const name = document.createElement('span');
    name.className = 'font-bold text-gray-700';
    name.textContent = user.name || 'Unknown user';
    wrapper.appendChild(name);

    cell.appendChild(wrapper);
    return cell;
}

function buildRoleCell(user) {
    const cell = document.createElement('td');
    cell.className = 'p-4';

    const isAdmin = user.role === 'admin';
    const badge = document.createElement('span');
    badge.className = 'px-2 py-1 rounded text-xs font-semibold ' +
        (isAdmin ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700');
    badge.textContent = isAdmin ? 'Admin' : 'Customer';
    cell.appendChild(badge);

    if (user.is_archived) {
        const chip = document.createElement('span');
        chip.className = 'ml-2 px-2 py-1 rounded text-xs font-semibold bg-amber-100 text-amber-700';
        chip.textContent = 'Archived';
        cell.appendChild(chip);
    }

    return cell;
}

function buildActionsCell(user) {
    const cell = document.createElement('td');
    cell.className = 'p-4';

    const wrapper = document.createElement('div');
    wrapper.className = 'flex items-center gap-3';

    if (user.is_protected) {
        wrapper.appendChild(buildProtectedNote());
    } else if (user.is_archived) {
        wrapper.appendChild(buildActionButton('Restore', 'fas fa-rotate-left',
            'text-green-600 hover:text-green-700 hover:underline',
            () => restoreUser(user.id, user.name)));
        wrapper.appendChild(buildActionButton('Delete Permanently', 'fas fa-trash-alt',
            'text-red-600 hover:text-red-700 hover:underline',
            () => deleteUserPermanently(user.id, user.name)));
    } else {
        wrapper.appendChild(buildActionButton('Archive', 'fas fa-box-archive',
            'text-amber-600 hover:text-amber-700 hover:underline',
            () => archiveUser(user.id, user.name)));
    }

    cell.appendChild(wrapper);
    return cell;
}

function buildProtectedNote() {
    const note = document.createElement('span');
    note.className = 'text-xs text-gray-400 flex items-center gap-1.5';

    const icon = document.createElement('i');
    icon.className = 'fas fa-shield-halved';

    note.appendChild(icon);
    note.appendChild(document.createTextNode(' Protected account'));

    return note;
}

function buildActionButton(label, iconClass, className, handler) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'inline-flex items-center gap-1.5 text-xs font-semibold whitespace-nowrap ' + className;

    const icon = document.createElement('i');
    icon.className = iconClass;

    button.appendChild(icon);
    button.appendChild(document.createTextNode(' ' + label));
    button.addEventListener('click', handler);

    return button;
}

function buildEmptyRow() {
    const row = document.createElement('tr');
    const cell = document.createElement('td');

    cell.colSpan = USER_TABLE_COLUMNS[usrState.view].length;
    cell.className = 'p-8 text-center text-gray-400';
    cell.textContent = hasSearchTerm()
        ? 'No users match your search.'
        : (usrState.view === 'archived'
            ? 'The archive is empty. Accounts you archive will appear here.'
            : 'No active users found.');

    row.appendChild(cell);
    return row;
}

/* ---------- Avatars & formatting ---------- */

function createAvatar(user) {
    if (!user.avatar) return createInitialsBadge(user.name);

    const image = document.createElement('img');
    image.src = resolveAvatarSrc(user.avatar);
    image.alt = user.name || 'User avatar';
    image.className = 'w-9 h-9 rounded-full object-cover bg-gray-100';

    image.addEventListener('error', () => {
        if (image.parentNode) {
            image.parentNode.replaceChild(createInitialsBadge(user.name), image);
        }
    });

    return image;
}

function createInitialsBadge(name) {
    const badge = document.createElement('div');
    badge.className = 'w-9 h-9 rounded-full bg-purple-100 text-brand-purple flex items-center justify-center text-xs font-bold uppercase';
    badge.textContent = initialsOf(name);
    return badge;
}

function initialsOf(name) {
    const parts = String(name || '').trim().split(/\s+/).filter(Boolean);

    if (parts.length === 0) return '?';
    if (parts.length === 1) return parts[0].substring(0, 2);

    return parts[0][0] + parts[parts.length - 1][0];
}

function resolveAvatarSrc(avatar) {
    if (/^(https?:)?\/\//i.test(avatar) || avatar.indexOf('data:') === 0) return avatar;
    if (avatar.indexOf('../') === 0 || avatar.indexOf('/') === 0) return avatar;

    return '../' + avatar.replace(/^\.\//, '');
}

function formatUserDate(value) {
    if (!value) return '—';

    const date = new Date(String(value).replace(' ', 'T'));
    if (isNaN(date.getTime())) return String(value).split(' ')[0];

    return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
}

/* ---------- Search helpers ---------- */

function getSearchTerm() {
    const input = document.getElementById('users-search-input');
    return input ? input.value.trim().toLowerCase() : '';
}

function hasSearchTerm() {
    return getSearchTerm().length > 0;
}

function getVisibleUsers() {
    const term = getSearchTerm();
    if (!term) return usrState.users;

    return usrState.users.filter(user => matchesSearch(user, term));
}

function matchesSearch(user, term) {
    return [user.id, user.name, user.email, user.phone, user.role]
        .filter(value => value !== null && value !== undefined && value !== '')
        .some(value => String(value).toLowerCase().indexOf(term) !== -1);
}

function buildFooterMessage(visibleCount) {
    const total = usrState.users.length;
    const label = usrState.view === 'archived' ? 'archived user(s)' : 'active user(s)';

    if (hasSearchTerm()) {
        return `Showing ${visibleCount} of ${total} ${label}.`;
    }

    if (usrState.view === 'archived') {
        return `${total} ${label}. Deleting permanently keeps their bookings, reports and reviews but removes the link to this account.`;
    }

    return `${total} ${label}.`;
}

/* ---------- Actions ---------- */

function archiveUser(userId, userName) {
    showConfirm(
        `Archive "${userName}"? The account is removed from the Active Users list and can no longer sign in. Bookings, reports and reviews are kept, and you can restore it any time from the Archive tab.`,
        () => performUserAction('archive', userId),
        { title: 'Archive User', variant: 'danger', confirmLabel: 'Archive' }
    );
}

function restoreUser(userId, userName) {
    showConfirm(
        `Restore "${userName}" to the Active Users list? They will be able to sign in again.`,
        () => performUserAction('restore', userId),
        { title: 'Restore User', variant: 'primary', confirmLabel: 'Restore' }
    );
}

function deleteUserPermanently(userId, userName) {
    showConfirm(
        `Permanently delete "${userName}"? This cannot be undone. Their bookings, reports and reviews are kept but will no longer be linked to this account.`,
        () => performDeleteUser(userId),
        { title: 'Delete Permanently', variant: 'danger', confirmLabel: 'Delete Permanently' }
    );
}

function performUserAction(action, userId) {
    fetch('../actions/users_action.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: action, id: userId })
    })
        .then(response => response.json())
        .then(data => {
            showToast(data.message || 'Action completed.', data.success ? 'success' : 'error');
            if (data.success) loadUsers();
        })
        .catch(error => {
            console.error('Error performing user action:', error);
            showToast('Error contacting the server. Please try again.', 'error');
        });
}

function performDeleteUser(userId) {
    fetch('../actions/users_action.php?id=' + encodeURIComponent(userId), {
        method: 'DELETE'
    })
        .then(response => response.json())
        .then(data => {
            showToast(data.message || 'Delete failed.', data.success ? 'success' : 'error');
            if (data.success) loadUsers();
        })
        .catch(error => {
            console.error('Error deleting user:', error);
            showToast('Error contacting the server. Please try again.', 'error');
        });
}

/* ---------- Init ---------- */

document.addEventListener('DOMContentLoaded', () => loadUsers('active'));
</script>
