<?php
// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', 'http://localhost/ezeria-library/auth/google-callback.php');

// Microsoft OAuth Configuration
define('MICROSOFT_CLIENT_ID', 'YOUR_MICROSOFT_CLIENT_ID');
define('MICROSOFT_CLIENT_SECRET', 'YOUR_MICROSOFT_CLIENT_SECRET');
define('MICROSOFT_REDIRECT_URI', 'http://localhost/ezeria-library/auth/microsoft-callback.php');
define('MICROSOFT_TENANT', 'common'); // or 'organizations', 'consumers'
?>