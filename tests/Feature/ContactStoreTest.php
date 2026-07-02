<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactStoreTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 検証に必要なカテゴリーを作成する
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    public function test_お問い合わせ入力画面が表示される(): void
    {
        $response = $this->get(
            route('contacts.index')
        );

        $response->assertOk();

        $response->assertViewIs('contact.index');

        $response->assertSeeText('お名前');
        $response->assertSeeText('メールアドレス');
        $response->assertSeeText('お問い合わせ内容');
    }

    public function test_お問い合わせフォーム確認ページが表示され入力内容が表示される(): void
    {
        $category = Category::firstOrFail();

        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $response = $this->post(
            route('contacts.confirm'),
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
                    $tag->id,
                ],
            ]
        );

        $response->assertOk();

        $response->assertViewIs('contact.confirm');

        $response->assertSeeText('山田');
        $response->assertSeeText('太郎');
        $response->assertSeeText('yamada@example.com');
        $response->assertSeeText('09012345678');
        $response->assertSeeText('東京都渋谷区');
        $response->assertSeeText('テストビル');
        $response->assertSeeText($category->content);
        $response->assertSeeText('お問い合わせ内容');
        $response->assertSeeText('テストタグ');
    }

    public function test_確認ページ表示時にバリデーションエラーならリダイレクトされエラーが返る(): void
    {
        $category = Category::firstOrFail();

        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $response = $this
            ->from(route('contacts.index'))
            ->post(
                route('contacts.confirm'),
                [
                    'first_name' => '',
                    'last_name' => '太郎',
                    'gender' => 1,
                    'email' => 'yamada@example.com',
                    'tel' => '09012345678',
                    'address' => '東京都渋谷区',
                    'building' => 'テストビル',
                    'category_id' => $category->id,
                    'detail' => 'お問い合わせ内容',
                    'tag_ids' => [
                        $tag->id,
                    ],
                ]
            );

        $response->assertRedirect(
            route('contacts.index')
        );

        $response->assertSessionHasErrors([
            'first_name',
        ]);
    }

    public function test_問い合わせとタグが保存されてサンクスページに遷移する(): void
    {
        $category = Category::firstOrFail();

        $tag1 = Tag::create([
            'name' => 'テスト1',
        ]);

        $tag2 = Tag::create([
            'name' => 'テスト2',
        ]);

        $response = $this->post(
            route('contacts.store'),
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
            route('contacts.thanks')
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
                'building' => 'テストビル',
                'category_id' => $category->id,
                'detail' => 'お問い合わせ内容',
            ]
        );

        $contact = Contact::where('email', 'yamada@example.com')
            ->firstOrFail();

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

    public function test_お問い合わせ送信時にバリデーションエラーならリダイレクトされエラーが返る(): void
    {
        $category = Category::firstOrFail();

        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $response = $this
            ->from(route('contacts.index'))
            ->post(
                route('contacts.store'),
                [
                    'first_name' => '山田',
                    'last_name' => '太郎',
                    'gender' => 1,
                    'email' => 'yamada@example.com',
                    'tel' => 'abcde',
                    'address' => '東京都',
                    'building' => 'テストビル',
                    'category_id' => $category->id,
                    'detail' => 'テスト',
                    'tag_ids' => [
                        $tag->id,
                    ],
                ]
            );

        $response->assertRedirect(
            route('contacts.index')
        );

        $response->assertSessionHasErrors([
            'tel',
        ]);

        $response->assertSessionHasInput([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
        ]);
    }
}
