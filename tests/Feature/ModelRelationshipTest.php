<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;




class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_many_contacts(): void
    {
        // テスト用のCategoryとContact1・Contact2を作成
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

        // Categoryの取得
        $contacts = $category->contacts;

        // Categoryから2件のContactを取得できることを確認
        $this->assertCount(2,$contacts);
    }


    public function test_contact_belongs_to_category(): void
    {
        // テスト用のCategoryとContact1・Contact2を作成
        $category = Category::create([
            'content' => '商品のお届けについて'
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

    public function test_contact_can_sync_tags(): void
    {
        //テスト用のCategory・Contact・Tagを作成
        $category = Category::create([
                    'content' => '商品のお届けについて'
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
            'name' => '重要'
        ]);
        $tag2 = Tag::create([
            'name' => '至急'
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

    public function test_tag_belongs_to_many_contacts(): void
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
