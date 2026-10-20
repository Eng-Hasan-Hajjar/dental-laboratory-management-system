<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{AuthController,DashboardController,DoctorController,PatientController,WorkTypeController,LabOrderController,InvoiceController,PaymentController,ReportController,UserController};

Route::get('/',fn()=>redirect()->route('dashboard'));
Route::middleware('guest')->group(function(){
 Route::get('/login',[AuthController::class,'create'])->name('login');
 Route::post('/login',[AuthController::class,'store'])->middleware('throttle:5,1')->name('login.attempt');
});
Route::middleware(['auth','active'])->group(function(){
 Route::post('/logout',[AuthController::class,'destroy'])->name('logout');
 Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');
 Route::resource('doctors',DoctorController::class)->except('show')->middleware('role:admin,manager,receptionist');
 Route::resource('patients',PatientController::class)->except('show')->middleware('role:admin,manager,receptionist');
 Route::get('/orders',[LabOrderController::class,'index'])->name('orders.index');
 Route::get('/orders/create',[LabOrderController::class,'create'])->middleware('role:admin,manager,receptionist')->name('orders.create');
 Route::post('/orders',[LabOrderController::class,'store'])->middleware('role:admin,manager,receptionist')->name('orders.store');
 Route::get('/orders/{order}',[LabOrderController::class,'show'])->name('orders.show');
 Route::get('/orders/{order}/edit',[LabOrderController::class,'edit'])->middleware('role:admin,manager,receptionist')->name('orders.edit');
 Route::put('/orders/{order}',[LabOrderController::class,'update'])->middleware('role:admin,manager,receptionist')->name('orders.update');
 Route::patch('/orders/{order}/status',[LabOrderController::class,'status'])->middleware('role:admin,manager,technician')->name('orders.status');
 Route::delete('/orders/{order}',[LabOrderController::class,'destroy'])->middleware('role:admin,manager')->name('orders.destroy');
 Route::resource('work-types',WorkTypeController::class)->except('show')->middleware('role:admin,manager');
 Route::get('/invoices',[InvoiceController::class,'index'])->middleware('role:admin,manager,accountant')->name('invoices.index');
 Route::get('/invoices/{invoice}',[InvoiceController::class,'show'])->middleware('role:admin,manager,accountant')->name('invoices.show');
 Route::get('/invoices/{invoice}/edit',[InvoiceController::class,'edit'])->middleware('role:admin,manager,accountant')->name('invoices.edit');
 Route::put('/invoices/{invoice}',[InvoiceController::class,'update'])->middleware('role:admin,manager,accountant')->name('invoices.update');
 Route::post('/invoices/{invoice}/payments',[PaymentController::class,'store'])->middleware('role:admin,manager,accountant')->name('payments.store');
 Route::get('/reports',[ReportController::class,'index'])->middleware('role:admin,manager,accountant')->name('reports.index');
 Route::get('/reports/csv/export',[ReportController::class,'csv'])->middleware('role:admin,manager,accountant')->name('reports.csv');
 Route::resource('users',UserController::class)->except(['show','destroy'])->middleware('role:admin');
});
