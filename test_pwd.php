<?php
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make("Illuminate\Contracts\Console\Kernel")->bootstrap();
$users = \Illuminate\Support\Facades\DB::table("users")->select("user_id", "password", "plain_password")->take(5)->get();
print_r($users);

