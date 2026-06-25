<?php

namespace Tests\Unit\Validation;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactStoreTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    public function test_問い合わせとタグが保存されて、サンクスページに遷移する(): void
    {
        $category = Category::first();
        $tag1 = Tag::create(['name' => 'テスト1']);
        $tag2 = Tag::create(['name' => 'テスト2']);

        $response = $this->post(
            route('contact.store'),
            [
                'first_name' => '山田',
                'last_name' => '太郎',
                'gender' => 1,
                'email' => 'yamada@example.com',
                'tel' => '09012345678',
                'address' => '東京都渋谷区',
                'building' => 'テストビル',
                'category_id' => $category->id,
                'detail' => 'お問い合わせ内容',
                'tag_ids' => [
                    $tag1->id,
                    $tag2->id,
                ],
            ]
        );

        $response->assertRedirect(
            route('contact.thanks')
        );
        $this->assertDatabaseHas(
            'contacts',
            [
                'first_name' => '山田',
                'last_name' => '太郎',
                'gender' => 1,
                'email' => 'yamada@example.com',
                'tel' => '09012345678',
                'address' => '東京都渋谷区',
                'category_id' => $category->id,
                'detail' => 'お問い合わせ内容',
            ]
        );
    }

    public function test_お問い合わせとタグの多対多リレーションが同期され保存される(): void
    {
        $contact = Contact::factory()->create();
        $tag1 = Tag::create(['name' => 'テスト１']);
        $tag2 = Tag::create(['name' => 'テスト２']);

        $contact->tags()->sync([
            $tag1->id,
            $tag2->id,
        ]);

        $this->assertDatabaseHas(
            'contact_tag',
            [
                'contact_id' => $contact->id,
                'tag_id' => $tag1->id,
            ]
        );
        $this->assertDatabaseHas(
            'contact_tag',
            [
                'contact_id' => $contact->id,
                'tag_id' => $tag2->id,
            ]
        );
    }

    public function test_不正な電話番号は拒否する(): void
    {
        $category = Category::first();

        $response = $this->post(
            route('contact.store'),
            [
                'first_name' => '山田',
                'last_name' => '太郎',
                'gender' => 1,
                'email' => 'yamada@example.com',
                // 不正
                'tel' => 'abcde',
                'address' => '東京都',
                'building' => 'テストビル',
                'category_id' => $category->id,
                'detail' => 'テスト',
            ]
        );

        $response->assertSessionHasErrors([
            'tel',
        ]);
    }
}
