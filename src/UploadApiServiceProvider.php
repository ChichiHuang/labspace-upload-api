<?php 
namespace Labspace\UploadApi;

use Illuminate\Support\ServiceProvider;

class UploadApiServiceProvider extends ServiceProvider
{


    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        //融合route
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');

        //新增config
        $this->publishes([
            __DIR__.'/../config/labspace-upload-api.php' => config_path('labspace-upload-api.php')
        ], 'config');




    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/labspace-upload-api.php', 'labspace-upload-api'
        );
    }



}
