<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Fields_form extends Model
{
//    protected $table
    protected $primaryKey = 'id_fields_forms';
    protected $fillable = ['*'];
    public $timestamps = false;
}
