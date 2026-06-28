<?php

namespace Tests\Unit\Validation;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function test_タグの新規登録ができる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->post(
            route('tags.store'),
            [
                'name' => 'テスト１',
            ]
        );

        $this->assertDatabaseHas(
            'tags',
            [
                'name' => 'テスト１',
            ]
        );
    }

    public function test_タグ名が空で新規登録を行うとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->post(
            route('tags.store'),
            [
                'name' => '',
            ]
        );
        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    public function test_新規登録のときタグ名は50文字まで入力できる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->post(
            route('tags.store'),
            [
                'name' => str_repeat('a', 50),
            ]
        );
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas(
            'tags',
            [
                'name' => str_repeat('a', 50),
            ]
        );
    }

    public function test_新規登録のときタグ名が50文字を超えるとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->post(
            route('tags.store'),
            [
                'name' => str_repeat('a', 51),
            ]
        );
        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    public function test_すでに存在するタグ名で新規登録するとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Tag::create([
            'name' => 'テスト1',
        ]);
        $response = $this->post(
            route('tags.store'),
            [
                'name' => 'テスト1',
            ]
        );
        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    public function test_タグの更新ができる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $tag = Tag::create([
            'name' => 'テスト１',
        ]);
        $this->put(
            route('tags.update', $tag),
            [
                'name' => 'テスト２',
            ]
        );
        $this->assertDatabaseHas(
            'tags',
            [
                'id' => $tag->id,
                'name' => 'テスト２',
            ]
        );
    }

    public function test_タグ名を空で更新を行うとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $tag = Tag::create([
            'name' => 'テスト１',
        ]);

        $response = $this->put(
            route('tags.update', $tag),
            [
                'name' => '',
            ]
        );

        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    public function test_更新のときタグ名は50文字まで入力できる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $tag = Tag::create([
            'name' => 'Laravel',
        ]);

        $response = $this->put(
            route('tags.update', $tag),
            [
                'name' => str_repeat('a', 50),
            ]
        );

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas(
            'tags',
            [
                'id' => $tag->id,
                'name' => str_repeat('a', 50),
            ]
        );
    }

    public function test_更新のときタグ名が50文字を超えるとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $tag = Tag::create([
            'name' => 'テスト１',
        ]);

        $response = $this->put(
            route('tags.update', $tag),
            [
                'name' => str_repeat('a', 51),
            ]
        );

        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    public function test_すでに存在するタグ名で更新するとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $tag1 = Tag::create([
            'name' => 'テスト１',
        ]);
        $tag2 = Tag::create([
            'name' => 'テスト２',
        ]);

        $response = $this->put(
            route('tags.update', $tag2),
            [
                'name' => 'テスト１',
            ]
        );

        $response->assertSessionHasErrors([
            'name',
        ]);
    }

    public function test_更新のとき自身の名前維持は可能である(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $tag = Tag::create([
            'name' => 'Laravel',
        ]);

        $response = $this->put(
            route('tags.update', $tag),
            [
                'name' => 'Laravel',
            ]
        );

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas(
            'tags',
            [
                'id' => $tag->id,
                'name' => 'Laravel',
            ]
        );
    }
}
