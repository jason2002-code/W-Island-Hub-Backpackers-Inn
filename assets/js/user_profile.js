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
                    <form onsubmit="saveProfile(event)" class="text-left space-y-3">
                        <h3 class="text-lg font-bold text-brand-dark mb-4 text-center">Edit Profile</h3>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Full Name</label>
                            <input type="text" id="edit-name" value="${user.name}" class="w-full border rounded p-2 text-sm focus:ring-2 focus:ring-brand-blue outline-none" required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Email Address</label>
                            <input type="email" id="edit-email" value="${user.email}" class="w-full border rounded p-2 text-sm focus:ring-2 focus:ring-brand-blue outline-none" required>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">New Password <span class="text-gray-400 text-[10px]">(leave blank to keep)</span></label>
                            <div class="password-wrapper">
                                <input type="password" id="edit-pass" class="w-full border rounded p-2 text-sm focus:ring-2 focus:ring-brand-blue outline-none pr-10" placeholder="••••••••">
                                <i class="fas fa-eye password-toggle-icon" id="toggle-edit-pass" onclick="togglePassword('edit-pass', 'toggle-edit-pass')"></i>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-6">
                            <button type="submit" class="flex-1 bg-brand-blue text-white py-2 rounded-lg text-sm font-bold hover:bg-sky-600 transition">Save</button>
                            <button type="button" onclick="renderUserProfile(false)" class="flex-1 border border-gray-300 text-gray-600 py-2 rounded-lg text-sm font-bold hover:bg-gray-50 transition">Cancel</button>
                        </div>
                    </form>
                `;
            } else {
profileHTML = `
                    <div class="w-24 h-24 rounded-full bg-brand-blue text-white flex items-center justify-center text-3xl font-bold mx-auto mb-4">${user.name.charAt(0).toUpperCase()}</div>
                    <h3 class="text-xl font-bold text-brand-dark">${user.name}</h3>
                    <p class="text-gray-500 text-sm mb-4">${user.email}</p>
                    <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-blue-50 text-brand-blue">Guest Member</span>
                    
                    <button onclick="renderUserProfile(true)" class="mt-6 w-full border border-brand-blue text-brand-blue hover:bg-blue-50 py-2 rounded-lg transition font-medium text-sm">
                        <i class="fas fa-user-edit mr-2"></i> Edit Profile
                    </button>
                    
                    <hr class="my-6 border-gray-100">
                    <button onclick="window.location.href = '../index.php'" class="w-full border border-gray-300 text-gray-600 hover:bg-gray-50 py-2 rounded-lg transition font-medium text-sm">
                        <i class="fas fa-home mr-2"></i> Back to Home
                    </button>
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
                            let cancelBtn = b.status === 'Pending' ? `<button onclick="cancelMyBooking('${b.id}')" class="text-xs bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 px-3 py-1.5 rounded mt-2 inline-block transition">Cancel Booking</button>` : '';
                            let safeRoomName = b.roomName ? b.roomName.replace(/'/g, "\\'") : '';
                            let reportBtn = `<button onclick="openReportModal('${b.id}', '${safeRoomName}')" class="text-xs bg-yellow-50 text-yellow-700 hover:bg-yellow-100 border border-yellow-200 px-3 py-1.5 rounded mt-2 inline-block ml-2 transition"><i class="fas fa-exclamation-circle"></i> Report Issue</button>`;
                            return `
                                <div class="border border-gray-100 p-4 rounded-xl flex flex-col md:flex-row justify-between md:items-center gap-4 hover:bg-gray-50 transition">
                                    <div>
                                        <div class="flex items-center gap-3 mb-1">
                                            <h4 class="font-bold text-brand-dark text-lg">${b.roomName}</h4>
                                            <span class="px-2 py-1 rounded text-xs font-bold ${statusColor}">${b.status}</span>
                                        </div>
                                        <p class="text-sm text-gray-500"><i class="far fa-calendar-alt mr-1"></i> ${b.checkIn} to ${b.checkOut} (${b.nights} nights)</p>
                                        <p class="text-xs text-gray-400 mt-1">Ref: ${b.id} • Booked on ${b.dateBooked}</p>
                                        ${cancelBtn}
                                        ${reportBtn}
                                    </div>
                                    <div class="text-left md:text-right">
                                        <p class="text-xs text-gray-500">Total Amount</p>
                                        <p class="font-bold text-xl text-brand-blue">$${b.total}</p>
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

        // Save profile changes to database
function saveProfile(e) {
            e.preventDefault();
            const newName = document.getElementById('edit-name').value.trim();
            const newEmail = document.getElementById('edit-email').value.trim();
            const newPass = document.getElementById('edit-pass').value.trim();

            // First, update basic profile info to database
            fetch('../actions/profile_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: appState.currentUser.id,
                    name: newName,
                    email: newEmail
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Update local state
                    appState.currentUser.name = newName;
                    appState.currentUser.email = newEmail;
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
                alert("Profile updated successfully!");
                updateAuthUI();
                renderUserProfile(false);
            })
            .catch(err => {
                console.error('Profile update error:', err);
                alert("Failed to update profile: " + err.message);
            });
        }

        // Cancel booking - saves to database
        function cancelMyBooking(bookingId) {
            if(confirm("Are you sure you want to cancel this reservation?")) {
                fetch('../actions/bookings_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id: bookingId,
                        status: 'Cancelled'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        // Update local state
                        const idx = appState.bookings.findIndex(b => b.id === bookingId);
                        if(idx > -1) {
                            appState.bookings[idx].status = 'Cancelled';
                        }
                        saveState();
                        renderUserProfile();
                        alert("Booking cancelled successfully!");
                    } else {
                        alert("Failed to cancel booking: " + data.message);
                    }
                })
                .catch(err => {
                    console.error('Cancel booking error:', err);
                    alert("Failed to cancel booking: " + err.message);
                });
            }
        }

        // Log out the current user
        function logout() {
            appState.currentUser = null;
            saveState(); // Clears sessionStorage
        
            window.location.href = '../logout.php';
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
                    alert('Feedback submitted successfully. Thank you!');
                    closeReportModal();
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => console.error(err));
        }
