<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $primaryKey = 'id_forms';
    protected $fillable = ['*'];
    public $timestamps = false;
}
