<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DebugController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AdminController;

use App\Http\Controllers\UserController;
 use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyAuthenticatedSessionController;

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RequestListController;

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
    return view('auth.login'); 
})->name('admin.login');


// 一般ユーザーのログイン処理
 Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
// 管理者のログイン処理
 Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login.store');
// ユーザー用ログアウト
 Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

// 管理者用ログアウト
 Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
 
// 一般ユーザーのダッシュボード（認証が必要）
Route::middleware(['auth:web'])->group(function () {
     Route::get('/attendance', [UserController::class, 'index'])->name('user.attendance');
    Route::get('/attendance/list' ,[UserController::class,'showList'])->name('user.attendance.list');
    Route::get('/attendance/{id}' ,[UserController::class,'detail'])->name('user.attendance.detail');
    Route::get('/attendance/date/{date}', [UserController::class, 'detailByDate'])->name('user.attendance.detailByDate');
    

    Route::post('/attendance/clock-in',[AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/clock-out',[AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    Route::post('/attendance/break-start',[AttendanceController::class,'breakStart'])->name('attendance.breakStart');
    Route::post('/attendance/break-end',[AttendanceController::class, 'breakEnd'])->name('attendance.breakEnd');

     Route::post('/attendance/{id}/edit-request', [AttendanceController::class, 'update'])->name('attendance.update');
   
     
      Route::get('/attendance/edit-detail/{date}',[UserController::class,'editDetail'])->name('attendance.editDetail');
      
      

});
   
 




// 管理者専用ページ（認証が必要）
Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::get('/attendance/list', [AdminController::class, 'index'])->name('admin.attendance.list');
    Route::get('/staff/list',[AdminController::class, 'staffList'])->name('admin.staff.list');
 Route::get('/stamp_correction_request/list', [RequestListController::class, 'adminRequestList'])->name('admin.stamp_correction_request.list');   
    
    
});

// 一般ユーザー用ルート



Route::middleware(['auth:web'])->get('/stamp_correction_request/list', [RequestListController::class, 'userRequestList'])->name('user.stamp_correction_request.list');








Route::get('/preview/{viewName}', [DebugController::class, 'show']);