<?php
use App\Campaign;

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

Route::get('/', "DashboardController@index");

Auth::routes();

Route::group(
    ['middleware' => 'auth'],
    function() {
        // Route::get('/home', 'HomeController@index')->name('home');
        Route::get('/dashboard', "DashboardController@index")->name('dashboard');
        Route::get('/dashboard/stream', 'DashboardController@stream')->name('dashboard.stream');
        
        Route::post('export', 'CampaignController@exportData')->name('campaigns.export');
        Route::put('startstop', 'CampaignController@updateStartStop')->name('campaigns.update.startstop');
        Route::resource('campaigns', 'CampaignController');
        
        Route::prefix('users')->group(function() {
            Route::get('/', "UserController@index")->name('users');
            Route::get('{id}/edit', "UserController@edit")->name('users.edit');
            Route::get('{id}/delete', "UserController@delete")->name('users.delete');
            Route::get('{id}/resetpass', "UserController@showResetPassword")->name('users.resetpass');
            Route::get('create', "UserController@create")->name('users.create');
            
            Route::put('/', 'UserController@update')->name('users.update');
            Route::put('resetpass', 'UserController@updatePassword')->name('users.update.password');

            Route::post('/', 'UserController@store')->name('users.store');
            Route::delete('/', 'UserController@destroy')->name('users.destroy');
        });

        Route::prefix('account')->group(function() {
            Route::get('/', "AccountController@index")->name('account');
            Route::put('/', 'AccountController@update')->name('account.update');
            Route::put('password', 'AccountController@updatePassword')->name('account.update.password');
        });

        Route::get('/calllogs/list', 'CallLogController@callLogs')->name('calllogs.list');
        Route::post('/calllogs/export', 'CallLogController@exportData')->name('calllogs.export');
        Route::resource('calllogs', 'CallLogController');

        Route::resource('contacts', 'ContactController');
        
        Route::get('templates/download/{id}', 'TemplateController@download')->name('templates.download');
        Route::resource('templates', 'TemplateController');

        /*
        Route::prefix('campaigns')->group(function() {
            Route::get('/', "CampaignController@index")->name('campaigns');
            Route::get('create', "CampaignController@create")->name('campaigns.create');
            Route::get('template/{templateId}', "CampaignController@downloadTemplate")->name('campaigns.template');
            Route::get('importprogress', 'CampaignController@importProgress')->name('campaigns.importprogress');
            Route::get('{id}', "CampaignController@show")->name('campaigns.show');
            Route::get('{id}/edit', "CampaignController@edit")->name('campaigns.edit');
            Route::get('{id}/delete', "CampaignController@delete")->name('campaigns.delete');
            
            Route::post('/', 'CampaignController@store')->name('campaigns.store');
            Route::post('export', 'CampaignController@exportData')->name('campaigns.export');
            Route::post('export/failed', 'CampaignController@exportFailedContacts')->name('campaigns.export.failed');

            Route::put('/', 'CampaignController@update')->name('campaigns.update');
            Route::put('startstop', 'CampaignController@updateStartStop')->name('campaigns.update.startstop');

            Route::delete('/', 'CampaignController@destroy')->name('campaigns.destroy');
        });
        
        Route::prefix('calllogs')->group(function() {
            Route::get('/', 'CallLogController@index')->name('calllogs');
            Route::get('/recording', 'CallLogController@recording')->name('calllogs.recording');
            Route::post('export', 'CallLogController@exportData')->name('calllogs.export');
        });
        */
    }
);
