<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthApiController,OrderApiController,DirectoryApiController};
Route::post('/v1/login',[AuthApiController::class,'login'])->middleware('throttle:5,1');
Route::middleware(['auth:sanctum','active','throttle:60,1'])->prefix('v1')->group(function(){
 Route::get('/me',[AuthApiController::class,'me']);Route::post('/logout',[AuthApiController::class,'logout']);
 Route::get('/orders',[OrderApiController::class,'index']);Route::get('/orders/{order}',[OrderApiController::class,'show']);
 Route::post('/orders',[OrderApiController::class,'store'])->middleware('role:admin,manager,receptionist');
 Route::patch('/orders/{order}/status',[OrderApiController::class,'status'])->middleware('role:admin,manager,technician');
 Route::get('/doctors',[DirectoryApiController::class,'doctors'])->middleware('role:admin,manager,receptionist');
 Route::get('/patients',[DirectoryApiController::class,'patients'])->middleware('role:admin,manager,receptionist');
 Route::get('/work-types',[DirectoryApiController::class,'workTypes']);
});
