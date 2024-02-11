<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class MallItem extends Model
{
    use HasFactory;

    protected $table = 'mall_item';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    public $timestamps = true;

    protected $fillable = [
        'name', 'price', 'description', 'image'
    ];

    public function itemCategories(): HasMany
    {
        return $this->hasMany(ItemCategory::class, 'mallItemId', 'id');
    }
}
