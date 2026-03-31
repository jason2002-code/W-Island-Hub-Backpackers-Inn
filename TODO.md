# Dark/Light Mode Removal - COMPLETED

All theme toggle buttons removed from navbar and admin dashboard.
Tailwind darkMode config removed.
Theme JS logic fully removed from script.js and admin/dashboard.php.

Site now runs strictly in light mode. All Tailwind `dark:` utility classes and theme-transition animations have been completely scrubbed from the codebase.

## Verification Results:
Use browser devtools to confirm:
- No `onclick="toggleTheme()"` anywhere
- No localStorage 'color-theme' checks
- Navbar auth UI clean (profile/logout or login/signup only)
- Admin top bar has search + avatar only

Task complete.
