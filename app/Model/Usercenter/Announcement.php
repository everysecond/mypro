<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/31 0031
 * Time: 下午 7:28
 */

namespace Itsm\Model\Usercenter;

use Itsm\Model\Model;
use Itsm\Model\Res\AuxDict;
use Itsm\Model\Res\AuxStuff;

class Announcement extends Model
{

    protected $connection = 'usercenter';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'Announcement';
    protected $guarded = [];
  protected $fillable = [
      'Id'
  ];
}