<?php

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Admin\PlatformHealthController;
use App\Http\Controllers\Admin\SystemLogController as AdminLogController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WorkspaceController as AdminWorkspaceController;
use App\Http\Controllers\GeoController;
use App\Http\Controllers\OtpLoginController;
use App\Http\Controllers\PublicWorkspaceController;
use App\Http\Controllers\SupportPinController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceInvitationController;
use App\Http\Controllers\WorkspaceMemberController;
use App\Http\Middleware\EnsureSuperAdmin;
use App\Http\Requests\Auth\RegisterRequest;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Responses\RegisterResponse;

Route::inertia('/', 'Welcome')->name('home');

Route::get('/invitations/{token}', [WorkspaceInvitationController::class, 'accept'])
    ->name('invitations.accept');

Route::middleware('guest')->group(function () {
    Route::post('/invitations/{token}', [WorkspaceInvitationController::class, 'register'])
        ->middleware([HandlePrecognitiveRequests::class])
        ->name('invitations.register');
    Route::post('/login/otp/request', [OtpLoginController::class, 'requestOtp'])->name('otp.request');
    Route::post('/login/otp/verify', [OtpLoginController::class, 'verifyOtp'])->name('otp.verify');
});

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return inertia('auth/VerifySuccess');
})->middleware(['auth', 'signed'])->name('verification.verify');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::post('/workspaces/switch', [WorkspaceController::class, 'switch'])->name('workspaces.switch');

    Route::get('/workspaces/create', [WorkspaceController::class, 'create'])->name('workspaces.create');
    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');
    Route::get('/workspaces/{workspace}/settings', [WorkspaceController::class, 'settings'])->name('workspaces.settings');
    Route::put('/workspaces/{workspace}', [WorkspaceController::class, 'update'])->name('workspaces.update');
    Route::post('/workspaces/{workspace}/invitations', [WorkspaceInvitationController::class, 'store'])->name('workspaces.invitations.store');
    Route::get('/directory', [WorkspaceMemberController::class, 'index'])->name('directory');
    Route::post('/user/support-pin', [SupportPinController::class, 'store'])->name('user.support-pin.store');
    Route::post('/admin/impersonation/leave', [AdminUserController::class, 'leaveImpersonation'])->name('admin.impersonation.leave');
});

Route::middleware(['auth', 'verified', EnsureSuperAdmin::class])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [PlatformHealthController::class, 'dashboard'])->name('dashboard');
        Route::get('/workspaces', [AdminWorkspaceController::class, 'index'])->name('workspaces.index');
        Route::patch('/workspaces/{workspace}/suspend', [AdminWorkspaceController::class, 'toggleSuspension'])->name('workspaces.suspend');
        Route::post('/workspaces/{workspace}/ghost', [AdminWorkspaceController::class, 'enterGhostMode'])->name('workspaces.ghost.enter');
        Route::post('/workspaces/ghost/exit', [AdminWorkspaceController::class, 'exitGhostMode'])->name('workspaces.ghost.exit');
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::post('/users/{user}/impersonate', [AdminUserController::class, 'impersonate'])->name('users.impersonate');
        Route::get('/logs', [AdminLogController::class, 'index'])->name('logs.index');
        Route::get('/logs/download/{date}', [AdminLogController::class, 'download'])->name('logs.download');
    });

Route::post('/register', function (RegisterRequest $request, CreateNewUser $creator) {
    event(new Registered($user = $creator->create($request->all())));
    Auth::login($user);

    return app(RegisterResponse::class);
})->middleware(['guest', HandlePrecognitiveRequests::class])->name('register.store');

Route::get('/api/user/verified-status', function () {
    return response()->json(['verified' => Auth::user()?->hasVerifiedEmail()]);
})->middleware('auth');

Route::prefix('api/geo')->group(function () {
    Route::get('/countries', [GeoController::class, 'countries']);
    Route::get('/countries/{countryId}/regions', [GeoController::class, 'regions']);
    Route::get('/cities', [GeoController::class, 'cities']);
});

require __DIR__.'/settings.php';

Route::get('/{workspace:slug}', [PublicWorkspaceController::class, 'show'])->name('workspace.public');
