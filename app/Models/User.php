<?php

namespace App\Models;

use Carbon\Carbon;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Model
{
    use HasFactory, SoftDeletes;

    const DELETED_AT = 'deletedAt';

    public $timestamps = false;

    protected $table = 'users';

    protected $fillable = [
        "_id",
        "firstName",
        "lastName",
        "username",
        "email",
        "password",
        "phone",
        "address",
        "birthDate",
        "profileImagePath",
        "isActive",
        "emailVerificationToken",
        "passwordResetToken",
        "refreshToken",
        "role",
        "permissions",
        "lastLogin",
        "createdAt",
        "updatedAt",
        "deletedAt",
        "createdBy",
        "updatedBy",
        "deletedBy"
    ];

    protected $hidden = [
        "_id",
        "password",
        "emailVerificationToken",
        "passwordResetToken",
        "refreshToken"
    ];

    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn(string $value): string => Carbon::parse($value)->timezone('Africa/Algiers')->toDateTimeString()
        );
    }

    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn(string $value): string => Carbon::parse($value)->timezone('Africa/Algiers')->toDateTimeString()
        );
    }

}
