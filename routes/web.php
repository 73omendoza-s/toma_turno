<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\PantallaBannersController;
use App\Http\Controllers\PantallaInstitucionalController;
use App\Http\Controllers\TelevisorController;
use App\Http\Controllers\TicketController;
use App\Livewire\Admin\Banners;
use App\Livewire\Admin\Catalogo;
use App\Livewire\Admin\Configuracion;
use App\Livewire\Admin\ConfigImpresora;
use App\Livewire\Admin\ControlInstitucional;
use App\Livewire\Admin\Usuarios;
use App\Livewire\Estadisticas;
use App\Livewire\Operador;
use App\Livewire\Recepcion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// ---------- Públicas (el televisor no inicia sesión) ----------
Route::get('/pantalla/institucional', PantallaInstitucionalController::class)
    ->middleware('throttle:pantalla')->name('pantalla.institucional');

Route::get('/pantalla', [TelevisorController::class, 'index'])->name('pantalla');
Route::get('/pantalla/datos', [TelevisorController::class, 'datos'])
    ->middleware('throttle:pantalla')->name('pantalla.datos');
Route::get('/pantalla/banners', PantallaBannersController::class)
    ->middleware('throttle:pantalla')->name('pantalla.banners');

// ---------- Acceso ----------
Route::get('/', fn() => Auth::check() ? redirect(Auth::user()->rol->inicio()) : redirect('/login'));

Route::get('/login', [LoginController::class, 'formulario'])->name('login');
Route::post('/login', [LoginController::class, 'entrar'])->middleware('throttle:login');
Route::post('/logout', [LoginController::class, 'salir'])->middleware('auth')->name('logout');

Route::get('/sesion/estado', fn() => response()->json(['ok' => true]))
    ->middleware('auth')->name('sesion.estado');

// ---------- Con sesión iniciada ----------
Route::middleware('auth')->group(function () {
    Route::get('/recepcion', Recepcion::class)->middleware('rol:recepcion,admin');
    Route::get('/recepcion/ticket/{turno}', [TicketController::class, 'show'])
        ->middleware('rol:recepcion,admin')->name('ticket.imprimir');

    Route::get('/operador', Operador::class)->middleware('rol:operador');

    Route::get('/admin/estadisticas', Estadisticas::class)
        ->middleware('rol:admin,supervisor')->name('estadisticas');

    // Solo administrador
    Route::middleware('rol:admin')->prefix('admin')->group(function () {
        Route::get('/catalogo', Catalogo::class)->name('admin.catalogo');

        Route::get('/usuarios', Usuarios::class)->name('admin.usuarios');
        Route::get('/configuracion', Configuracion::class)->name('admin.configuracion');
        Route::get('/banners', Banners::class)->name('admin.banners');
        Route::get('/institucional', ControlInstitucional::class)->name('admin.institucional');
        Route::get('/impresora', ConfigImpresora::class)->name('admin.impresora');
    });
});
