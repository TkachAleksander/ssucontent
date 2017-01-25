<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fields_forms_current extends Model
{
    protected $table = 'fields_forms_current';
    protected $primaryKey = 'id_fields_forms_current';
    protected $guarded = ['id_fields_forms_current'];
    public $timestamps = false;
}
