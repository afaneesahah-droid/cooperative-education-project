<?php
/**
 * Auto upload script for 406665005.site.yru.ac.th
 */

$ftp_server = "host.site.yru.ac.th";
$ftp_user   = "s406665005";
$ftp_pass   = "Afanee2548";
$remote_base = "/htdocs/406665005.site.yru.ac.th";

echo "Connecting to FTP server: $ftp_server...\n";
$conn = @ftp_connect($ftp_server, 21, 15);
if (!$conn) {
    die("FAILED: Could not connect to $ftp_server\n");
}

if (!@ftp_login($conn, $ftp_user, $ftp_pass)) {
    ftp_close($conn);
    die("FAILED: Invalid credentials for $ftp_user\n");
}

ftp_pasv($conn, true);
echo "SUCCESS: Logged in to $ftp_server\n";

$files = [
    'routes/web.php',
    'app/Models/Device.php',
    'app/Models/RepairRequest.php',
    'app/Http/Controllers/RepairRequestController.php',
    'database/migrations/2026_10_13_000002_add_image_to_repair_requests_table.php',
    'resources/views/dashboards/head.blade.php',
    'resources/views/dashboards/head/header.blade.php',
    'resources/views/dashboards/head/modals.blade.php',
    'resources/views/dashboards/head/partials/job_row.blade.php',
    'resources/views/dashboards/head/scripts.blade.php',
    'resources/views/dashboards/executive.blade.php',
    'resources/views/dashboards/executive/header.blade.php',
    'resources/views/dashboards/executive/modals.blade.php',
    'resources/views/dashboards/executive/scripts.blade.php',
    'resources/views/dashboards/technician.blade.php',
    'resources/views/dashboards/technician/header.blade.php',
    'resources/views/dashboards/technician/scripts.blade.php',
    'resources/views/dashboards/technician/partials/budget_row.blade.php',
    'resources/views/dashboards/teacher.blade.php',
    'resources/views/dashboards/teacher/header.blade.php',
    'resources/views/dashboards/teacher/scripts.blade.php',
    'resources/views/dashboards/teacher/modals.blade.php'
];

// If specific file passed as argument
if ($argc > 1) {
    $files = array_slice($argv, 1);
}

foreach ($files as $relPath) {
    $localPath = __DIR__ . '/' . str_replace('\\', '/', $relPath);
    if (!file_exists($localPath)) {
        echo "Skip (Not found): $localPath\n";
        continue;
    }
    
    $remotePath = $remote_base . '/' . str_replace('\\', '/', $relPath);
    
    // Ensure remote directory exists
    $parts = explode('/', dirname($remotePath));
    $current = '';
    foreach ($parts as $p) {
        if ($p === '') {
            $current .= '/';
            continue;
        }
        $current .= ($current === '/' ? '' : '/') . $p;
        @ftp_mkdir($conn, $current);
    }
    
    echo "Uploading $relPath (" . filesize($localPath) . " bytes)... ";
    if (ftp_put($conn, $remotePath, $localPath, FTP_BINARY)) {
        echo "SUCCESS\n";
    } else {
        echo "FAILED\n";
    }
}

// Clear compiled blade view cache
$cacheDir = $remote_base . "/storage/framework/views";
$cachedFiles = @ftp_nlist($conn, $cacheDir);
if ($cachedFiles) {
    $cleared = 0;
    foreach ($cachedFiles as $cf) {
        $base = basename($cf);
        if ($base !== '.' && $base !== '..' && $base !== '.gitignore') {
            if (@ftp_delete($conn, $cf)) {
                $cleared++;
            }
        }
    }
    echo "Cleared $cleared compiled view cache files on remote server.\n";
}

ftp_close($conn);
echo "Deployment finished!\n";
