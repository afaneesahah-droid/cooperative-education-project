<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

echo "<h1>Clearing Laravel Caches...</h1>";
echo "<ul>";
$commands = ['view:clear', 'cache:clear', 'config:clear', 'route:clear'];
foreach ($commands as $command) {
    try {
        \Illuminate\Support\Facades\Artisan::call($command);
        echo "<li>Cache cleared: " . $command . "</li>";
    } catch (\Exception $e) {
        echo "<li>Failed to clear: " . $command . " - " . $e->getMessage() . "</li>";
    }
}
echo "</ul>";
echo "<p>Done! <a href='./'>Go back to website</a></p>";
