<?php

use App\Http\Controllers\v1\AgentCompanyController;
use App\Http\Controllers\v1\Auth0\ClientCredentialController;
use App\Http\Controllers\v1\Auth0\UserController as Auth0UserController;
use App\Http\Controllers\v1\Auth\UserController;
use App\Http\Controllers\v1\BusinessController;
use App\Http\Controllers\v1\BusinessCorporateController;
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

        Route::group(['prefix' => 'portal/auth', 'middleware' => ['m2m']], function () {
            Route::controller(Auth0UserController::class)
                ->group(function () {
                    Route::group(['middleware' => ['restrict.query']], function () {
                        Route::get('/users/{userId}/invitations', 'getInvitations')->name('portal.auth.get-invitations');
                        Route::get('/users/{userId}/resend-invitation', 'resendInvitation')->name('portal.auth.resend-invitation');
                        Route::get('/roles', 'getRoles')->name('portal.auth.get-roles');
                        Route::post('/users', 'store')->name('portal.auth.store');
                        Route::put('/users/{userId}', 'update')->name('portal.auth.update');
                    });
                    Route::get('/users', 'index')->name('portal.auth.users');
                });

            Route::controller(AgentCompanyController::class)
                ->middleware(['middleware' => 'restrict.query'])
                ->group(function () {
                    Route::post('/companies', 'store')->name('portal.companies.store');
                    Route::get('/companies', 'index')->name('portal.companies.index');
                });
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
                    Route::put('/{business}/save', 'saveProgress')->name('business.save');
                    // Route::get('{business}/download', 'downloadDeclaration')->name('declaration.download');
                    // Route::get('/{documentType}/{columnName}/download', 'downloadDocuments')->name('documents.download');
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

        Route::group(['middleware' => ['client.type:app']], function () {
            Route::prefix('/businesses-corporate')
                ->controller(BusinessCorporateController::class)
                ->name('businesses.corporate')
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::put('/update-entities/{business}', 'updatePoliticalEntityPerson')->name('entities.update');
                    Route::post('/indicias/{business}', 'createIndicias')->name('indicias.create');
                    Route::put('/update-indicias/{business}', 'updateIndicias')->name('indicias.update');
                    Route::put('/{business}/submit', 'submitApplication')->name('corporate.submit');
                    Route::delete('/{business}', 'deleteApplication')->name('delete');

                    Route::group(['middleware' => ['restrict.query']], function () {
                        Route::post('/', 'create')->name('create');
                        Route::put('/{business}/save', 'saveProgress')->name('save');

                        #Documents
                        Route::get('{business}/download', 'downloadDeclaration')->name('declaration.download');
                        Route::get('/{documentType}/{columnName}/download', 'downloadDocuments')->name('documents.download');
                        Route::post('{business}/upload-document', 'uploadDocument')->name('document.upload');
                        Route::post('{business}/download-document', 'dowloadDocument')->name('document.download');
                        Route::post('{business}/remove-document', 'removeDocument')->name('remove.download');
                        Route::post('{business}/remove-comp-rep-document', 'removeCompanyRepresentativeDocument')->name('remove.comprep-document');

                        #Business Products
                        Route::prefix('/product')
                            ->controller(BusinessCorporateController::class)
                            ->group(function () {
                                Route::get('/{business}', 'getProducts')->name('product.index');
                                Route::post('/{business}', 'createProducts')->name('product.create');
                                Route::put('/{business}', 'updateProducts')->name('product.update');
                        });

                        #Business Company
                        Route::prefix('/company')
                            ->controller(BusinessCorporateController::class)
                            ->group(function () {
                                Route::get('/{business}/{section?}', 'getSection')->name('company.sections');
                        });

                        #Share Invite
                        Route::group(['middleware' => ['role:agent']], function () {
                            Route::post('/send-invite', 'invite')->name('.invite');
                        });

                        Route::post('/share-application', 'shareApplication')->name('.share-application');
                    });
            });
        });
    });
});
