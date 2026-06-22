<?php
header('Content-Type: text/plain');
echo "Laravel Deployed Files Diagnostics\n";
echo "===================================\n";
echo "Current File Location: " . __FILE__ . "\n";
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "App Env: " . (getenv('APP_ENV') ?: 'Not Set') . "\n";
echo "DB Connection: " . (getenv('DB_CONNECTION') ?: 'Not Set') . "\n";
echo "Database File: " . (getenv('DB_DATABASE') ?: 'Not Set') . "\n";

echo "\nFiles in /home/site/wwwroot:\n";
if (is_dir('/home/site/wwwroot')) {
    print_r(scandir('/home/site/wwwroot'));
} else {
    echo "Directory /home/site/wwwroot does not exist!\n";
}

echo "\nFiles in current directory:\n";
print_r(scandir(__DIR__));
?>
