<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportItem extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'int';
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'name',
        'date',
    ];
}
