<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register Spatie Permission middleware
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            
            // Custom middleware untuk role check
            'role.check' => \App\Http\Middleware\RoleMiddleware::class,
        ]);
        
        // Redirect ke login jika unauthorized
        $middleware->redirectGuestsTo('/login');
        
        // Rate limiting sudah dikonfigurasi di AppServiceProvider
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
