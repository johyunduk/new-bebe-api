<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (HttpException $exception) {
            return response()->json([
                'result' => '9999',
                'status' => $exception->getStatusCode(),
                'message' => $exception->getMessage()
            ], $exception->getStatusCode());
        });

        $this->renderable(function (\Exception $exception) {
            return response()->json([
                'result' => '9999',
                'status' => 500,
                'message' => 'Internal Server Error Occurred...'
            ], 500);
        });
    }
}
