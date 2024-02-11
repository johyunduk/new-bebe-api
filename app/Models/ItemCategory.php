<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemCategory extends Model
{
    use HasFactory;

    protected $table = 'item_category';

    const CREATED_AT = 'createdAt';
    const UPDATED_AT = 'updatedAt';

    public $timestamps = true;

    protected $fillable = [
        'mallItemId', 'categoryId'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categoryId', 'id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(MallItem::class, 'mallItemId', 'id');
    }
}
