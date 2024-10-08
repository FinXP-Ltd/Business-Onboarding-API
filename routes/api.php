<?php

use App\Http\Controllers\v1\Auth0\UserController as Auth0UserController;
use App\Http\Controllers\v1\Auth\UserController;
use App\Http\Controllers\v1\BusinessController;
use App\Http\Controllers\v1\DocumentController;
use App\Http\Controllers\v1\CompanyController;
use App\Http\Controllers\v1\Person\NaturalPersonController;
use App\Http\Controllers\v1\Person\NonNaturalPersonController;
use App\Http\Controllers\v1\LookupController;
use App\Http\Controllers\v1\SearchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['api', 'XssSanitizer'], 'prefix' => 'v1'], function () {
    Route::group(['middleware' => ['auth:auth0-api']], function () {
        Route::prefix('/auth')
            ->controller(UserController::class)
            ->group(function () {
                Route::get('/me', 'show')->name('auth.me');
                Route::post('/users', 'create')->name('auth.create');
                Route::put('/users/{userId}', 'update')->name('auth.update');
            });

        // better payments
        Route::group(['middleware' => ['client.type:zbx|bp', 'm2m']], function () {
            Route::prefix('/businesses')
                ->controller(BusinessController::class)
                ->group(function () {

                    Route::group(['middleware' => ['restrict.query']], function () {
                        Route::get('/', 'index')->name('business.index');
                        Route::get('/{business}', 'show')->name('business.show');
                        Route::get('/{business}/documents', 'getDocumentsList')->name('business.documents');
                    });

                    Route::post('/', 'create')->name('business.create');
                    Route::put('/{business}', 'update')->name('business.update');

                    Route::post('/composition', 'createComposition')->name('business.createComposition');
                    Route::put('/composition/{businessComposition}', 'updateComposition')->name('business.updateComposition');
                    Route::put('/{business}/submit', 'submit')->name('business.submit');
                    Route::put('/{business}/withdraw', 'withdraw')->name('business.withdraw');
                    Route::put('/{business}/draft', 'draft')->name('business.draft');
                    Route::delete('/composition/{businessComposition}', 'delete')->name('business.deleteComposition');
                });

            Route::prefix('/documents')
                ->controller(DocumentController::class)
                ->group(function () {
                    Route::post('/', 'upload')->name('document.upload');
                    Route::get('/list', 'list')->name('document.list');
                    Route::get('/{document}', 'show')->name('document.show')->middleware('restrict.query');
                    Route::delete('/{document}', 'delete')->name('document.delete');
                });

            Route::prefix('/search')
                ->controller(SearchController::class)
                ->group(function () {
                    Route::post('/person', 'searchPerson')->name('search.person');
                });

            Route::prefix('/persons')
                ->controller(NaturalPersonController::class)
                ->group(function () {
                    Route::post('/', 'store')->name('natural.store');
                    Route::put('/{natural}', 'update')->name('natural.update');
                    Route::get('/{natural}', 'show')->name('natural.show')->middleware('restrict.query');
                });

            Route::prefix('/non-natural-person')
                ->controller(NonNaturalPersonController::class)
                ->group(function () {
                    Route::post('/', 'store')->name('nonNatural.store');
                    Route::put('/{nonNatural}', 'update')->name('nonNatural.update');
                    Route::get('/{nonNatural}', 'show')->name('nonNatural.show')->middleware('restrict.query');
                });
            Route::prefix('/company')
                ->controller(CompanyController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('company.index');
                    Route::post('/', 'store')->name('company.store');
                    Route::put('/{company}', 'update')->name('company.update');
                    Route::get('/{company}', 'show')->name('company.show')->middleware('restrict.query');
                    Route::delete('/{company}', 'delete')->name('company.delete');
                });

            Route::prefix('/lookup')
                ->controller(LookupController::class)
                ->group(function () {
                    Route::get('/{group}', 'getGroupList')->name('lookup.group.list');
                    Route::get('/', 'getEnumsType')->name('lookup.enums.list');
                });
        });
    });
});
