<?php

namespace Tests\Feature\Api\V1;

use App\Models\Contact;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactDestroyApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
    }

    public function test_お問い合わせを削除できる(): void
    {
        $contact = Contact::factory()->create();

        $response = $this->deleteJson('/api/v1/contacts/'.$contact->id);

        $response->assertNoContent();
        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_削除時に中間テーブルの紐づきも削除される(): void
    {
        $tag = Tag::create(['name' => 'テストタグ']);
        $contact = Contact::factory()->create();
        $contact->tags()->sync([$tag->id]);

        $this->assertDatabaseHas('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);

        $response = $this->deleteJson('/api/v1/contacts/'.$contact->id);

        $response->assertNoContent();

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);

        $this->assertDatabaseMissing('contact_tag', [
            'contact_id' => $contact->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_存在しないお問い合わせ_i_dは削除できない(): void
    {
        $response = $this->deleteJson('/api/v1/contacts/99999999');

        $response->assertNotFound();
    }
}
