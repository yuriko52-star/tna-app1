<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
// use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyAuthenticatedSessionController;

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
// ログイン画面（共通）
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/admin/login', function () {
    return view('auth.login'); // 共通ビューを使う
})->name('admin.login');

// 一般ユーザー用ログイン
// Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::middleware(['auth'])->get('/attendance', [UserController::class, 'attendance'])->name('attendance.record');

// 管理者用ログイン
// Route::get('/admin/login', [AuthenticatedSessionController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store']);

Route::middleware(['auth', 'admin'])->get('/attendance/list', [AdminController::class, 'index'])->name('attendance.list');
// ログアウト
// ユーザー用ログアウト
// Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');エラーになる
Route::post('/logout', function (Request $request) {
    Auth::logout();
    return redirect('/login');
})->name('logout');

// 管理者用ログアウト
// Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');エラーになる
Route::post('/admin/logout', function (Request $request) {
    Auth::logout();
    return redirect('/admin/login');
})->name('admin.logout');




Route::get('/attendance',[AttendanceController::class, 'index']);
Route::get('/preview/{viewName}', [DebugController::class, 'show']);