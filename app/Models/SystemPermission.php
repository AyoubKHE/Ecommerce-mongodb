<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class SystemPermission extends Model
{
    use HasFactory;

    protected $table = 'systemPermissions';

    public $timestamps = false;

    protected $fillable = [
        "name",
        "options",
    ];

    protected $casts = [
        "options" => "array",
    ];
}
