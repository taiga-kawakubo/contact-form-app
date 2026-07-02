<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactExportTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 検証に必要なユーザーを作成する
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
    }

    private function createContact(array $overrides = []): Contact
    {
        $createdAt = $overrides['created_at'] ?? now();

        unset($overrides['created_at']);

        $contact = Contact::create(array_merge([
            'category_id' => $overrides['category_id'],
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'test@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル',
            'detail' => 'お問い合わせ内容です。',
        ], $overrides));

        $contact->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ])->save();

        return $contact;
    }

    public function test_ログイン済みユーザーはcsvをダウンロードできる(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $this->createContact([
            'category_id' => $category->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'created_at' => '2026-06-01 10:00:00',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export');

        $response->assertOk();

        $response->assertDownload();

        $content = $response->streamedContent();

        $this->assertStringContainsString('yamada@example.com', $content);
    }

    public function test_フィルタ条件付きでcsvをダウンロードできる(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $targetCategory = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $otherCategory = Category::create([
            'content' => '商品の交換について',
        ]);

        $this->createContact([
            'category_id' => $targetCategory->id,
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'target@example.com',
            'created_at' => '2026-06-01 10:00:00',
        ]);

        $this->createContact([
            'category_id' => $otherCategory->id,
            'first_name' => '山田',
            'last_name' => '次郎',
            'gender' => 1,
            'email' => 'other-category@example.com',
            'created_at' => '2026-06-01 10:00:00',
        ]);

        $this->createContact([
            'category_id' => $targetCategory->id,
            'first_name' => '山田',
            'last_name' => '花子',
            'gender' => 2,
            'email' => 'other-gender@example.com',
            'created_at' => '2026-06-01 10:00:00',
        ]);

        $this->createContact([
            'category_id' => $targetCategory->id,
            'first_name' => '佐藤',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'other-keyword@example.com',
            'created_at' => '2026-06-01 10:00:00',
        ]);

        $this->createContact([
            'category_id' => $targetCategory->id,
            'first_name' => '山田',
            'last_name' => '三郎',
            'gender' => 1,
            'email' => 'other-date@example.com',
            'created_at' => '2026-06-02 10:00:00',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export?'.http_build_query([
                'keyword' => '山田',
                'gender' => 1,
                'category_id' => $targetCategory->id,
                'date' => '2026-06-01',
            ]));

        $response->assertOk();

        $response->assertDownload();

        $content = $response->streamedContent();

        $this->assertStringContainsString('target@example.com', $content);

        $this->assertStringNotContainsString('other-category@example.com', $content);
        $this->assertStringNotContainsString('other-gender@example.com', $content);
        $this->assertStringNotContainsString('other-keyword@example.com', $content);
        $this->assertStringNotContainsString('other-date@example.com', $content);
    }

    public function test_フィルタ未指定の場合は新着順でcsvに出力される(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $category = Category::create([
            'content' => '商品のお届けについて',
        ]);

        $this->createContact([
            'category_id' => $category->id,
            'first_name' => '古い',
            'last_name' => '問い合わせ',
            'email' => 'old@example.com',
            'created_at' => '2026-06-01 10:00:00',
        ]);

        $this->createContact([
            'category_id' => $category->id,
            'first_name' => '新しい',
            'last_name' => '問い合わせ',
            'email' => 'new@example.com',
            'created_at' => '2026-06-02 10:00:00',
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/contacts/export');

        $response->assertOk();

        $response->assertDownload();

        $content = $response->streamedContent();

        $this->assertStringContainsString('new@example.com', $content);
        $this->assertStringContainsString('old@example.com', $content);

        $newPosition = mb_strpos($content, 'new@example.com');
        $oldPosition = mb_strpos($content, 'old@example.com');

        $this->assertNotFalse($newPosition);
        $this->assertNotFalse($oldPosition);

        $this->assertLessThan($oldPosition, $newPosition);
    }

    public function test_未認証ユーザーは_cs_vをダウンロードできない(): void
    {
        $response = $this->get('/contacts/export');

        $response->assertRedirect(route('login'));
    }
}
