<?php

namespace clk528\NyuJiaoWei;


use Illuminate\Routing\Router;

class NyuJiaoWei
{

    public static function routes()
    {
        \Route::namespace('clk528\\NyuJiaoWei\\Controllers')->group(function (Router $router) {
            $router->group(['prefix' => 'upload'], function (Router $router) {

                $router->group(['prefix' => 'staff'], function (Router $router) {
                    $router->get('/', 'UploadController@index')->name('admin.staff.index');

                    $router->get('import', 'UploadController@create')->name('admin.staff.import');
                    $router->post('import', 'UploadController@import');
                });

                $router->group(['prefix' => 'teacher'], function (Router $router) {
                    $router->get('/', 'UploadController@index')->name('admin.teacher.index');

                    $router->get('import', 'UploadController@create')->name('admin.teacher.import');
                    $router->post('import', 'UploadController@import');
                });

                $router->group(['prefix' => 'student-other'], function (Router $router) {
                    $router->get('/', 'UploadController@index')->name('admin.student-other.index');

                    $router->get('import', 'UploadController@create')->name('admin.student-other.import');
                    $router->post('import', 'UploadController@import');
                });

                $router->group(['prefix' => 'master-or-doctoral'], function (Router $router) {
                    $router->get('/', 'UploadController@index')->name('admin.master-or-doctoral.index');

                    $router->get('import', 'UploadController@create')->name('admin.master-or-doctoral.import');
                    $router->post('import', 'UploadController@import');
                });
                $router->group(['prefix' => 'student-go-local'], function (Router $router) {
                    $router->get('/', 'UploadController@index')->name('admin.student-go-local.index');

                    $router->get('import', 'UploadController@create')->name('admin.student-go-local.import');
                    $router->post('import', 'UploadController@import');
                });

                $router->group(['prefix' => 'student-undergraduate'], function (Router $router) {
                    $router->get('/', 'UploadController@index')->name('admin.student-undergraduate.index');

                    $router->get('import', 'UploadController@create')->name('admin.student-undergraduate.import');
                    $router->post('import', 'UploadController@import');
                });
            });

            $router->resource('audit', 'AuditController', [
                'as' => config('admin.route.prefix') . '.',
            ]);
        });
    }
}
