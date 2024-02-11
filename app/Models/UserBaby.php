<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBaby extends Model
{
    use HasFactory;

    protected $table = 'user_baby';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    public $timestamps = true;

    protected $fillable = [
        'name', 'birthDate', 'gender', 'face', 'expectDate', 'pregnantDate', 'userId'
    ];
}
