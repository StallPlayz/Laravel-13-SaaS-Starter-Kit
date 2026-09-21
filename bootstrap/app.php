<?php

use App\Http\Middleware\EnforceStrictRoleRouting;
use App\Http\Middleware\EnforceWorkspaceSuspension;
use App\Http\Middleware\EnsureActiveWorkspace;
use App\Http\Middleware\EnsureGhostModeIsReadOnly;
use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\LogImpersonationActivity;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Psr\Log\LogLevel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        channels: __DIR__.'/../routes/channels.php',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->web(append: [
            HandleAppearance::class,
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
            EnsureActiveWorkspace::class,
            EnsureGhostModeIsReadOnly::class,
            EnforceWorkspaceSuspension::class,
            LogImpersonationActivity::class,
            EnforceStrictRoleRouting::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->level(FatalError::class, LogLevel::EMERGENCY);
        $exceptions->level(QueryException::class, LogLevel::CRITICAL);
        $exceptions->level(PDOException::class, LogLevel::CRITICAL);
        $exceptions->level(ConnectionException::class, LogLevel::ALERT);
        $exceptions->level(TokenMismatchException::class, LogLevel::WARNING);
        $exceptions->level(ThrottleRequestsException::class, LogLevel::WARNING);
        $exceptions->level(AuthenticationException::class, LogLevel::NOTICE);
        $exceptions->level(NotFoundHttpException::class, LogLevel::INFO);
    })->create();
