<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IRSController;
use App\Http\Controllers\KHSController;
use App\Http\Controllers\PKLController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\AddUserController;
use App\Http\Controllers\SkripsiController;
use App\Http\Controllers\WilayahController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\ProgressMhsContoller;
use App\Http\Controllers\EditProfileController;
use App\Http\Controllers\EntryProgressController;
use App\Http\Controllers\ManajemenUserController;
use App\Http\Controllers\VerifikasiBerkasController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web Routes for your application. These
| Routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Home 
Route::get('/', [HomeController::class, 'index'])->name('home');

// Auth (login & logout)
Route::get('/login', [AuthController::class, 'index'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// Middleware auth
Route::group(['middleware' => ['auth']], function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('editprofile')->name('dashboard');

    // Fiture Operator
    Route::group(['middleware' => ['operator']], function () {
        // add user & manajamen user (CRUD User)
        Route::get('/operator/add_user', [AddUserController::class, 'index'])->name('user_add');
        Route::get('/operator/manajemen_user', [ManajemenUserController::class, 'index'])->name('user_manajemen');
        Route::resource('/operator/mahasiswa', MahasiswaController::class);
        Route::resource('/operator/dosen', DosenController::class);
    });

    // Fiture Department
    Route::group(['middleware' => ['department']], function () {
        // progress studi mahasiswa
        Route::get('/department/progress_studi_mahasiswa', [ProgressMhsContoller::class, 'department']);
        // data mahasiswa
        Route::get('/department/data_mahasiswa', [MahasiswaController::class, 'data_mahasiswa']);
        // data dosen
        Route::get('/department/data_dosen', [DosenController::class, 'data_dosen']);
    });

    // Fiture Dosen
    Route::group(['middleware' => ['dosen']], function () {
        // progress studi mahasiswa
        Route::get('/dosen/progress_studi_mahasiswa', [ProgressMhsContoller::class, 'dosen']);
        Route::get('/dosen/progress_studi_mahasiswa/detail', [ProgressMhsContoller::class, 'show'])->name('progress_detail');
        Route::get('/dosen/progress_studi_mahasiswa/semester', [ProgressMhsContoller::class, 'show_semester'])->name('progress_detail_semester');

        // verifikasi berkas mahasiswa
        Route::get('/dosen/verifikasi_berkas_mahasiswa', [VerifikasiBerkasController::class, 'index']);
        Route::get('/dosen/verifikasi_berkas_mahasiswa/detail', [VerifikasiBerkasController::class, 'show'])->name('berkas_detail');
        Route::post('/dosen/verifikasi_berkas_mahasiswa/update', [VerifikasiBerkasController::class, 'update'])->name('verifikasi_update');

        // data mahasiswa
        Route::get('/dosen/data_mahasiswa', [MahasiswaController::class, 'data_mahasiswa']);
        Route::post('/dosen/data_mahasiswa/detail', [MahasiswaController::class, 'data_mahasiswa_detail'])->name('data_mahasiswa_detail');

        // data mahasiswa pkl
        Route::get('/dosen/data_mahasiswa_pkl', [MahasiswaController::class, 'data_pkl']);
        // data mahasiswa skripsi
        Route::get('/dosen/data_mahasiswa_skripsi', [MahasiswaController::class, 'data_skripi']);
    });

    // Fiture Mahasiswa
    Route::group(['middleware' => ['mahasiswa', 'editprofile']], function () {
        // entry progress
        Route::get('/mahasiswa/entry', [EntryProgressController::class, 'index'])->middleware('entry_progress');
        Route::post('/mahasiswa/entry', [EntryProgressController::class, 'entry_progress'])->name('entry_progress');
        // irs
        Route::resource('/mahasiswa/irs', IRSController::class);
        Route::get('/mahasiswa/entry/irs', [IRSController::class, 'index'])->middleware('irs');
        Route::get('/mahasiswa/data/irs', [IRSController::class, 'data'])->name('data_irs');
        Route::get('/mahasiswa/irs/{semester}/{nim}/edit', [IRSController::class, 'edit'])->name('irs.edit');
        // khs
        Route::resource('/mahasiswa/khs', KHSController::class);
        Route::get('/mahasiswa/entry/khs', [KHSController::class, 'index'])->middleware('khs');
        Route::get('/mahasiswa/data/khs', [KHSController::class, 'data'])->name('data_khs');
        Route::get('/mahasiswa/khs/{semester}/{nim}/edit', [KHSController::class, 'edit'])->name('khs.edit');
        // pkl
        Route::resource('/mahasiswa/pkl', PKLController::class);
        Route::get('/mahasiswa/entry/pkl', [PKLController::class, 'index'])->middleware('pkl');
        Route::get('/mahasiswa/data/pkl', [PKLController::class, 'data'])->name('data_pkl');
        Route::get('/mahasiswa/pkl/{semester}/{nim}/edit', [PKLController::class, 'edit'])->name('pkl.edit');
        // skripsi
        Route::resource('/mahasiswa/skripsi', SkripsiController::class);
        Route::get('/mahasiswa/entry/skripsi', [SkripsiController::class, 'index'])->middleware('skripsi');
        Route::get('/mahasiswa/data/skripsi', [SkripsiController::class, 'data'])->name('data_skripsi');
        Route::get('/mahasiswa/skripsi/{semester}/{nim}/edit', [SkripsiController::class, 'edit'])->name('skripsi.edit');
    });

    // edit profile
    Route::resource('/mahasiswa/edit_profile', EditProfileController::class)->middleware('mahasiswa');

    // Wilayah Indonesia
    Route::get('/wilayah/{provinsi}', [WilayahController::class, 'index'])->name('wilayah');

    // Upload File
    Route::post('/upload', [UploadController::class, 'upload']);
});

// Login & Logout [Done]
// Dashboard [Done]
// Fiture Operator: Add User [Done]
// Fiture Operator: Manajemen User [Done]
// Fiture Operator: CRUD Mahasiswa [Done]
// Fiture Operator: CRUD Dosen [Done]
// Fiture Department: Progress Studi Mahasiswa [Done]
// Fiture Department: Data Mahasiswa [Done]
// Fiture Department: Data Dosen [Done]
// Fiture Dosen: Progress Studi Mahasiswa [Done]
// Fiture Dosen: Verifikasi Berkas Mahasiswa [Done]
// Fiture Dosen: Data Mahasiswa [Done]
// Fiture Dosen: Data Mahasiswa PKL [Done]
// Fiture Dosen: Data Mahsiswa Skripsi [Done]
// Fiture Mahasiswa: edit profile [Done]
// Fiture Mahasiswa: IRS [Done]
// Fiture Mahasiswa: KHS [Done]
// Fiture Mahasiswa: PKL [Done]
// Fiture Mahasiswa: Skripsi [Done]
