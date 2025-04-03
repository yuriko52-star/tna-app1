<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
 use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyAuthenticatedSessionController;

use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
// 一般ユーザーのログインページ
Route::get('/login', function () {
    return view('auth.login');
})->name('login');
// 管理者のログインページ
Route::get('/admin/login', function () {
    return view('auth.login'); // 共通ビューを使う
})->name('admin.login');

// 一般ユーザー用ログイン
// Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
// 一般ユーザーのログイン処理
 Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
// 管理者のログイン処理
 Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login.store');

// Route::middleware(['auth'])->get('/attendance', [UserController::class, 'attendance'])->name('attendance.record');
// ユーザー用ログアウト
 Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
//  エラーになったが設定
// 管理者用ログアウト
 Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
 //  エラーになったが設定
// 一般ユーザーのダッシュボード（認証が必要）
Route::middleware(['auth:web'])->group(function () {
    Route::get('/attendance', [UserController::class, 'index'])->name('user.attendance');
});
// 管理者専用ページ（認証が必要）
Route::middleware(['auth:web', 'admin'])->group(function () {
    Route::get('admin/attendance/list', [AdminController::class, 'index'])->name('admin.attendance.list');
});

// 管理者用ログイン
// Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');


/*Route::middleware(['auth', 'admin'])->get('/attendance/list', [AdminController::class, 'index'])->name('attendance.list');
*/


// ログアウト
/* Route::post('/logout', function (Request $request) {
    // Auth::logout();
    return redirect('/login');
})->name('logout');
*/

/*Route::post('/admin/logout', function (Request $request) {
    Auth::logout();
    return redirect('/admin/login');
})->name('admin.logout');
*/



// Route::get('/attendance',[AttendanceController::class, 'index']);
Route::get('/preview/{viewName}', [DebugController::class, 'show']);