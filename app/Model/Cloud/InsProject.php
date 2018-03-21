<?php

namespace Itsm\Model\Cloud;

use Itsm\Model\Model;

class InsProject extends Model {
    protected $connection = 'cloud';
    public $primaryKey = 'Id';
    public $timestamps = false;

    protected $table = 'ins_project';

    protected $fillable = ['Id', 'deleted'];
}
