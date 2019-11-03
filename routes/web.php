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

Artisan::call('view:clear');

Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
});

Route::prefix('/ajax')->group(function () {
    Route::get('/vendors', 'AjaxController@getVendors')->name('ajaxGetVendors');
    Route::get('/projects', 'AjaxController@getProjects')->name('ajaxGetProjects');
    Route::get('/news', 'AjaxController@getNews')->name('ajaxGetNews');
    Route::post('/access', 'AjaxController@access')->name('ajaxAccess');

    Route::get('/import', 'AjaxController@testImport')->name('testImport');
    Route::get('/tracks', 'AjaxController@getTracks')->name('ajaxTracks');
    Route::get('/tracks_categories', 'AjaxController@getCategoriesTracks')->name('ajaxCategoriesTracks');
    Route::get('/tracks_covers', 'AjaxController@getCoversTracks')->name('ajaxCoversTracks');
    Route::get('/tracks_diagnostic', 'AjaxController@getDiagnostic')->name('ajaxDiagnostic');
    
    Route::get('/categories_type', 'AjaxController@getCategoriesType')->name('ajaxCategoriesType');
    Route::get('/covers_type', 'AjaxController@getCoversType')->name('ajaxCoversType');

    Route::get('/messages', 'AjaxController@getRoadMessages')->name('getRoadMessages');

    Route::get('/new/tracks', 'AjaxController@getTracksNew')->name('ajaxTracksNew');

});

Route::get('/', 'PageController@index')->name('home');
Route::get('/osm', 'PageController@osm')->name('osm');

Route::get('/vendors', 'PageController@vendors')->name('vendors');
Route::get('/vendors/{id}', 'PageController@vendor')->name('vendor');
Route::get('/projects', 'PageController@projects')->name('projects');
Route::get('/projects/{id}', 'PageController@project')->name('project');

Route::get('/static/{slug}', 'PageController@static')->name('static');

Route::get('/map', 'PageController@map')->name('map');
Route::get('/map-new', 'PageController@mapNew')->name('mapnew');