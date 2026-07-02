<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactStoreApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
    }

    private function validData(array $override = []): array
    {
        $category = Category::firstOrFail();
        $tag = Tag::firstOrCreate([
            'name' => 'テストタグ',
        ]);

        return array_merge([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => [$tag->id],
        ], $override);
    }

    public function test_お問い合わせを新規登録できる(): void
    {
        $response = $this->postJson('/api/v1/contacts',
            $this->validData()
        );

        $response->assertCreated();

        $this->assertDatabaseHas('contacts', [
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル',
            'detail' => 'お問い合わせ内容です',
        ]);
    }

    public function test_登録成功時のレスポンス構造が正しい(): void
    {
        $response = $this->postJson('/api/v1/contacts', $this->validData());

        $response->assertCreated();

        $response->assertJsonStructure([
            'data' => [
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
        ]);
    }

    public function test_登録成功時にカテゴリ情報がレスポンスに含まれる(): void
    {
        $data = $this->validData();

        $category = Category::findOrFail($data['category_id']);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertCreated();

        $response->assertJsonPath('data.category.id', $category->id);
        $response->assertJsonPath('data.category.content', $category->content);
    }

    public function test_タグを指定して登録すると中間テーブルに保存される(): void
    {
        $tag = Tag::create([
            'name' => '指定タグ',
        ]);

        $response = $this->postJson('/api/v1/contacts', $this->validData([
            'tag_ids' => [$tag->id],
        ]));

        $response->assertCreated();

        $response->assertJsonPath('data.tags.0.id', $tag->id);
        $response->assertJsonPath('data.tags.0.name', $tag->name);

        $contactId = $response->json('data.id');

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contactId,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_tag_idsを指定しなくても登録できる(): void
    {
        $data = $this->validData();
        unset($data['tag_ids']);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertCreated();
        $response->assertJsonCount(0, 'data.tags');
    }

    public function test_tag_idsが空配列でも登録できる(): void
    {
        $data = $this->validData([
            'tag_ids' => [],
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertCreated();
        $response->assertJsonCount(0, 'data.tags');
    }

    public function test_buildingを指定しなくても登録できる(): void
    {
        $data = $this->validData();
        unset($data['building']);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertCreated();
        $response->assertJsonPath('data.building', null);
    }

    public function test_first_nameが空の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData([
            'first_name' => '',
        ]);
        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'first_name',
        ]);
    }

    public function test_last_nameが空の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData([
            'last_name' => '',
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'last_name',
        ]);
    }

    public function test_genderが空の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData([
            'gender' => '',
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'gender',
        ]);
    }

    public function test_emailが空の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData([
            'email' => '',
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'email',
        ]);
    }

    public function test_telが空の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData([
            'tel' => '',
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'tel',
        ]);
    }

    public function test_addressが空の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData([
            'address' => '',
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'address',
        ]);
    }

    public function test_category_idが空の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData([
            'category_id' => '',
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'category_id',
        ]);
    }

    public function test_detailが空の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData([
            'detail' => '',
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'detail',
        ]);
    }

    public function test_存在しないgenderはバリデーションエラーとなりメッセージが返る(): void
    {
        $data = $this->validData([
            'gender' => 999,
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'gender',
        ]);

        $this->assertSame(
            '性別の値が不正です',
            $response->json('errors.gender.0')
        );
    }

    public function test_存在しないcategory_idはバリデーションエラーとなりメッセージが返る(): void
    {
        $data = $this->validData([
            'category_id' => 999999,
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'category_id',
        ]);
        $this->assertSame(
            '選択されたカテゴリーが存在しません',
            $response->json('errors.category_id.0')
        );
    }

    public function test_存在しないtag_idsはバリデーションエラーとなりメッセージが返る(): void
    {
        $data = $this->validData([
            'tag_ids' => [999999],
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'tag_ids.0',
        ]);
        $this->assertSame(
            '選択されたタグが存在しません',
            $response->json('errors')['tag_ids.0'][0]
        );
    }

    public function test_tag_idsが配列でない場合はバリデーションエラーになる(): void
    {
        $data = $this->validData([
            'tag_ids' => 'タグ',
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'tag_ids',
        ]);
    }

    public function test_email形式でない場合はバリデーションエラーになる(): void
    {
        $data = $this->validData([
            'email' => 'not-email',
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'email',
        ]);
    }

    public function test_telが不正な場合はバリデーションエラーとなりメッセージが返る(): void
    {
        $data = $this->validData([
            'tel' => '090-aaaa-bbbb',
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'tel',
        ]);

        $this->assertSame(
            '電話番号はハイフンなしの10〜11桁で入力してください',
            $response->json('errors.tel.0')
        );
    }

    public function test_first_nameは255文字まで登録できる(): void
    {
        $data = $this->validData([
            'first_name' => str_repeat('a', 255),
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertCreated();

        $response->assertJsonPath('data.first_name', str_repeat('a', 255));
    }

    public function test_first_nameが256文字以上の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData([
            'first_name' => str_repeat('a', 256),
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'first_name',
        ]);
    }

    public function test_last_nameは255文字まで登録できる(): void
    {
        $data = $this->validData([
            'last_name' => str_repeat('a', 255),
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertCreated();

        $response->assertJsonPath('data.last_name', str_repeat('a', 255));
    }

    public function test_last_nameが256文字以上の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData([
            'last_name' => str_repeat('a', 256),
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'last_name',
        ]);
    }

    public function test_detailは120文字まで登録できる(): void
    {
        $data = $this->validData([
            'detail' => str_repeat('a', 120),
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertCreated();

        $response->assertJsonPath('data.detail', str_repeat('a', 120));
    }

    public function test_detailが121文字以上の場合はバリデーションエラーになる(): void
    {
        $data = $this->validData([
            'detail' => str_repeat('a', 121),
        ]);

        $response = $this->postJson('/api/v1/contacts', $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'detail',
        ]);
    }

    public function test_必須項目が未入力の場合は指定された日本語メッセージが返る(): void
    {
        $response = $this->postJson('/api/v1/contacts', []);

        $response->assertStatus(422);

        $this->assertSame(
            '姓を入力してください',
            $response->json('errors.first_name.0')
        );

        $this->assertSame(
            '名を入力してください',
            $response->json('errors.last_name.0')
        );

        $this->assertSame(
            '性別を選択してください',
            $response->json('errors.gender.0')
        );

        $this->assertSame(
            'メールアドレスを入力してください',
            $response->json('errors.email.0')
        );

        $this->assertSame(
            '電話番号を入力してください',
            $response->json('errors.tel.0')
        );

        $this->assertSame(
            '住所を入力してください',
            $response->json('errors.address.0')
        );

        $this->assertSame(
            'お問い合わせの種類を選択してください',
            $response->json('errors.category_id.0')
        );

        $this->assertSame(
            'お問い合わせ内容を入力してください',
            $response->json('errors.detail.0')
        );
    }
}
