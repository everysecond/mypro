<?php
/**
 * Created by PhpStorm.
 * User: chenglh
 * Date: 2016/9/26
 * Time: 10:44
 */

namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;

class IssueRecord extends Model
{

    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'issuerecord';
    protected $guarded = [];

}