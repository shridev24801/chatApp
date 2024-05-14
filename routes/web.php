<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\PusherAuthController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use App\Events\PrivateMessageEvent;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Broadcast::routes(['middleware' => ['web']]);

Route::post('/pusher/auth', [PusherAuthController::class, 'authenticate'])->name('/pusher/auth');
Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';


Route::middleware('auth')->get('/chat/{recipient?}', [ChatController::class, 'showChat'])->name('chat');
Route::middleware('auth')->post('/chat/{recipient}', [ChatController::class, 'sendMessage'])->name('send.message');
Route::post('/mark-messages-read', [ChatController::class, 'markMessagesRead'])->name('mark.messages.read');

Route::get('send',function(){

    event(new PrivateMessageEvent(User::find(2), 'Test message'));
    return 'sent successfully';
});

