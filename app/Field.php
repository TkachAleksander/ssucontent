<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $table = 'fields';
    protected $fillable = ['id_elements','label_fields'];
    public $timestamps = false;
}
