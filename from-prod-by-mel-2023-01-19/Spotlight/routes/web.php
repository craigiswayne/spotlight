<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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


// OKTA INTEGRATION
Route::get('login/okta', 'App\Http\Controllers\Auth\LoginController@redirectToProvider')->name('login-okta');
Route::get('login/okta/callback', 'App\Http\Controllers\Auth\LoginController@handleProviderCallback');

Route::group(['middleware' => 'auth'], function(){

    Route::get('/', 'App\Http\Controllers\ApplicationController@index');

    Route::get('/showreel', 'App\Http\Controllers\ApplicationController@showreel');
    Route::get('/third-party-providers', 'App\Http\Controllers\ApplicationController@thirdPartyProviders');
    Route::get('/all-games', 'App\Http\Controllers\ApplicationController@allGames');
    Route::get('/new-games', 'App\Http\Controllers\ApplicationController@gamesNew');
    Route::get('/play-it-forward', 'App\Http\Controllers\ApplicationController@playItForward');
    Route::get('/markets', 'App\Http\Controllers\ApplicationController@regulatedMarkets');
    Route::get('/products', 'App\Http\Controllers\ApplicationController@products');
    Route::get('/games-lobby', 'App\Http\Controllers\ApplicationController@gamesLobby');
});

Route::group(['middleware' => 'auth:api', 'middleware' => 'auth'], function(){
    Route::get('/download', 'App\Http\Controllers\ProfileController@currentDownload')->middleware('permission.normal:Website|Download');
});

