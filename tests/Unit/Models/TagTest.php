<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;



class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_タグは複数のお問い合わせを持つ(): void
    {
        
        //テスト用のCategory・Contact・Tagを作成
        $category = Category::create([
                    'content' => '商品のお届けについて'
                ]);
        $contact1 = Contact::create([
            'category_id' => $category->id,
            'first_name' => '太郎',
            'last_name' => '山田',
            'gender' => 1,
            'email' => 'taro@example.com',
            'tel' => '09012345678',
            'address' => '福岡県福岡市',
            'building' => null,
            'detail' => '商品についての問い合わせ',
        ]);
        $contact2 = Contact::create([
            'category_id' => $category->id,
            'first_name' => '花子',
            'last_name' => '佐藤',
            'gender' => 2,
            'email' => 'hanako@example.com',
            'tel' => '08012345678',
            'address' => '福岡県春日市',
            'building' => 'サンプルマンション101',
            'detail' => '配送についての問い合わせ',
        ]);
        $tag = Tag::create([
            'name' => '重要'
        ]);

        // Tagと2件のContactを関連付ける
        $tag->contacts()->sync([
            $contact1->id,
            $contact2->id,
        ]);
        $contacts = $tag->contacts;

        // Tagに関連するContactが2件取得できることを確認
        $this->assertCount(
            2,
            $contacts
        );
    }
}
