<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylorotwell@gmail.com>
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels nice to relax.
|
*/
$xhProf = false;
if (false) {
    xhprof_enable();
    $xhProf = true;
}
require __DIR__ . '/../bootstrap/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__ . '/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

if ($xhProf) {
    $xhProf_data = xhprof_disable();
    include_once "./xhprof/xhprof_lib/utils/xhprof_lib.php";
    include_once "./xhprof/xhprof_lib/utils/xhprof_runs.php";
    $xhProf_runs = new XHProfRuns_Default();
    $run_id = $xhProf_runs->save_run($xhProf_data, "hx");
    echo '<a href="http://www.itsm.com/xhprof/xhprof_html/index.php?run=' . $run_id . '&source=hx" target="_blank">统>计</a>';
}
$response->send();
$kernel->terminate($request, $response);