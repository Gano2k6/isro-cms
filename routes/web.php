<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RankingController;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'index'])->name('home');
Route::get('/lang/{locale}', function ($locale) {if (in_array($locale, array_keys(config('global.languages')))) { Setting::updateOrCreate(['key' => 'locale'], ['value' => $locale]); } return redirect()->back(); })->name('lang.switch');

Route::get('/post/{slug}', [PageController::class, 'post'])->name('pages.post.show');
Route::get('/page/{slug}', [PageController::class, 'page'])->name('pages.page.show');
Route::get('/timers', [PageController::class, 'timers'])->name('pages.timers');
Route::get('/uniques', [PageController::class, 'uniques'])->name('pages.uniques');
Route::get('/uniques-advanced', [PageController::class, 'uniques_advanced'])->name('pages.uniques-advanced');
Route::any('/fortress', [PageController::class, 'fortress'])->name('pages.fortress');
Route::any('/globals', [PageController::class, 'globals'])->name('pages.globals');
Route::get('/download', [PageController::class, 'download'])->name('pages.download');
Route::get('/gateway', [PageController::class, 'gateway'])->name('pages.gateway');

Route::get('/ranking', [RankingController::class, 'index'])->name('ranking');
Route::any('/ranking/player', [RankingController::class, 'player'])->name('ranking.player');
Route::any('/ranking/guild', [RankingController::class, 'guild'])->name('ranking.guild');
Route::any('/ranking/unique', [RankingController::class, 'unique'])->name('ranking.unique');
Route::any('/ranking/unique-monthly', [RankingController::class, 'unique_monthly'])->name('ranking.unique-monthly');
Route::any('/ranking/honor', [RankingController::class, 'honor'])->name('ranking.honor');
Route::any('/ranking/job', [RankingController::class, 'job'])->name('ranking.job');
Route::any('/ranking/job-all', [RankingController::class, 'job_all'])->name('ranking.job-all');
Route::any('/ranking/job-hunter', [RankingController::class, 'job_hunter'])->name('ranking.job-hunter');
Route::any('/ranking/job-thieve', [RankingController::class, 'job_thieve'])->name('ranking.job-thieve');
Route::any('/ranking/job-trader', [RankingController::class, 'job_trader'])->name('ranking.job-trader');
Route::any('/ranking/fortress-player', [RankingController::class, 'fortress_player'])->name('ranking.fortress-player');
Route::any('/ranking/fortress-guild', [RankingController::class, 'fortress_guild'])->name('ranking.fortress-guild');

Route::get('/ranking/character/{name}', [RankingController::class, 'character_view'])->name('ranking.character.view');
Route::get('/ranking/guild/{name}', [RankingController::class, 'guild_view'])->name('ranking.guild.view');
Route::any('/ranking/guild-crest/{hex}.png', [RankingController::class, 'guild_crest'])->name('ranking.guild-crest');

Route::middleware(['auth', config('settings.register_confirm') ? 'verified' : 'throttle'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile/edit', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile/edit', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/silk-history', [ProfileController::class, 'silk_history'])->name('profile.silk-history');

    require __DIR__.'/donate.php';
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
