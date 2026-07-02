<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Contact extends Model
{
    use HasFactory;

    /**
     * 複数代入可能な属性
     */
    protected $fillable = [
        'category_id',
        'first_name',
        'last_name',
        'gender',
        'email',
        'tel',
        'address',
        'building',
        'detail',
    ];

    /**
     * このお問い合わせが属するカテゴリーを取得
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * お問い合わせに紐づくタグ一覧を取得する
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)
            ->withTimestamps();
    }

    /**
     * キャストする属性
     */
    protected $casts = [
        'gender' => 'integer',
    ];

    /**
     * 性別の表示ラベルを取得する
     */
    public function getGenderLabelAttribute(): string
    {
        return match ($this->gender) {
            1 => '男性',
            2 => '女性',
            3 => 'その他',
            default => '未設定',
        };
    }
}
