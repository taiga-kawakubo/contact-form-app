<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactIndexApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
    }

    public function test_お問い合わせ一覧を取得できる(): void
    {
        $category = Category::firstOrFail();
        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
        ]);

        $response = $this->getJson('/api/v1/contacts');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'detail' => 'お問い合わせ内容',
        ]);
    }

    public function test_お問い合わせ一覧のレスポンス構造が正しい(): void
    {
        $category = Category::firstOrFail();

        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $contact = Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
        ]);

        $contact->tags()->attach($tag->id);

        $response = $this->getJson('/api/v1/contacts');

        $response->assertStatus(200);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'category' => [
                        'id',
                        'content',
                    ],
                    'first_name',
                    'last_name',
                    'gender',
                    'email',
                    'tel',
                    'address',
                    'building',
                    'detail',
                    'tags' => [
                        '*' => [
                            'id',
                            'name',
                        ],
                    ],
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_一覧取得時にカテゴリ情報が含まれる(): void
    {
        $category = Category::firstOrFail();

        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
        ]);

        $response = $this->getJson('/api/v1/contacts');

        $response->assertOk();

        $response->assertJsonPath(
            'data.0.category.content',
            $category->content
        );
    }

    public function test_一覧取得時にタグ情報が含まれる(): void
    {
        $category = Category::firstOrFail();

        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $contact = Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
        ]);

        $contact->tags()->attach($tag->id);

        $response = $this->getJson('/api/v1/contacts');

        $response->assertStatus(200);

        $response->assertJsonPath(
            'data.0.tags.0.id',
            $tag->id
        );

        $response->assertJsonPath(
            'data.0.tags.0.name',
            'テストタグ'
        );
    }

    public function test_姓のキーワード検索ができる(): void
    {
        $category = Category::firstOrFail();

        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
        ]);

        Contact::factory()->create([
            'first_name' => '佐藤',
            'last_name' => '花子',
            'email' => 'sato@example.com',
            'tel' => '08012345678',
            'category_id' => $category->id,
            'detail' => '別のお問い合わせ内容',
        ]);

        $response = $this->getJson('/api/v1/contacts?keyword=山田');

        $response->assertOk();

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.first_name', '山田');
        $response->assertJsonPath('data.0.last_name', '太郎');
        $response->assertJsonPath('data.0.email', 'yamada@example.com');

        $response->assertJsonMissing([
            'first_name' => '佐藤',
        ]);
    }

    public function test_名のキーワード検索ができる(): void
    {
        $category = Category::firstOrFail();

        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
        ]);

        Contact::factory()->create([
            'first_name' => '佐藤',
            'last_name' => '花子',
            'email' => 'sato@example.com',
            'tel' => '08012345678',
            'category_id' => $category->id,
            'detail' => '別のお問い合わせ内容',
        ]);

        $response = $this->getJson('/api/v1/contacts?keyword=太郎');

        $response->assertOk();

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.first_name', '山田');
        $response->assertJsonPath('data.0.last_name', '太郎');
        $response->assertJsonPath('data.0.email', 'yamada@example.com');

        $response->assertJsonMissing([
            'last_name' => '花子',
        ]);
    }

    public function test_メールアドレスのキーワード検索ができる(): void
    {
        $category = Category::firstOrFail();

        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
        ]);

        Contact::factory()->create([
            'first_name' => '佐藤',
            'last_name' => '花子',
            'email' => 'sato@example.com',
            'tel' => '08012345678',
            'category_id' => $category->id,
            'detail' => '別のお問い合わせ内容',
        ]);

        $response = $this->getJson('/api/v1/contacts?keyword=yamada@example.com');

        $response->assertOk();

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.first_name', '山田');
        $response->assertJsonPath('data.0.last_name', '太郎');
        $response->assertJsonPath('data.0.email', 'yamada@example.com');

        $response->assertJsonMissing([
            'email' => 'sato@example.com',
        ]);
    }

    public function test_フルネーム検索ができる_空白なし(): void
    {
        $category = Category::firstOrFail();

        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
        ]);

        Contact::factory()->create([
            'first_name' => '佐藤',
            'last_name' => '花子',
            'email' => 'sato@example.com',
            'tel' => '08012345678',
            'category_id' => $category->id,
            'detail' => '別のお問い合わせ内容',
        ]);

        $response = $this->getJson('/api/v1/contacts?keyword=山田太郎');

        $response->assertOk();

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.first_name', '山田');
        $response->assertJsonPath('data.0.last_name', '太郎');
        $response->assertJsonPath('data.0.email', 'yamada@example.com');

        $response->assertJsonMissing([
            'first_name' => '佐藤',
        ]);
    }

    public function test_フルネーム検索ができる_空白あり(): void
    {
        $category = Category::firstOrFail();

        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
        ]);

        Contact::factory()->create([
            'first_name' => '佐藤',
            'last_name' => '花子',
            'email' => 'sato@example.com',
            'tel' => '08012345678',
            'category_id' => $category->id,
            'detail' => '別のお問い合わせ内容',
        ]);

        $response = $this->getJson(
            '/api/v1/contacts?'.http_build_query([
                'keyword' => '山田 太郎',
            ])
        );

        $response->assertOk();

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.first_name', '山田');
        $response->assertJsonPath('data.0.last_name', '太郎');
        $response->assertJsonPath('data.0.email', 'yamada@example.com');

        $response->assertJsonMissing([
            'first_name' => '佐藤',
        ]);
    }

    public function test_性別検索ができる(): void
    {
        Contact::factory()->create([
            'first_name' => '男性ユーザー',
            'gender' => 1,
        ]);

        Contact::factory()->create([
            'first_name' => '女性ユーザー',
            'gender' => 2,
        ]);

        $response = $this->getJson('/api/v1/contacts?gender=1');

        $response->assertOk();

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.first_name', '男性ユーザー');
        $response->assertJsonPath('data.0.gender', 1);

        $response->assertJsonMissing([
            'first_name' => '女性ユーザー',
        ]);
    }

    public function test_カテゴリ検索ができる(): void
    {
        $category1 = Category::findOrFail(1);
        $category2 = Category::findOrFail(2);
        Contact::factory()->create([
            'first_name' => '対象',
            'category_id' => $category1->id,
        ]);
        Contact::factory()->create([
            'first_name' => '対象外',
            'category_id' => $category2->id,
        ]);

        $response = $this->getJson('/api/v1/contacts?category_id='.$category1->id);

        $response->assertOk();

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.first_name', '対象');
        $response->assertJsonPath('data.0.category.content', $category1->content);
        $response->assertJsonPath('data.0.category.id', $category1->id);

        $response->assertJsonMissing([
            'first_name' => '対象外',
        ]);
    }

    public function test_作成日検索ができる(): void
    {

        Contact::factory()->create([
            'first_name' => '今日のデータ',
            'created_at' => '2026-06-24',
        ]);
        Contact::factory()->create([
            'first_name' => '昨日のデータ',
            'created_at' => '2026-06-23',
        ]);

        $response = $this->getJson('/api/v1/contacts?date=2026-06-24');

        $response->assertOk();

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.first_name', '今日のデータ');

        $response->assertJsonMissing([
            'first_name' => '昨日のデータ',
        ]);
    }

    public function test_複数条件で検索ができる(): void
    {
        $category1 = Category::findOrFail(1);
        $category2 = Category::findOrFail(2);

        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'category_id' => $category1->id,
            'created_at' => '2026-06-24 10:00:00',
        ]);

        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '花子',
            'gender' => 2,
            'email' => 'yamada-hanako@example.com',
            'category_id' => $category1->id,
            'created_at' => '2026-06-24 10:00:00',
        ]);

        Contact::factory()->create([
            'first_name' => '佐藤',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'sato@example.com',
            'category_id' => $category1->id,
            'created_at' => '2026-06-24 10:00:00',
        ]);

        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '次郎',
            'gender' => 1,
            'email' => 'yamada-jiro@example.com',
            'category_id' => $category2->id,
            'created_at' => '2026-06-24 10:00:00',
        ]);

        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '三郎',
            'gender' => 1,
            'email' => 'yamada-saburo@example.com',
            'category_id' => $category1->id,
            'created_at' => '2026-06-23 10:00:00',
        ]);

        $response = $this->getJson(
            '/api/v1/contacts?'.http_build_query([
                'keyword' => '山田',
                'gender' => 1,
                'category_id' => $category1->id,
                'date' => '2026-06-24',
            ])
        );

        $response->assertOk();

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.first_name', '山田');
        $response->assertJsonPath('data.0.last_name', '太郎');
        $response->assertJsonPath('data.0.gender', 1);
        $response->assertJsonPath('data.0.email', 'yamada@example.com');
        $response->assertJsonPath('data.0.category.id', $category1->id);

        $response->assertJsonMissing([
            'email' => 'yamada-hanako@example.com',
        ]);

        $response->assertJsonMissing([
            'email' => 'sato@example.com',
        ]);

        $response->assertJsonMissing([
            'email' => 'yamada-jiro@example.com',
        ]);

        $response->assertJsonMissing([
            'email' => 'yamada-saburo@example.com',
        ]);
    }

    public function test_お問い合わせ一覧は作成日時の新しい順で返る(): void
    {
        $category = Category::firstOrFail();

        Contact::factory()->create([
            'first_name' => '古いデータ',
            'email' => 'old@example.com',
            'category_id' => $category->id,
            'created_at' => '2026-06-22',
        ]);

        Contact::factory()->create([
            'first_name' => '新しいデータ',
            'email' => 'new@example.com',
            'category_id' => $category->id,
            'created_at' => '2026-06-24',
        ]);

        Contact::factory()->create([
            'first_name' => '中間のデータ',
            'email' => 'middle@example.com',
            'category_id' => $category->id,
            'created_at' => '2026-06-23',
        ]);

        $response = $this->getJson('/api/v1/contacts');

        $response->assertOk();

        $response->assertJsonCount(3, 'data');

        $response->assertJsonPath('data.0.first_name', '新しいデータ');
        $response->assertJsonPath('data.1.first_name', '中間のデータ');
        $response->assertJsonPath('data.2.first_name', '古いデータ');
    }

    public function test_お問い合わせ一覧はデフォルト20件でページネーションされる(): void
    {
        $category = Category::firstOrFail();

        Contact::factory()
            ->count(21)
            ->sequence(function ($sequence) use ($category) {
                return [
                    'first_name' => '山田'.$sequence->index,
                    'last_name' => '太郎',
                    'email' => 'yamada'.$sequence->index.'@example.com',
                    'category_id' => $category->id,
                    'created_at' => now()->subMinutes($sequence->index),
                ];
            })
            ->create();

        $response = $this->getJson('/api/v1/contacts');

        $response->assertOk();

        $response->assertJsonCount(20, 'data');

        $response->assertJsonPath('meta.per_page', 20);
        $response->assertJsonPath('meta.total', 21);
        $response->assertJsonPath('meta.current_page', 1);
        $response->assertJsonPath('meta.last_page', 2);

    }

    public function test_per_pageを指定すると指定件数で返る(): void
    {
        $category = Category::firstOrFail();

        Contact::factory()
            ->count(6)
            ->sequence(function ($sequence) use ($category) {
                return [
                    'first_name' => '山田'.$sequence->index,
                    'last_name' => '太郎',
                    'email' => 'yamada'.$sequence->index.'@example.com',
                    'category_id' => $category->id,
                    'created_at' => now()->subMinutes($sequence->index),
                ];
            })
            ->create();

        $response = $this->getJson('/api/v1/contacts?per_page=5');

        $response->assertOk();

        $response->assertJsonCount(5, 'data');

        $response->assertJsonPath('meta.per_page', 5);
        $response->assertJsonPath('meta.total', 6);
        $response->assertJsonPath('meta.current_page', 1);
        $response->assertJsonPath('meta.last_page', 2);
    }

    public function test_ページネーションのメタ情報が返される(): void
    {
        $category = Category::firstOrFail();

        Contact::factory()
            ->count(21)
            ->sequence(function ($sequence) use ($category) {
                return [
                    'first_name' => '山田'.$sequence->index,
                    'last_name' => '太郎',
                    'email' => 'yamada'.$sequence->index.'@example.com',
                    'category_id' => $category->id,
                    'created_at' => now()->subMinutes($sequence->index),
                ];
            })
            ->create();

        $response = $this->getJson('/api/v1/contacts');

        $response->assertOk();

        $response->assertJsonCount(20, 'data');

        $response->assertJsonStructure([
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'path',
                'per_page',
                'to',
                'total',
            ],
        ]);

        $response->assertJsonPath('meta.current_page', 1);
        $response->assertJsonPath('meta.from', 1);
        $response->assertJsonPath('meta.last_page', 2);
        $response->assertJsonPath('meta.per_page', 20);
        $response->assertJsonPath('meta.to', 20);
        $response->assertJsonPath('meta.total', 21);
    }

    public function test_pageを指定すると指定ページのデータが返る(): void
    {
        $category = Category::firstOrFail();

        Contact::factory()
            ->count(21)
            ->sequence(function ($sequence) use ($category) {
                return [
                    'first_name' => '山田'.$sequence->index,
                    'last_name' => '太郎',
                    'email' => 'yamada'.$sequence->index.'@example.com',
                    'category_id' => $category->id,
                    'created_at' => now()->subMinutes($sequence->index),
                ];
            })
            ->create();

        $response = $this->getJson('/api/v1/contacts?page=2');

        $response->assertOk();

        $response->assertJsonCount(1, 'data');

        $response->assertJsonPath('data.0.first_name', '山田20');

        $response->assertJsonPath('meta.per_page', 20);
        $response->assertJsonPath('meta.current_page', 2);
    }

    public function test_検索結果が0件の場合は空のdata配列が帰る(): void
    {
        $category = Category::firstOrFail();

        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/contacts?keyword=存在しない文字');

        $response->assertOk();

        $response->assertJsonCount(0, 'data');

        $response->assertJsonPath('data', []);
    }

    public function test_キーワード検索で256文字はバリデーションエラーになる(): void
    {
        $keyword = str_repeat('a', 256);

        $response = $this->getJson(
            '/api/v1/contacts?'.http_build_query([
                'keyword' => $keyword,
            ])
        );

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'keyword',
        ]);
    }

    public function test_キーワード検索で255文字はバリデーションエラーにならない(): void
    {
        $keyword = str_repeat('a', 255);

        $response = $this->getJson(
            '/api/v1/contacts?'.http_build_query([
                'keyword' => $keyword,
            ])
        );

        $response->assertOk();
        $response->assertJsonMissingValidationErrors([
            'keyword',
        ]);
    }

    public function test_不正な性別値はバリデーションエラーとなりメッセージが返る(): void
    {
        $response = $this->getJson('/api/v1/contacts?gender=999');

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'gender',
        ]);

        $response->assertJsonPath(
            'errors.gender.0',
            '性別の値が不正です'
        );
    }

    public function test_存在しないカテゴリーidはバリデーションエラーとなりメッセージが返る(): void
    {
        $response = $this->getJson('/api/v1/contacts?category_id=999999');

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'category_id',
        ]);

        $response->assertJsonPath(
            'errors.category_id.0',
            '選択されたカテゴリーが存在しません'
        );
    }

    public function test_日付形式でない入力はバリデーションエラーになる(): void
    {
        $response = $this->getJson('/api/v1/contacts?date=abc');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['date']);
    }
}
