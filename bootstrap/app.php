<?php

if (!function_exists('highlight_file')) {
    function highlight_file($filename, $return = false) {
        $content = @file_get_contents($filename);
        $highlighted = '<pre><code>' . htmlspecialchars($content ?? '') . '</code></pre>';
        if ($return) return $highlighted;
        echo $highlighted;
        return true;
    }
}

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->registered(function ($app) {
        $app->usePublicPath(path: base_path());
    })
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->validateCsrfTokens(except: [
            'admin/*',
            'head/*',
            'executive/*',
            'technician/*',
            'teacher/*',
            'repair-requests*',
            'repair-requests',
            'budget/deduct'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException $e, $request) {
            if ($request->is('repair-requests*')) {
                return redirect('/dashboard/teacher?tab=status');
            }
            return redirect()->back();
        });
    })->create();
