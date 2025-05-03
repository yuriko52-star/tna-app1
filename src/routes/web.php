<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AdminController;

use App\Http\Controllers\UserController;
 use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyAuthenticatedSessionController;

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\RequestListController;
use App\Http\Controllers\EmailVerificationController;

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


Route::get('/admin/login', function () {
    return view('auth.login'); 
})->name('admin.login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
Route::post('/admin/login', [AuthenticatedSessionController::class, 'store'])->name('admin.login.store');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
Route::post('/admin/logout', [AuthenticatedSessionController::class, 'destroy'])->name('admin.logout');
 
Route::middleware(['auth'])->group(function () {
   Route::get('/email/verify', [EmailVerificationController::class, 'show'])
        ->name('verification.notice'); 
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/email/resend', [EmailVerificationController::class, 'resend'])
        ->middleware(['throttle:6,1'])
        ->name('verification.resend');
});

// 一般ユーザーのダッシュボード（認証が必要）
Route::middleware(['auth:web', 'verified'])->group(function () {
     Route::get('/attendance', [UserController::class, 'index'])->name('user.attendance');
    Route::get('/attendance/list' ,[UserController::class,'showList'])->name('user.attendance.list');
    Route::get('/attendance/date/{date}', [UserController::class, 'detailByDate'])->name('user.attendance.detailByDate');
    Route::get('/attendance/{id}' ,[UserController::class,'detail'])->name('user.attendance.detail');
    Route::post('/attendance/clock-in',[AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/clock-out',[AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    Route::post('/attendance/break-start',[AttendanceController::class,'breakStart'])->name('attendance.breakStart');
    Route::post('/attendance/break-end',[AttendanceController::class, 'breakEnd'])->name('attendance.breakEnd');

     Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
     Route::patch('/attendance/{id}/edit-request', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::get('/attendance/edit-detail/{date}',[UserController::class,'editDetail'])->name('attendance.editDetail');

    Route::get('/stamp_correction_request/list',[RequestListController::class,'userRequestList'])->name('user.stamp_correction_request.list');
    
});



   
// 管理者専用ページ（認証が必要）
Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::get('/attendance/list', [AdminController::class, 'index'])->name('admin.attendance.index');
    Route::get('/staff/list',[AdminController::class, 'staffList'])->name('admin.staff.list');
    
    Route::get('/stamp_correction_request/list', [RequestListController::class, 'adminRequestList'])->name('admin.stamp_correction_request.list'); 
    Route::get('/attendance/staff/{id}',[AdminController::class,'showList'])->name('admin.attendance.staff');  
    Route::get('/attendance/staff/{id}/csv',[AdminController::class,'downloadCsv'])->name('admin.attendance.downloadCsv');
    Route::get('/attendance/detail/{id}/{date}',[AdminController::class ,'detailByDateForAdmin'])->name('admin.attendance.detailByDateForAdmin');
    Route::get('/attendance/{id}',[AdminController::class,'detailForAdmin'])->name('admin.attendance.detail');
     Route::post('/attendance-edit/{id}/approve',[AdminController::class,'approveAttendanceEdit'])->name('admin.attendanceEdit.approve');
     Route::post('/break-edit/{id}/approve',[AdminController::class,'approveBreakEdit'])->name('admin.breakEdit.approve');
     Route::post('/attendance/store/{id}', [AdminController::class, 'store'])->name('admin.attendance.store.new');
     Route::patch('/attendance/{id}/edit-request', [AdminController::class, 'update'])->name('admin.attendance.update');
    
    
    Route::get('/stamp_correction_request/approve', [AdminController::class, 'approveOnlyBreak'])
    ->name('admin.approveOnlyBreak');
     Route::get('/stamp_correction_request/approve/{attendance_correct_request}',[AdminController::class, 'approvePage'])->name('admin.approvePage');
    
    
});













// Route::get('/preview/{viewName}', [DebugController::class, 'show']);