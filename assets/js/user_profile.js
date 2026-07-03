// ================= USER PROFILE (GUEST MANAGEMENT) =================
        function renderUserProfile(isEditing = false) {
            const user = appState.currentUser;
            
            // Redirect to home if there is no logged-in user
            if (!user) {
                window.location.href = '../index.php';
                return;
            }
            
            // Security: If an admin accesses this page, immediately redirect them to their dashboard
            if (user.role === 'admin') {
                window.location.href = '../admin/dashboard.php';
                return;
            }

            // 1. Render Profile Card (View or Edit mode)
            let profileHTML = '';
            if(isEditing) {
                profileHTML = `
                    <form onsubmit="saveProfile(event)" class="text-left space-y-4 bg-white p-2">
                        <div class="text-center mb-6">
                            <h3 class="text-xl font-bold text-gray-800">Edit Profile</h3>
                            <p class="text-xs text-gray-500 mt-1">Update your personal information</p>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Full Name</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-user"></i></div>
                                <input type="text" id="edit-name" value="${user.name}" class="w-full pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue focus:border-transparent outline-none transition-all" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide">Email Address</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-envelope"></i></div>
                                <input type="email" id="edit-email" value="${user.email}" class="w-full pl-10 pr-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue focus:border-transparent outline-none transition-all" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide flex justify-between">
                                Profile Logo
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-camera"></i></div>
                                <input type="file" id="edit-avatar" accept="image/*" class="w-full pl-10 pr-3 py-2 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue focus:border-transparent outline-none transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wide flex justify-between">
                                New Password <span class="text-gray-400 font-normal normal-case">(leave blank to keep)</span>
                            </label>
                            <div class="relative password-wrapper">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400"><i class="fas fa-lock"></i></div>
                                <input type="password" id="edit-pass" class="w-full pl-10 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:bg-white focus:ring-2 focus:ring-brand-blue focus:border-transparent outline-none transition-all" placeholder="••••••••">
                                <i class="fas fa-eye absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer hover:text-gray-600 transition-colors" id="toggle-edit-pass" onclick="togglePassword('edit-pass', 'toggle-edit-pass')"></i>
                            </div>
                        </div>
                        <div class="flex gap-3 mt-8 pt-4 border-t border-gray-100">
                            <button type="button" onclick="renderUserProfile(false)" class="flex-1 bg-white border border-gray-200 text-gray-700 py-2.5 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-all shadow-sm flex items-center justify-center gap-2"><i class="fas fa-times"></i> Cancel</button>
                            <button type="submit" class="flex-1 bg-gradient-to-r from-brand-blue to-brand-cyan text-white py-2.5 rounded-xl text-sm font-semibold hover:shadow-lg hover:-translate-y-0.5 transition-all shadow-md flex items-center justify-center gap-2"><i class="fas fa-save"></i> Save</button>
                        </div>
                    </form>
                `;
            } else {
profileHTML = `
                    <div class="relative w-28 h-28 mx-auto mb-5 group">
                        <div class="absolute inset-0 bg-gradient-to-br from-brand-blue to-brand-cyan rounded-full shadow-lg transform group-hover:scale-105 transition-transform duration-300"></div>
                        <div class="absolute inset-1 bg-white rounded-full flex items-center justify-center overflow-hidden">
                            ${user.avatar ? 
                                `<img src="../${user.avatar}" class="w-full h-full object-cover" alt="Profile Image">` : 
                                `<span class="text-4xl font-bold bg-gradient-to-br from-brand-blue to-brand-cyan bg-clip-text text-transparent">
                                    ${user.name.charAt(0).toUpperCase()}
                                </span>`
                            }
                        </div>
                    </div>
                    
                    <h3 class="text-2xl font-bold text-gray-800 mb-1">${user.name}</h3>
                    <p class="text-gray-500 text-sm mb-4 font-medium flex items-center justify-center gap-2">
                        <i class="fas fa-envelope text-gray-400"></i> ${user.email}
                    </p>
                    
                    <div class="mb-6">
                        <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-50 text-brand-blue border border-blue-100 shadow-sm">
                            <i class="fas fa-check-circle"></i> Guest Member
                        </span>
                    </div>
                    
                    <div class="space-y-3 mt-6">
                        <button onclick="renderUserProfile(true)" class="w-full bg-gradient-to-r from-brand-blue to-brand-cyan text-white hover:shadow-lg hover:-translate-y-0.5 py-3 rounded-xl transition-all duration-300 font-semibold text-sm shadow-md flex items-center justify-center gap-2">
                            <i class="fas fa-user-edit"></i> Edit Profile
                        </button>
                        
                        <button onclick="sessionStorage.setItem('skipLoader', 'true'); window.location.href = '../index.php';" class="w-full bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-gray-300 py-3 rounded-xl transition-all duration-300 font-semibold text-sm shadow-sm flex items-center justify-center gap-2">
                            <i class="fas fa-home"></i> Back to Home
                        </button>
                    </div>
                `;
            }
            document.getElementById('profile-card-container').innerHTML = profileHTML;

            const userBookings = appState.bookings.filter(b => b.userId === user.id).reverse();
            const container = document.getElementById('user-bookings-container');
            
            if(userBookings.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-bed text-4xl text-gray-200 mb-4"></i>
                        <p class="text-gray-500 mb-4">You have no booking history.</p>
                        <button onclick="router('rooms')" class="bg-brand-blue text-white px-6 py-2 rounded-lg font-medium">Explore Rooms</button>
                    </div>`;
            } else {
                container.innerHTML = `
                    <div class="space-y-4">
                        ${userBookings.map(b => {
                            let statusColor = b.status === 'Confirmed' ? 'bg-green-100 text-green-700' : (b.status === 'Cancelled' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700');
                            let safeRoomName = b.roomName ? b.roomName.replace(/'/g, "\\'") : '';
                            let reportBtn = `<button onclick="openReportModal('${b.id}', '${safeRoomName}')" class="text-xs bg-yellow-50 text-yellow-700 hover:bg-yellow-100 border border-yellow-200 px-3 py-1.5 rounded mt-2 inline-block transition"><i class="fas fa-exclamation-circle"></i> Report Issue / Request Cancellation</button>`;
                            return `
                                <div class="border border-gray-100 p-4 rounded-xl flex flex-col md:flex-row justify-between md:items-center gap-4 hover:bg-gray-50 transition">
                                    <div>
                                        <div class="flex items-center gap-3 mb-1">
                                            <h4 class="font-bold text-brand-dark text-lg">${b.roomName}</h4>
                                            <span class="px-2 py-1 rounded text-xs font-bold ${statusColor}">${b.status}</span>
                                        </div>
                                        <p class="text-sm text-gray-500"><i class="far fa-calendar-alt mr-1"></i> ${b.checkIn} to ${b.checkOut} (${b.nights} nights)</p>
                                        <p class="text-xs text-gray-400 mt-1">Ref: ${b.id} • Booked on ${b.dateBooked}</p>
                                        <p class="text-xs text-red-500 mt-1 mb-1 font-semibold">Strictly Full Payment. No direct cancellations.</p>
                                        ${reportBtn}
                                    </div>
                                    <div class="text-left md:text-right">
                                        <p class="text-xs text-gray-500">Total Amount</p>
                                        <p class="font-bold text-xl text-brand-blue">₱${parseFloat(b.total).toFixed(2)}</p>
                                        <p class="text-xs text-gray-400">Via ${b.paymentMethod}</p>
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                `;
            }
            
            // 3. Render Feedback & Reports
            const reportsContainer = document.getElementById('user-reports-container');
            if (reportsContainer) {
                const userReports = appState.reports || [];
                if (userReports.length === 0) {
                    reportsContainer.innerHTML = `<p class="text-gray-500 text-center py-4">You have not submitted any feedback or reports.</p>`;
                } else {
                    reportsContainer.innerHTML = `<div class="space-y-4">
                        ${userReports.map(r => `
                            <div class="border border-gray-100 p-4 rounded-xl hover:bg-gray-50 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <h4 class="font-bold text-brand-dark">${r.subject || 'No Subject'}</h4>
                                    <span class="px-2 py-1 rounded text-xs font-bold ${r.status === 'Resolved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'}">${r.status}</span>
                                </div>
                                <p class="text-sm text-gray-600 mb-2">${r.message}</p>
                                ${r.admin_response ? `
                                    <div class="bg-blue-50 border-l-4 border-brand-blue p-3 mt-3 rounded-r-lg">
                                        <p class="text-xs font-bold text-brand-blue mb-1"><i class="fas fa-reply mr-1"></i> Admin Response:</p>
                                        <p class="text-sm text-gray-700">${r.admin_response}</p>
                                    </div>
                                ` : ''}
                            </div>
                        `).join('')}
                    </div>`;
                }
            }
        }

        // Toggle Password Visibility
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input && icon) {
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
        }

        // Save profile changes to database
function saveProfile(e) {
            e.preventDefault();
            const newName = document.getElementById('edit-name').value.trim();
            const newEmail = document.getElementById('edit-email').value.trim();
            const newPass = document.getElementById('edit-pass').value.trim();
            const avatarInput = document.getElementById('edit-avatar');

            const formData = new FormData();
            formData.append('id', appState.currentUser.id);
            formData.append('name', newName);
            formData.append('email', newEmail);
            
            if (avatarInput && avatarInput.files[0]) {
                formData.append('avatar', avatarInput.files[0]);
            }

            // First, update basic profile info to database
            fetch('../actions/profile_action.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update local state
                    appState.currentUser.name = data.user.name || newName;
                    appState.currentUser.email = data.user.email || newEmail;
                    if (data.user.avatar !== undefined) {
                        appState.currentUser.avatar = data.user.avatar;
                    }
                    saveState();
                    
                    // If password changed, update it too
                    if (newPass && newPass.length >= 8) {
                        return fetch('../actions/profile_action.php', {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                id: appState.currentUser.id,
                                current_password: '',
                                new_password: newPass
                            })
                        }).then(res => res.json());
                    }
                    return data;
                } else {
                    throw new Error(data.message);
                }
            })
            .then(data => {
                if (data && data.success === false) {
                    throw new Error(data.message || "Failed to update profile details");
                }
                showToast("Profile updated successfully!", 'success');
                updateAuthUI();
                renderUserProfile(false);
            })
            .catch(err => {
                console.error('Profile update error:', err);
                showToast("Failed to update profile: " + err.message, 'error');
            });
        }

        // Log out the current user
        function logout() {
            const role = appState.currentUser ? appState.currentUser.role : 'user';
            appState.currentUser = null;
            saveState(role); // Clears storage for this role
        
            window.location.href = '../logout.php?type=' + role;
        }
        
        // Open report/feedback modal
        function openReportModal(bookingId = null, roomName = null) {
            document.getElementById('report-booking-id').value = bookingId || '';
            if (roomName && roomName !== 'undefined' && roomName !== 'null') {
                document.getElementById('report-subject').value = 'Regarding ' + roomName;
            } else {
                document.getElementById('report-subject').value = '';
            }
            document.getElementById('report-message').value = '';
            const modal = document.getElementById('report-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        
        // Close report/feedback modal
        function closeReportModal() {
            const modal = document.getElementById('report-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        
        // Submit the report
        function submitReport(e) {
            e.preventDefault();
            const bookingId = document.getElementById('report-booking-id').value;
            const type = document.getElementById('report-type').value;
            const subject = document.getElementById('report-subject').value;
            const message = document.getElementById('report-message').value;
        
            fetch('../actions/reports_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: appState.currentUser.id,
                    booking_id: bookingId ? bookingId : null,
                    type: type,
                    subject: subject,
                    message: message
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast('Feedback submitted successfully. Thank you!', 'success');
                    closeReportModal();
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            })
            .catch(err => console.error(err));
        }
