<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactUpdateApiTest extends TestCase
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

        $tag = Tag::firstOrCreate(['name' => 'テストタグ']);

        return array_merge([
            'first_name' => '更新後姓',
            'last_name' => '更新後名',
            'gender' => 1,
            'email' => 'updated@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => '更新後ビル',
            'category_id' => $category->id,
            'detail' => '更新後のお問い合わせ内容です',
            'tag_ids' => [$tag->id],
        ], $override);
    }

    public function test_お問い合わせを更新できる(): void
    {
        $contact = Contact::factory()->create([
            'first_name' => '更新前姓',
            'last_name' => '更新前名',
            'email' => 'before@example.com',
        ]);
        $data = $this->validData([
            'first_name' => '更新後姓',
            'last_name' => '更新後名',
            'email' => 'after@example.com',
        ]);

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertOk();

        $response->assertJsonPath('data.id', $contact->id);
        $response->assertJsonPath('data.first_name', '更新後姓');
        $response->assertJsonPath('data.last_name', '更新後名');
        $response->assertJsonPath('data.email', 'after@example.com');

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => '更新後姓',
            'last_name' => '更新後名',
            'email' => 'after@example.com',
        ]);

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
            'first_name' => '更新前姓',
            'email' => 'before@example.com',
        ]);
    }

    public function test_更新成功時にレスポンス構造が正しい(): void
    {
        $contact = Contact::factory()->create([
            'first_name' => '更新前姓',
            'last_name' => '更新前名',
            'email' => 'updated@example.com',
        ]);
        $data = $this->validData([
            'first_name' => '更新後姓',
            'last_name' => '更新後名',
            'email' => 'after@example.com',
        ]);

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertOk();

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

    public function test_更新成功時にカテゴリ情報がレスポンスに含まれる(): void
    {
        $oldCategory = Category::firstOrFail();

        $newCategory = Category::where('id', '!=', $oldCategory->id)->firstOrFail();

        $contact = Contact::factory()->create([
            'category_id' => $oldCategory->id,
        ]);

        $data = $this->validData([
            'category_id' => $newCategory->id,
        ]);

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertOk();

        $response->assertJsonPath('data.category.id', $newCategory->id);
        $response->assertJsonPath('data.category.content', $newCategory->content);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'category_id' => $newCategory->id,
        ]);
    }

    public function test_tag_idsを指定してタグを更新すると中間テーブルに保存される(): void
    {
        $oldTag = Tag::create(['name' => '更新前タグ']);
        $newTag = Tag::create(['name' => '更新後タグ']);

        $contact = Contact::factory()->create();
        $contact->tags()->sync([$oldTag->id]);

        $data = $this->validData(['tag_ids' => [$newTag->id]]);

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertOk();

        $response->assertJsonCount(1, 'data.tags');

        $response->assertJsonPath('data.tags.0.id', $newTag->id);
        $response->assertJsonPath('data.tags.0.name', $newTag->name);
        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $newTag->id,
        ]);

        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $oldTag->id,
        ]);
    }

    public function test_tag_idsが空配列の場合はタグ紐づけが解除される(): void
    {
        $oldTag = Tag::create(['name' => '更新前タグ']);
        $contact = Contact::factory()->create();
        $contact->tags()->sync([$oldTag->id]);

        $data = $this->validData(['tag_ids' => []]);

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertOk();
        $response->assertJsonCount(0, 'data.tags');
        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $oldTag->id,
        ]);
    }

    public function test_buildingは空でも更新できる(): void
    {
        $contact = Contact::factory()->create();
        $data = $this->validData(['building' => null]);

        $response = $this->putJson('/api/v1/contacts/'.$contact->id, $data);

        $response->assertOk();
        $response->assertJsonPath('data.building', null);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'building' => null,
        ]);
    }

    public function test_存在しないお問い合わせidは更新できない(): void
    {
        $data = $this->validData();

        $response = $this->putJson('/api/v1/contacts/999999', $data);

        $response->assertNotFound();

        $response->assertJson([
            'error' => 'お問い合わせが見つかりませんでした。',
        ]);
    }

    public function test_first_nameが空の場合はバリデーションエラーになる(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'first_name' => '',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'first_name',
        ]);
    }

    public function test_last_nameが空の場合はバリデーションエラーになる(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'last_name' => '',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'last_name',
        ]);
    }

    public function test_genderが空の場合はバリデーションエラーになる(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'gender' => '',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'gender',
        ]);
    }

    public function test_emailが空の場合はバリデーションエラーになる(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'email' => '',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'email',
        ]);
    }

    public function test_telが空の場合はバリデーションエラーになる(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'tel' => '',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'tel',
        ]);
    }

    public function test_addressが空の場合はバリデーションエラーになる(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'address' => '',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'address',
        ]);
    }

    public function test_category_idが空の場合はバリデーションエラーになる(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'category_id' => '',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'category_id',
        ]);
    }

    public function test_detailが空の場合はバリデーションエラーになる(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'detail' => '',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'detail',
        ]);
    }

    public function test_存在しないgenderはバリデーションエラーとなりメッセージが返る(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'gender' => 999,
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

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
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'category_id' => 999999,
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

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
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'tag_ids' => [
                999999,
            ],
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

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
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'tag_ids' => 'タグ',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'tag_ids',
        ]);
    }

    public function test_email形式でない場合はバリデーションエラーになる(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'email' => 'not-email',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'email',
        ]);
    }

    public function test_telが数字以外の場合はバリデーションエラーとなりメッセージが返る(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'tel' => '090-aaaa-bbbb',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'tel',
        ]);

        $this->assertSame(
            '電話番号はハイフンなしの10〜11桁で入力してください',
            $response->json('errors.tel.0')
        );
    }

    public function test_first_nameは255文字まで更新できる(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'first_name' => str_repeat('a', 255),
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertOk();

        $response->assertJsonPath('data.first_name', str_repeat('a', 255));

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => str_repeat('a', 255),
        ]);
    }

    public function test_first_nameが256文字以上の場合はバリデーションエラーになる(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'first_name' => str_repeat('a', 256),
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'first_name',
        ]);
    }

    public function test_last_nameは255文字まで更新できる(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'last_name' => str_repeat('a', 255),
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertOk();

        $response->assertJsonPath('data.last_name', str_repeat('a', 255));

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'last_name' => str_repeat('a', 255),
        ]);
    }

    public function test_last_nameが256文字以上の場合はバリデーションエラーになる(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'last_name' => str_repeat('a', 256),
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'last_name',
        ]);
    }

    public function test_telが9桁の場合はバリデーションエラーになる(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'tel' => '123456789',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'tel',
        ]);
    }

    public function test_telが12桁の場合はバリデーションエラーになる(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'tel' => '123456789012',
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertStatus(422);

        $response->assertJsonValidationErrors([
            'tel',
        ]);
    }

    public function test_detailは120文字まで更新できる(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'detail' => str_repeat('あ', 120),
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

        $response->assertOk();

        $response->assertJsonPath('data.detail', str_repeat('あ', 120));

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'detail' => str_repeat('あ', 120),
        ]);
    }

    public function test_detailが121文字以上の場合はバリデーションエラーになる(): void
    {
        $contact = Contact::factory()->create();

        $data = $this->validData([
            'detail' => str_repeat('あ', 121),
        ]);

        $response = $this->putJson("/api/v1/contacts/{$contact->id}", $data);

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
