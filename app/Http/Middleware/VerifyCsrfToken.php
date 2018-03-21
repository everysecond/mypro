<?php

namespace Itsm\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'kindeditor/uploadify',
        'kindeditor/uploadfile',
        'kindeditor/uploadExcel',
        'kindeditor/uploadProvider',
        'kindeditor/uploadTypeAndProd'
    ];
}
