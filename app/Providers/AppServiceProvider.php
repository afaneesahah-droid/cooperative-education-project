<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (request()->has('clear_all_cache')) {
            try {
                \Illuminate\Support\Facades\Artisan::call('view:clear');
                \Illuminate\Support\Facades\Artisan::call('route:clear');
                \Illuminate\Support\Facades\Artisan::call('config:clear');
                \Illuminate\Support\Facades\Artisan::call('cache:clear');
                echo "เคลียร์แคชทั้งหมดเรียบร้อยแล้ว! กรุณาลบโค้ดนี้ออกจาก AppServiceProvider.php ภายหลังด้วยครับ";
                exit;
            } catch (\Exception $e) {
                echo "เกิดข้อผิดพลาดในการเคลียร์แคช: " . $e->getMessage();
                exit;
            }
        }

        if (request()->has('check_db')) {
            $users = \Illuminate\Support\Facades\DB::select('SELECT user_id, LENGTH(user_id) as len FROM users');
            dd([
                'users' => $users
            ]);
        }

        // Force HTTPS scheme
        if (!in_array(request()->getHost(), ['127.0.0.1', 'localhost'])) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
