<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_お問い合わせが一つのカテゴリーを持つ(): void
    {
        // テスト用のCategoryとContact1・Contact2を作成
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);
        $contact = Contact::create([
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

        // Contactから関連するCategoryを取得
        $contactCategory = $contact->category;

        // Contactが正しいCategoryに属していることを確認
        $this->assertEquals(
            $category->id,
            $contactCategory->id
        );
        // Category名が正しく取得できることを確認
        $this->assertEquals(
            '商品のお届けについて',
            $contactCategory->content
        );
    }

    public function test_お問い合わせはタグと多対多のリレーションを同期できる(): void
    {
        // テスト用のCategory・Contact・Tagを作成
        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $contact = Contact::create([
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

        $tag1 = Tag::create([
            'name' => '重要',
        ]);
        $tag2 = Tag::create([
            'name' => '至急',
        ]);

        // Contactに2つのTagを同期する
        $contact->tags()->sync([
            $tag1->id,
            $tag2->id,
        ]);

        // Contactに関連するTagが2件取得できることを確認
        $this->assertCount(
            2,
            $contact->tags
        );
    }
}
