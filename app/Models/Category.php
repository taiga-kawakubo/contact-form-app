<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'content',
    ];

    /**
     * このカテゴリーに属するお問い合わせ一覧を取得する。
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }
}
