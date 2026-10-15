<?php

use App\Http\Controllers\BookingSuccessController;
use App\Http\Controllers\HomeController;
use App\Livewire\ItemDetail;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/items/{item}', ItemDetail::class)->name('items.show');
Route::get('/bookings/{booking:booking_code}/success', BookingSuccessController::class)->name('booking.success');
