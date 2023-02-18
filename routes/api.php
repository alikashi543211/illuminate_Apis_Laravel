<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\ForgetController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Feed\FeedCrudController;
use App\Http\Controllers\Api\Home\FindNearByController;
use App\Http\Controllers\Api\Home\FollowController;
use App\Http\Controllers\Api\Home\ReportController;
use App\Http\Controllers\Api\Home\UserController;
use App\Http\Controllers\Api\User\HomeController;
use App\Http\Controllers\Api\User\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('auth')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('login', 'login');
        Route::post('logout', 'logout');
    });
    Route::controller(RegisterController::class)->group(function () {
        Route::post('register', 'register');
        Route::post('verifyEmailVerificationCode', 'verifyEmailVerificationCode');
        Route::post('verificationCodeResend', 'verificationCodeResend');
    });
    Route::controller(ForgetController::class)->group(function () {
        Route::post('forgetPasswordMail', 'forgetPasswordMail');
        Route::post('verifyResetCode', 'verifyResetCode');
        Route::post('resetPassword', 'resetPassword');
    });
});

Route::middleware(['auth:sanctum'])->prefix('user')->group(function () {
    Route::prefix('profile')->group(function () {
        Route::controller(ProfileController::class)->group(function () {
            Route::get('getUser', 'getUser');
            Route::post('uploadPhoto', 'uploadPhoto');
            Route::post('checkUsernameAvailability', 'checkUsernameAvailability');
            Route::post('storeUsername', 'storeUsername');
            Route::post('storeGeneralData', 'storeGeneralData');
            Route::post('updatePassword', 'updatePassword');
            Route::post('storeLocation', 'storeLocation');
        });
    });
    Route::prefix('home')->group(function () {
        Route::controller(FindNearByController::class)->group(function () {
            Route::post('findNearBy', 'findNearBy');
            Route::post('findNearByFeeds', 'findNearByFeeds');
        });
        Route::controller(UserController::class)->group(function () {
            Route::post('getUsers', 'getUsers');
        });
        Route::controller(FollowController::class)->group(function () {
            Route::post('following', 'following');
        });
        Route::controller(ReportController::class)->group(function () {
            Route::post('feedReport', 'feedReport');
        });
    });
    Route::prefix('feed')->group(function () {
        Route::controller(FeedCrudController::class)->group(function () {
            Route::post('listing', 'listing');
            Route::post('store', 'store');
            Route::post('update', 'update');
            Route::post('delete', 'delete');
        });
    });
});
