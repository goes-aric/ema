<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Akun\AkunController;
use App\Http\Controllers\User\AuthController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Jurnal\JurnalController;
use App\Http\Controllers\Laporan\NeracaController;
use App\Http\Controllers\Laporan\LabaRugiController;
use App\Http\Services\Laporan\PerubahanModalService;
use App\Http\Controllers\Jurnal\DetailJurnalController;
use App\Http\Controllers\Pembelian\PembelianController;
use App\Http\Controllers\Penjualan\PenjualanController;
use App\Http\Controllers\Laporan\PerubahanModalController;

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

/* REGISTER & LOGIN (AUTH) */
Route::controller(AuthController::class)->group(function(){
    Route::post('/auth/register', 'register')->name('auth.register');
    Route::post('/auth/login', 'login')->name('auth.login');
});

Route::middleware(['auth:api'])->group(function(){
    /* BAGAN AKUN */
    Route::controller(AkunController::class)->group(function(){
        Route::get('/akun/options', 'fetchDataOptions')->name('akun.fetchDataOptions');
        Route::get('/akun', 'index')->name('akun.index');
        Route::post('/akun', 'store')->name('akun.store');
        Route::get('/akun/{id}', 'show')->name('akun.show');
        Route::put('/akun/{id}', 'update')->name('akun.update');
        Route::delete('/akun/{id}', 'destroy')->name('akun.destroy');
        Route::delete('/akun', 'destroyMultiple')->name('akun.destroyMultiple');
    });

    /* PEMBELIAN */
    Route::controller(PembelianController::class)->group(function(){
        Route::get('/pembelian/charts', 'charts')->name('pembelian.charts');
        Route::get('/pembelian/all', 'list')->name('pembelian.list');
        Route::get('/pembelian', 'index')->name('pembelian.index');
        Route::post('/pembelian', 'store')->name('pembelian.store');
        Route::get('/pembelian/{id}', 'show')->name('pembelian.show');
        Route::put('/pembelian/{id}', 'update')->name('pembelian.update');
        Route::delete('/pembelian/{id}', 'destroy')->name('pembelian.destroy');
        Route::delete('/pembelian', 'destroyMultiple')->name('pembelian.destroyMultiple');
    });

    /* PENJUALAN */
    Route::controller(PenjualanController::class)->group(function(){
        Route::get('/penjualan/charts', 'charts')->name('penjualan.charts');
        Route::get('/penjualan/all', 'list')->name('penjualan.list');
        Route::get('/penjualan', 'index')->name('penjualan.index');
        Route::post('/penjualan', 'store')->name('penjualan.store');
        Route::get('/penjualan/{id}', 'show')->name('penjualan.show');
        Route::put('/penjualan/{id}', 'update')->name('penjualan.update');
        Route::delete('/penjualan/{id}', 'destroy')->name('penjualan.destroy');
        Route::delete('/penjualan', 'destroyMultiple')->name('penjualan.destroyMultiple');
    });

    /* JURNAL UMUM */
    Route::controller(JurnalController::class)->group(function(){
        Route::get('/jurnal/all', 'list')->name('jurnal.list');
        Route::get('/jurnal', 'index')->name('jurnal.index');
        Route::post('/jurnal', 'store')->name('jurnal.store');
        Route::get('/jurnal/{id}', 'show')->name('jurnal.show');
        Route::put('/jurnal/{id}', 'update')->name('jurnal.update');
        Route::delete('/jurnal/{id}', 'destroy')->name('jurnal.destroy');
        Route::delete('/jurnal', 'destroyMultiple')->name('jurnal.destroyMultiple');
    });

    /* DETAIL JURNAL UMUM */
    Route::controller(DetailJurnalController::class)->group(function(){
        Route::get('/detail-jurnal', 'index')->name('detail-jurnal.index');
        Route::post('/detail-jurnal', 'store')->name('detail-jurnal.store');
        Route::get('/detail-jurnal/{id}', 'show')->name('detail-jurnal.show');
        Route::put('/detail-jurnal/{id}', 'update')->name('detail-jurnal.update');
        Route::delete('/detail-jurnal/{id}', 'destroy')->name('detail-jurnal.destroy');
        Route::delete('/detail-jurnal', 'destroyMultiple')->name('detail-jurnal.destroyMultiple');
    });

    /* DATA NERACA */
    Route::controller(NeracaController::class)->group(function(){
        Route::get('/neraca/data', 'dataAkun')->name('neraca.dataAkun');
    });

    /* DATA LABA RUGI */
    Route::controller(LabaRugiController::class)->group(function(){
        Route::get('/laba-rugi/data', 'dataLabaRugi')->name('labaRugi.dataLabaRugi');
    });

    /* DATA PERUBAHAN MODAL */
    // Route::controller(PerubahanModalController::class)->group(function(){
    //     Route::get('/perubahan-modal/data-sebelumnya', 'dataPrevious')->name('labaRugi.dataPrevious');
    //     Route::get('/perubahan-modal/data-sekarang', 'dataCurrent')->name('labaRugi.dataCurrent');
    // });

    /* USERS & LOGOUT */
    Route::controller(UserController::class)->group(function(){
        Route::get('/users/options', 'fetchDataOptions')->name('users.fetchDataOptions');
        Route::delete('/users', 'destroyMultiple')->name('users.destroyMultiple');
        Route::put('/users/profile', 'updateProfile')->name('users.updateProfile');
        Route::put('/users/password', 'changePassword')->name('users.changePassword');
    });
    Route::apiResource('users', UserController::class);
    Route::get('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
});
