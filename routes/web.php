<?php

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

        Route::prefix('campaign')->group(function() {
            Route::get('/', "CampaignController@index")->name('campaign');
            Route::get('create', "CampaignController@create")->name('campaign.create');
            Route::get('show/{campaign?}', "CampaignController@show")->name('campaign.show');
            Route::get('edit/{campaign?}', "CampaignController@edit")->name('campaign.edit');
            Route::get('delete/{campaign?}', "CampaignController@delete")->name('campaign.delete');
            Route::get('template', "CampaignController@downloadTemplate")->name('campaign.template');
    
            // Route::get('list', 'CampaignController@getCampaignList')->name('campaign.list');
            Route::post('list/ajax', 'CampaignController@getCampaignListAjax')->name('campaign.list.ajax');
            Route::post('/', 'CampaignController@store')->name('campaign.store');
            Route::put('/', 'CampaignController@update')->name('campaign.update');
            Route::put('startstop', 'CampaignController@updateStartStop')->name('campaign.update.startstop');
            Route::delete('/', 'CampaignController@destroy')->name('campaign.destroy');
    
            Route::post('export', 'CampaignController@exportData')->name('campaign.export');
            Route::post('export/failed', 'CampaignController@exportFailedContacts')->name('campaign.export.failed');
        });
        
        Route::prefix('user')->group(function() {
            Route::get('/', "UserController@index")->name('user');
            Route::get('create', "UserController@create")->name('user.create');
            Route::get('edit/{username?}', "UserController@edit")->name('user.edit');
            Route::get('resetpass/{username?}', "UserController@showResetPassword")->name('user.resetpass');
            Route::get('delete/{username?}', "UserController@delete")->name('user.delete');
    
            // Route::get('list', 'UserController@getUserList')->name('user.list');
            Route::post('list', 'UserController@getUserListAjax')->name('user.list.ajax');
            Route::post('/', 'UserController@store')->name('user.store');
            Route::put('/', 'UserController@update')->name('user.update');
            Route::put('resetpass', 'UserController@updatePassword')->name('user.update.password');
            Route::delete('/{username?}', 'UserController@destroy')->name('user.destroy');
        });
        
        Route::prefix('account')->group(function() {
            Route::get('/', "AccountController@index")->name('account');
            Route::put('/', 'AccountController@update')->name('account.update');
            Route::put('password', 'AccountController@updatePassword')->name('account.update.password');
        });
        
        Route::prefix('contact')->group(function() {
            Route::get('show/{contact?}/{campaign?}', 'ContactController@show')->name('contact.show');
            Route::get('list/{campaign?}', 'ContactController@contactList')->name('contact.list');
            Route::post('list', 'ContactController@contactListAjax')->name('contact.list.ajax');
        });
        
        Route::prefix('calllog')->group(function() {
            Route::get('/', 'CallLogController@index')->name('calllogs');
            Route::get('/{startDate?}/{endDate?}', 'CallLogController@getCallStatus')->name('callog.status');
            Route::post('export', 'CallLogController@exportData')->name('calllog.export');
            Route::get('/recording', 'CallLogController@recording')->name('calllog.recording');
        });
    }
);
