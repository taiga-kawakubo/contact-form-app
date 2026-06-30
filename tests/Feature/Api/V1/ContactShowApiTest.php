<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactShowApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
    }

    public function test_お問い合わせ詳細を取得できる(): void
    {
        $contact = Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
        ]);

        $response = $this->getJson('/api/v1/contacts/'.$contact->id);

        $response->assertOk();

        $response->assertJsonPath('data.id', $contact->id);
        $response->assertJsonPath('data.first_name', '山田');
        $response->assertJsonPath('data.last_name', '太郎');
        $response->assertJsonPath('data.email', 'yamada@example.com');
    }

    public function test_詳細取得成功時に_jso_n構造が正しい(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->getJson('/api/v1/contacts/'.$contact->id);

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

    public function test_詳細取得時にカテゴリ情報が含まれる(): void
    {
        $category = Category::firstOrFail();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/contacts/'.$contact->id);

        $response->assertOk();

        $response->assertJsonPath('data.category.id', $category->id);
        $response->assertJsonPath('data.category.content', $category->content);
    }

    public function test_詳細取得時にタグ情報が含まれる(): void
    {
        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $contact = Contact::factory()->create();

        $contact->tags()->sync([
            $tag->id,
        ]);

        $response = $this->getJson('/api/v1/contacts/'.$contact->id);

        $response->assertOk();

        $response->assertJsonCount(1, 'data.tags');

        $response->assertJsonPath('data.tags.0.id', $tag->id);
        $response->assertJsonPath('data.tags.0.name', $tag->name);
    }

    public function test_存在しないお問い合わせ_i_dは404になる(): void
    {
        $response = $this->getJson('/api/v1/contacts/99999999');

        $response->assertNotFound();

        $response->assertJsonStructure([
            'message',
        ]);
    }
}
