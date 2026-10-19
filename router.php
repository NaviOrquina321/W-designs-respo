<?php
// Router for PHP's built-in dev server:  php -S localhost:8000 router.php
// (Apache/XAMPP uses .htaccess for the same protection.)
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$blocked = preg_match('#^/(data|docs|tests|test-results|\.git)(/|$)#i', $path)
    || preg_match('#/(ai_config|config\.local|config\.sample|ai_config\.sample|router)\.php$#i', $path)
    || preg_match('#\.(db|sqlite|sqlite3|sql|log|md|json)$#i', $path);
if ($blocked) {
    http_response_code(403);
    echo 'Forbidden';
    return true;
}
return false; // let the built-in server handle everything else normally
