<?php

namespace Kvaksrud\IbmCos;

use Illuminate\Support\ServiceProvider;

class IbmCosServiceProvider extends ServiceProvider{

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/ibm-cos.php' => config_path('ibm-cos.php')
        ]);
    }

    public function register()
    {

    }

}
