<?php

use App\Http\Controllers\KehamilanController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\HistoryKehamilanController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PostCategoryController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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

    /**
     * Register
     *
     * Register new user. If everything is okay, you'll get a 200 OK response.
     *
     * Otherwise, the request will fail with a 400 error, and a response listing the failed services.
     *
     */

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
                ->name('password.email');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
                ->name('password.update');

    Route::middleware('auth:sanctum')->post('/fcm_token/{fcmToken}', [UserController::class,'setFcmToken']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('post')->group(function(){

    Route::get('/',[PostController::class,'index'])->middleware('auth:sanctum')->name('post.index');

    Route::get('/category/{slug}',[PostController::class,'indexByCategory'])->name('post.category');

    Route::get('/{slug}',[PostController::class,'show'])->name('post.show');

});

Route::prefix('page')->group(function(){

    Route::get('/',[PageController::class,'index'])->middleware('auth:sanctum')->name('page.index');
    Route::get('/{slug}',[PageController::class,'show'])->name('page.show');

});

Route::prefix('media')->group(function(){

    Route::get('/',[PostController::class,'index'])->middleware('auth:sanctum')->name('media.index');

    Route::get('/category/{slug}',[PostController::class,'indexByCategory'])->name('media.category');

    Route::get('/{slug}',[PostController::class,'show'])->name('media.show');

});

Route::prefix('kehamilan')->middleware('auth:sanctum')->group(function(){

    Route::get('/',[KehamilanController::class,'index'])->name('kehamilan.index');

    Route::post('/',[KehamilanController::class,'store'])->name('kehamilan.store');

    Route::put('/{uidKehamilan}',[KehamilanController::class,'update'])->name('kehamilan.update');

    Route::delete('/{uidKehamilan}',[KehamilanController::class,'destroy'])->name('kehamilan.destroy');


});

Route::prefix('history_kehamilan')->middleware('auth:sanctum')->group(function(){

    Route::get('/{uuidKehamilan}',[HistoryKehamilanController::class,'index'])->name('history_kehamilan.index');

    Route::post('/{uuidKehamilan}',[HistoryKehamilanController::class,'store'])->name('history_kehamilan.store');

   // Route::put('/{uidKehamilan}',[KehamilanController::class,'update'])->name('history_kehamilan.update');

    Route::delete('/{uidKehamilan}',[HistoryKehamilanController::class,'destroy'])->name('history_kehamilan.destroy');


});

Route::prefix('post_category')->group(function(){
    Route::get('/',[PostCategoryController::class,'index'])->name('post_category.index');
});


Route::prefix('init')->group(function(){
    Route::get('/storage',function(){
        Artisan::call('storage:link');
    });

});



