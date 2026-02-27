  // Initialize State from LocalStorage or Defaults
        let appState = {
            users: JSON.parse(localStorage.getItem('w_users')) || [],
            currentUser: JSON.parse(sessionStorage.getItem('w_currentUser')) || null,
            bookings: JSON.parse(localStorage.getItem('w_bookings')) || [],
            rooms: JSON.parse(localStorage.getItem('w_rooms')) || defaultRooms
        };

        const saveState = () => {
            localStorage.setItem('w_users', JSON.stringify(appState.users));
            localStorage.setItem('w_bookings', JSON.stringify(appState.bookings));
            localStorage.setItem('w_rooms', JSON.stringify(appState.rooms));
            if(appState.currentUser) sessionStorage.setItem('w_currentUser', JSON.stringify(appState.currentUser));
            else sessionStorage.removeItem('w_currentUser');
        };