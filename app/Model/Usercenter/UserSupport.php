<?php
/**
 * Created by PhpStorm.
 * User: chenglh
 * Date: 2016/8/12
 * Time: 14:05
 */

namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;

class UserSupport extends Model
{


    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'support';

    protected $fillable = [
        'Id',
        'Title',
        'Body',
        'OperationId',
        'Status',
        'UpTs',
        'ChargeUserId',
        'ClassInficationOne',
        'dose',
        'ProcessTs'
    ];

}
