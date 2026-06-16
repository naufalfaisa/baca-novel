<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\NovelController as AdminNovelController;
use App\Http\Controllers\Admin\ChapterController as AdminChapterController;
use App\Http\Controllers\Author\DashboardController as AuthorDashboard;
use App\Http\Controllers\Author\NovelController as AuthorNovelController;
use App\Http\Controllers\Author\ChapterController as AuthorChapterController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\NovelController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Middleware\CheckRole;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/novels/{slug}', [NovelController::class, 'show'])->name('novels.show');
Route::get('/novels/{slug}/chapter/{chapterNumber}', [ChapterController::class, 'show'])->name('chapters.show');

Route::post('/xendit/webhook', [SubscriptionController::class, 'webhook'])->name('xendit.webhook');

Route::middleware('auth')->group(function () {
    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile/apply-author', [ProfileController::class, 'applyAuthor'])->name('profile.applyAuthor');

    Route::post('/novels/{novel}/bookmark', [NovelController::class, 'toggleBookmark'])->name('novels.bookmark');
    Route::post('/novels/{novel}/vote', [NovelController::class, 'vote'])->name('novels.vote');
    Route::post('/novels/{novel}/report', [NovelController::class, 'report'])->name('novels.report');

    Route::post('/chapters/{chapter}/comment', [ChapterController::class, 'storeComment'])->name('chapters.comment');
});

Route::middleware(['auth', CheckRole::class.':user,author'])->group(function () {
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success');
    Route::post('/subscription/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscription.subscribe');
});

Route::middleware(['auth', CheckRole::class.':admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    Route::post('/author-applications/{application}/approve', [AdminDashboard::class, 'approveAuthor'])->name('author-applications.approve');
    Route::post('/author-applications/{application}/reject', [AdminDashboard::class, 'rejectAuthor'])->name('author-applications.reject');
    Route::post('/novels/{novel}/toggle-status', [AdminDashboard::class, 'toggleNovelStatus'])->name('novels.toggle-status');

    Route::get('/novels/create', [AdminNovelController::class, 'create'])->name('novels.create');
    Route::post('/novels', [AdminNovelController::class, 'store'])->name('novels.store');
    Route::get('/novels/{novel}', [AdminNovelController::class, 'show'])->name('novels.show');
    Route::get('/novels/{novel}/edit', [AdminNovelController::class, 'edit'])->name('novels.edit');
    Route::put('/novels/{novel}', [AdminNovelController::class, 'update'])->name('novels.update');
    Route::delete('/novels/{novel}', [AdminNovelController::class, 'destroy'])->name('novels.destroy');

    Route::get('/novels/{novel}/chapters/create', [AdminChapterController::class, 'create'])->name('chapters.create');
    Route::post('/novels/{novel}/chapters', [AdminChapterController::class, 'store'])->name('chapters.store');
    Route::get('/novels/{novel}/chapters/{chapter}/edit', [AdminChapterController::class, 'edit'])->name('chapters.edit');
    Route::put('/novels/{novel}/chapters/{chapter}', [AdminChapterController::class, 'update'])->name('chapters.update');
    Route::delete('/novels/{novel}/chapters/{chapter}', [AdminChapterController::class, 'destroy'])->name('chapters.destroy');
});

Route::middleware(['auth', CheckRole::class.':author'])->prefix('author')->name('author.')->group(function () {
    Route::get('/dashboard', [AuthorDashboard::class, 'index'])->name('dashboard');

    Route::get('/novels/create', [AuthorNovelController::class, 'create'])->name('novels.create');
    Route::post('/novels', [AuthorNovelController::class, 'store'])->name('novels.store');
    Route::get('/novels/{novel}', [AuthorNovelController::class, 'show'])->name('novels.show');
    Route::get('/novels/{novel}/edit', [AuthorNovelController::class, 'edit'])->name('novels.edit');
    Route::put('/novels/{novel}', [AuthorNovelController::class, 'update'])->name('novels.update');
    Route::delete('/novels/{novel}', [AuthorNovelController::class, 'destroy'])->name('novels.destroy');

    Route::get('/novels/{novel}/chapters/create', [AuthorChapterController::class, 'create'])->name('chapters.create');
    Route::post('/novels/{novel}/chapters', [AuthorChapterController::class, 'store'])->name('chapters.store');
    Route::get('/novels/{novel}/chapters/{chapter}/edit', [AuthorChapterController::class, 'edit'])->name('chapters.edit');
    Route::put('/novels/{novel}/chapters/{chapter}', [AuthorChapterController::class, 'update'])->name('chapters.update');
    Route::delete('/novels/{novel}/chapters/{chapter}', [AuthorChapterController::class, 'destroy'])->name('chapters.destroy');
});

require __DIR__.'/auth.php';
