<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Service;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Order;
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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::post('/meet-and-greet/search', [Service::class, 'meetAndGreet']);
Route::get('/meet-and-greet/search/{serviceId}', [Service::class, 'meetAndGreetDetail']);
Route::post('/login', [AuthController::class, 'Login']);
Route::get('/service/airport', [Service::class, 'getServiceAirport']);
Route::get('/airport-list', [Service::class, 'getAirportList']);
Route::get('country-list', [Service::class, 'getCountryList']);

Route::get('/airport-lounge/search', [Service::class, 'lounge']);

Route::post('order/meet-and-greet', [Order::class, 'meetAndGreet']);
Route::get('order/meet-and-greet/{reference_id}', [Order::class, 'getMeetGreetOrder']);
