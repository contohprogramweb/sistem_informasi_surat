<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Log semua error critical ke storage/logs/error.log
            if ($this->isCritical($e)) {
                \Log::critical($e->getMessage(), [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'url' => request()->fullUrl(),
                    'user_id' => auth()->id(),
                    'ip' => request()->ip(),
                ]);
            }
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Handle specific exception types with user-friendly pages
        if ($e instanceof ModelNotFoundException) {
            return $this->renderCustomErrorPage(404, 'Data tidak ditemukan', 'Data yang Anda cari tidak ditemukan dalam sistem.');
        }

        if ($e instanceof NotFoundHttpException) {
            return $this->renderCustomErrorPage(404, 'Halaman tidak ditemukan', 'Halaman yang Anda akses tidak ditemukan atau telah dipindahkan.');
        }

        if ($e instanceof AccessDeniedHttpException) {
            return $this->renderCustomErrorPage(403, 'Akses ditolak', 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        if ($e instanceof AuthenticationException) {
            return redirect()->route('login');
        }

        if ($e instanceof TokenMismatchException) {
            return redirect()->back()->with('error', 'Sesi Anda telah kadaluarsa. Silakan coba lagi.');
        }

        if ($e instanceof ValidationException) {
            return parent::render($request, $e);
        }

        // Handle 500 errors
        if ($this->isProduction() && $e instanceof \Exception) {
            \Log::error('Server Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'url' => request()->fullUrl(),
            ]);
            
            return $this->renderCustomErrorPage(500, 'Terjadi kesalahan server', 'Maaf, terjadi kesalahan pada server kami. Tim teknis telah diberitahu.');
        }

        return parent::render($request, $e);
    }

    /**
     * Render custom error page with Bootstrap styling
     */
    private function renderCustomErrorPage(int $statusCode, string $title, string $message)
    {
        return response()->view('errors.custom', [
            'code' => $statusCode,
            'title' => $title,
            'message' => $message,
        ], $statusCode);
    }

    /**
     * Check if exception is critical
     */
    private function isCritical(Throwable $e): bool
    {
        $criticalClasses = [
            \Error::class,
            \ParseError::class,
            \TypeError::class,
            \Illuminate\Database\QueryException::class,
        ];

        foreach ($criticalClasses as $class) {
            if ($e instanceof $class) {
                return true;
            }
        }

        // Check message for critical keywords
        $criticalKeywords = ['fatal', 'critical', 'database', 'connection'];
        $message = strtolower($e->getMessage());
        
        foreach ($criticalKeywords as $keyword) {
            if (strpos($message, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if in production environment
     */
    private function isProduction(): bool
    {
        return app()->environment('production');
    }
}