Route::group(['middleware' => 'auth', 'middleware' => 'auth.admin'], function(){

    Route::get('/admin', 'App\Http\Controllers\AdminController@index');

    Route::get('/admin/pages', 'App\Http\Controllers\NavigationController@index');
    Route::patch('/admin/pages/reorder', 'App\Http\Controllers\NavigationController@reorder')->middleware('permission.admin:Pages|Edit');
    Route::patch('/admin/pages/{id}', 'App\Http\Controllers\NavigationController@update')->middleware('permission.admin:Pages|Edit');

    Route::get('/admin/profile', 'App\Http\Controllers\ProfileController@index')->middleware('permission.admin:Profiles|View');
    Route::get('/admin/profile/add', 'App\Http\Controllers\ProfileController@add')->middleware('permission.admin:Profiles|Add');
    Route::get('/admin/profile/edit/{id}', 'App\Http\Controllers\ProfileController@edit')->middleware('permission.admin:Profiles|Edit');
    Route::get('/admin/profile/clone/{id}', 'App\Http\Controllers\ProfileController@clone')->middleware('permission.admin:Profiles|Edit');
    Route::get('/admin/profile/download/{id}', 'App\Http\Controllers\ProfileController@download')->middleware('permission.admin:Profiles|Download');

    Route::get('/admin/users', 'App\Http\Controllers\UserController@index')->middleware('permission.admin:Users|View');
    Route::get('/admin/roles', 'App\Http\Controllers\RoleController@index')->middleware('permission.admin:Roles|View');

    Route::get('/admin/showreel', "App\Http\Controllers\ShowreelController@index")->middleware('permission.admin:Showreel|View');
    Route::post('/admin/showreel', "App\Http\Controllers\ShowreelController@create")->middleware('permission.admin:Showreel|Add');
    Route::post('/admin/showreel/{id}', "App\Http\Controllers\ShowreelController@update")->middleware('permission.admin:Showreel|Edit');
    Route::patch('/admin/showreel/reorder', 'App\Http\Controllers\ShowreelController@reorder')->middleware('permission.admin:Showreel|Edit');
    Route::delete('/admin/showreel/{id}', 'App\Http\Controllers\ShowreelController@delete')->middleware('permission.admin:Showreel|Delete');

    Route::get('/admin/third-party-providers', 'App\Http\Controllers\ThirdPartyProviderController@index')->middleware('permission.admin:Third Party Providers|View');
    Route::post('/admin/third-party-providers', "App\Http\Controllers\ThirdPartyProviderController@create")->middleware('permission.admin:Third Party Providers|Add');
    Route::post('/admin/third-party-providers/{id}', "App\Http\Controllers\ThirdPartyProviderController@update")->middleware('permission.admin:Third Party Providers|Edit');
    Route::patch('/admin/third-party-providers/reorder', 'App\Http\Controllers\ThirdPartyProviderController@reorder')->middleware('permission.admin:Third Party Providers|Edit');
    Route::delete('/admin/third-party-providers/{id}', 'App\Http\Controllers\ThirdPartyProviderController@delete')->middleware('permission.admin:Third Party Providers|Delete');

    Route::get('/admin/games', 'App\Http\Controllers\GameController@index')->middleware('permission.admin:Games|View');
    Route::get('/admin/games/add', 'App\Http\Controllers\GameController@addIndex')->middleware('permission.admin:Games|Add');
    Route::patch('/admin/games/add', 'App\Http\Controllers\GameController@create')->middleware('permission.admin:Games|Add');
    Route::patch('/admin/games/{ids}/status/update', 'App\Http\Controllers\GameController@updateStatus')->middleware('permission.admin:Games|Add');
    Route::delete('/admin/games/{ids}', 'App\Http\Controllers\GameController@delete')->middleware('permission.admin:Games|Delete');
    Route::get('/admin/games/{id}', 'App\Http\Controllers\GameController@show')->middleware('permission.admin:Games|View');
    Route::patch('/admin/games/layout', 'App\Http\Controllers\GameController@layout')->middleware('permission.admin:Games|Edit');
    Route::patch('/admin/games/{id}', 'App\Http\Controllers\GameController@update')->middleware('permission.admin:Games|Edit');
    Route::post('/admin/games/{id}/stats', 'App\Http\Controllers\GameController@updateStats')->middleware('permission.admin:Games|Edit');
    Route::post('/admin/games/{id}/stats/toggle', 'App\Http\Controllers\GameController@updateNewMaths')->middleware('permission.admin:Games|Edit');

    Route::post('/admin/games/{id}/feature', 'App\Http\Controllers\GameController@addFeature')->middleware('permission.admin:Games|Edit');

    Route::post('/admin/games/features/{type}', 'App\Http\Controllers\GameController@addFeatureAsset')->middleware('permission.admin:Games|Edit');
    Route::patch('/admin/games/features/{type}/reorder', 'App\Http\Controllers\GameController@reorderFeatureAssets')->middleware('permission.admin:Games|Edit');
    Route::patch('/admin/games/features/{type}/{id}', 'App\Http\Controllers\GameController@updateFeatureAsset')->middleware('permission.admin:Games|Edit');
    Route::delete('/admin/games/features/{type}/{id}', 'App\Http\Controllers\GameController@deleteFeatureAsset')->middleware('permission.admin:Games|Edit');

    Route::get('/admin/studios', 'App\Http\Controllers\StudioController@index')->middleware('permission.admin:Studios|View');
    Route::post('/admin/studios', 'App\Http\Controllers\StudioController@create')->middleware('permission.admin:Studios|Add');
    Route::patch('/admin/studios/reorder', 'App\Http\Controllers\StudioController@reorder')->middleware('permission.admin:Studios|Edit');
    Route::patch('/admin/studios/{id}', 'App\Http\Controllers\StudioController@update')->middleware('permission.admin:Studios|Edit');
    Route::delete('/admin/studios/{id}', 'App\Http\Controllers\StudioController@delete')->middleware('permission.admin:Studios|Delete');

    Route::get('/admin/play-it-forward', 'App\Http\Controllers\PlayItForwardController@index')->middleware('permission.admin:Play It Forward|View');
    Route::post('/admin/play-it-forward', 'App\Http\Controllers\PlayItForwardController@create')->middleware('permission.admin:Play It Forward|Add');
    Route::patch('/admin/play-it-forward/reorder', 'App\Http\Controllers\PlayItForwardController@reorder')->middleware('permission.admin:Play It Forward|Edit');
    Route::patch('/admin/play-it-forward/{id}', 'App\Http\Controllers\PlayItForwardController@update')->middleware('permission.admin:Play It Forward|Edit');
    Route::delete('/admin/play-it-forward/{id}', 'App\Http\Controllers\PlayItForwardController@delete')->middleware('permission.admin:Play It Forward|Delete');
    Route::post('/admin/play-it-forward/{type}', 'App\Http\Controllers\PlayItForwardController@createAsset')->middleware('permission.admin:Play It Forward|Edit');
    Route::patch('/admin/play-it-forward/{type}/reorder', 'App\Http\Controllers\PlayItForwardController@reorderAsset')->middleware('permission.admin:Play It Forward|Edit');
    Route::patch('/admin/play-it-forward/{type}/{id}', 'App\Http\Controllers\PlayItForwardController@updateAsset')->middleware('permission.admin:Play It Forward|Edit');
    Route::delete('/admin/play-it-forward/{type}/{id}', 'App\Http\Controllers\PlayItForwardController@deleteAsset')->middleware('permission.admin:Play It Forward|Edit');

    Route::get('/admin/products', 'App\Http\Controllers\ProductController@index')->middleware('permission.admin:Products|View');
    Route::post('/admin/products', 'App\Http\Controllers\ProductController@create')->middleware('permission.admin:Products|Add');
    Route::patch('/admin/products', 'App\Http\Controllers\ProductController@pageUpdate')->middleware('permission.admin:Products|Edit');
    Route::patch('/admin/products/{id}', 'App\Http\Controllers\ProductController@update')->middleware('permission.admin:Products|Edit');
    Route::delete('/admin/products/{id}', 'App\Http\Controllers\ProductController@delete')->middleware('permission.admin:Products|Delete');
    Route::patch('/admin/product-features/reorder', 'App\Http\Controllers\ProductFeatureController@reorder')->middleware('permission.admin:Products|Edit');
    Route::post('/admin/products/{product}/features', 'App\Http\Controllers\ProductFeatureController@store')->middleware('permission.admin:Products|Edit');
    Route::patch('/admin/product-features/{id}', 'App\Http\Controllers\ProductFeatureController@update')->middleware('permission.admin:Products|Edit');
    Route::delete('/admin/product-features/{id}', 'App\Http\Controllers\ProductFeatureController@delete')->middleware('permission.admin:Products|Edit');

    Route::get('/admin/markets', 'App\Http\Controllers\RegulatedMarketController@index')->middleware('permission.admin:Markets|View');
    Route::post('/admin/markets/logos', 'App\Http\Controllers\RegulatedMarketController@addLogo')->middleware('permission.admin:Markets|Edit');
    Route::delete('/admin/markets/logos/{id}', 'App\Http\Controllers\RegulatedMarketController@deleteLogo')->middleware('permission.admin:Markets|Edit');
    Route::get('/admin/markets/{id}', 'App\Http\Controllers\RegulatedMarketController@get')->middleware('permission.admin:Markets|View');
    Route::post('/admin/markets', 'App\Http\Controllers\RegulatedMarketController@create')->middleware('permission.admin:Markets|Add');
    Route::patch('/admin/markets/{id}', 'App\Http\Controllers\RegulatedMarketController@update')->middleware('permission.admin:Markets|Edit');
    Route::delete('/admin/markets/{id}', 'App\Http\Controllers\RegulatedMarketController@delete')->middleware('permission.admin:Markets|Delete');
});

