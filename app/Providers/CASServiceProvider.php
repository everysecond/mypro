<?php
/**
 * api-gateway
 *
 * User: Wudi<wudi@51idc.com>
 * Date: 16/4/7 15:14
 */

namespace Itsm\Providers;

use phpCAS;
use Illuminate\Support\ServiceProvider;

class CASServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('CAS', function ($app) {
            $cas = new phpCAS();
            $cas::client(CAS_VERSION_2_0, env('CAS_HOST'), (int)env('CAS_PORT'), env('CAS_CONTEXT'));
            $cas::setNoCasServerValidation();

            return $cas;
        });
    }

    public function provides()
    {
        return array('CAS');
    }

}