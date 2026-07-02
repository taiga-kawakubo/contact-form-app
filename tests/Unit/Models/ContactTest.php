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

        $contactCategory = $contact->category;

        $this->assertEquals(
            $category->id,
            $contactCategory->id
        );
        $this->assertSame(
            '商品のお届けについて',
            $contactCategory->content
        );
    }

    public function test_お問い合わせはタグと多対多のリレーションを同期できる(): void
    {
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

        $contact->tags()->sync([
            $tag1->id,
            $tag2->id,
        ]);

        $contact->load('tags');

        $this->assertCount(2, $contact->tags);

        $this->assertEqualsCanonicalizing(
            [
                $tag1->id,
                $tag2->id,
            ],
            $contact->tags->pluck('id')->all()
        );
    }
}
