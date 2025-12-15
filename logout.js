document.getElementById('confirmLogoutBtn').addEventListener('click', function() {
    // Display a simple alert message before logging out
    var confirmLogout = confirm("Are you sure you want to log out?");
    
    // If the user clicks "OK", proceed to logout
    if (confirmLogout) {
        // Redirect to logout.php to handle the logout process
        window.location.href = 'logout.php';
    }
});