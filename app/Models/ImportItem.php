<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportItem extends Model
{
    public $incrementing = false; // id из Excel, не автоинкремент
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'name',
        'date',
    ];
}
