<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $primaryKey = 'id_messages';
    protected $fillable = ['is_read'];
    public $timestamps = true;
}
