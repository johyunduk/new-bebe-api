<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Diary extends Model
{
    use HasFactory;

    protected $table = 'diary';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    public $timestamps = true;

    protected $fillable = [
        'title', 'content', 'weight', 'height', 'userId'
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }
}