Route::group(['middleware' => 'auth:api', 'middleware' => 'auth.admin'], function(){


     Route::get('/admin/api/roles', 'App\Http\Controllers\RoleController@get')->middleware('permission.admin:Profiles|Edit,Users|Add,Users|Edit');
    Route::get('/admin/api/profile/name/check', 'App\Http\Controllers\ProfileController@checkName')->middleware('permission.admin:Profiles|Edit');
    Route::get('/admin/api/studios', 'App\Http\Controllers\StudioController@get')->middleware('permission.admin:Profiles|Edit');
    Route::get('/admin/api/games', 'App\Http\Controllers\GameController@get')->middleware('permission.admin:Profiles|Edit');
    Route::post('/admin/api/profile/create', 'App\Http\Controllers\ProfileController@create')->middleware('permission.admin:Profiles|Add,Profiles|Edit');
    Route::post('/admin/api/profile/{id}/edit', 'App\Http\Controllers\ProfileController@update')->middleware('permission.admin:Profiles|Edit');
    Route::post('/admin/api/profile/{id}/setOverride', 'App\Http\Controllers\ProfileController@setUserOverride')->middleware('permission.admin:Profiles|Edit');
    Route::post('/admin/api/profile/clearOverride', 'App\Http\Controllers\ProfileController@clearUserOverride')->middleware('permission.admin:Profiles|Edit');
    Route::post('/admin/api/profile/{id}/roles', 'App\Http\Controllers\ProfileController@assign')->middleware('permission.admin:Profiles|Edit');
    Route::delete('/admin/api/profile/{id}/delete', 'App\Http\Controllers\ProfileController@delete')->middleware('permission.admin:Profiles|Delete');

    Route::delete('/admin/api/user/{id}/delete', 'App\Http\Controllers\UserController@delete')->middleware('permission.admin:Users|Delete');
    Route::get('/admin/api/user/available', 'App\Http\Controllers\UserController@available')->middleware('permission.admin:Users|Add,Users|Edit');
    Route::post('/admin/api/user/{id}/edit', 'App\Http\Controllers\UserController@edit')->middleware('permission.admin:Users|Edit');
    Route::post('/admin/api/user/create', 'App\Http\Controllers\UserController@create')->middleware('permission.admin:Users|Add');

    Route::get('/admin/api/users', 'App\Http\Controllers\UserController@get')->middleware('permission.admin:Roles|Edit');
    Route::post('/admin/api/role/{id}/users', 'App\Http\Controllers\RoleController@upsertUsers')->middleware('permission.admin:Roles|Edit');

    Route::get('/admin/api/role/types', 'App\Http\Controllers\RoleTypeController@get')->middleware('permission.admin:Roles|Add,Roles|Edit');
    Route::get('/admin/api/profiles', 'App\Http\Controllers\ProfileController@get')->middleware('permission.admin:Roles|Add,Roles|Edit');
    Route::get('/admin/api/securables', 'App\Http\Controllers\SecurableController@get')->middleware('permission.admin:Roles|Add,Roles|Edit');
    Route::get('/admin/api/role/available', 'App\Http\Controllers\RoleController@available')->middleware('permission.admin:Roles|Add,Roles|Edit');
    Route::post('/admin/api/role/create', 'App\Http\Controllers\RoleController@create')->middleware('permission.admin:Roles|Add');

    Route::post('/admin/api/role/{id}/edit', 'App\Http\Controllers\RoleController@edit')->middleware('permission.admin:Roles|Edit');
    Route::delete('/admin/api/role/{id}/delete', 'App\Http\Controllers\RoleController@delete')->middleware('permission.admin:Roles|Delete');
});

Route::group(['middleware' => 'auth:api', 'middleware' => 'auth.admin'], function(){
    Route::get('/games-lobby/api/games/year/{year}', 'App\Http\Controllers\GameLobbyController@getGames');
    Route::get('/games-lobby/api/game/{id}', 'App\Http\Controllers\GameLobbyController@getDemo');
    Route::get('/games-lobby/api/games/search', 'App\Http\Controllers\GameLobbyController@search');

});
