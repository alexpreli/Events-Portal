<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OrganizerRegisterController;
use App\Http\Controllers\UserRegisterController;


Route::get('/', function()  {
    return view('homePage');
});

Route::post('/contactRequest', [ContactController::class, 'store']);
Route::post('/organizerRegister', [OrganizerRegisterController::class, 'organizerRegister']);
Route::post('/userRegister', [UserRegisterController::class, 'userRegister']);
Route::post('/organizerLogin', [AuthController::class, 'organizerLogin']);
Route::post('/userLogin', [AuthController::class, 'userLogin']);

Route::post('/events', [EventController::class, 'store']);
Route::put('/events', [EventController::class, 'update']);
Route::delete('/events', [EventController::class, 'destroy']);
Route::get('/events', [EventController::class, 'index']);
Route::post('/events/register', [EventController::class, 'registerForEvent']);
Route::post('/events/userEvents', [EventController::class, 'getUserEvents']);
Route::post('/events/organizerEvents', [EventController::class, 'getOrganizerEvents']);
Route::get('/events/participants', [EventController::class, 'getEventsWithParticipants']);
Route::get('/events/perDay', [EventController::class, 'getEventsPerDay']);