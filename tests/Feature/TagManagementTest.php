<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_タグの新規登録ができる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->post(
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
        $response->assertRedirect(
            route('admin.index')
        );
    }

    public function test_未認証ユーザーはタグを作成できない(): void
    {
        $response = $this->post(
            route('tags.store'),
            [
                'name' => 'テストタグ',
            ]
        );

        $response->assertRedirect(
            route('login')
        );
        $this->assertDatabaseMissing(
            'tags',
            [
                'name' => 'テストタグ',
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

    public function test_タグ名は文字列でなければならない(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(
            route('tags.store'),
            [
                'name' => ['テストタグ'],
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

    public function test_認証済みユーザーはタグ編集画面を表示できる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $response = $this->get(
            route('tags.edit', $tag)
        );

        $response->assertOk();
        $response->assertSee('value="テストタグ"', false);
    }

    public function test_未認証ユーザーはタグ編集画面にアクセスできない(): void
    {
        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $response = $this->get(
            route('tags.edit', $tag)
        );

        $response->assertRedirect(
            route('login')
        );
    }

    public function test_タグの更新ができる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $tag = Tag::create([
            'name' => 'テスト１',
        ]);
        $response = $this->put(
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
        $response->assertRedirect(
            route('admin.index')
        );
    }

    public function test_未認証ユーザーはタグを更新できない(): void
    {
        $tag = Tag::create([
            'name' => '更新前タグ',
        ]);

        $response = $this->put(
            route('tags.update', $tag),
            [
                'name' => '更新後タグ',
            ]
        );

        $response->assertRedirect(
            route('login')
        );

        $this->assertDatabaseHas(
            'tags',
            [
                'id' => $tag->id,
                'name' => '更新前タグ',
            ]
        );

        $this->assertDatabaseMissing(
            'tags',
            [
                'id' => $tag->id,
                'name' => '更新後タグ',
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

    public function test_すでに存在するタグ名で更新するとバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Tag::create([
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

    public function test_タグの削除ができる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $response = $this->delete(
            route('tags.delete', $tag)
        );

        $response->assertRedirect(
            route('admin.index')
        );

        $this->assertDatabaseMissing(
            'tags',
            [
                'id' => $tag->id,
            ]
        );
    }

    public function test_未認証ユーザーはタグを削除できない(): void
    {
        $tag = Tag::create([
            'name' => 'テストタグ',
        ]);

        $response = $this->delete(
            route('tags.delete', $tag)
        );

        $response->assertRedirect(
            route('login')
        );

        $this->assertDatabaseHas(
            'tags',
            [
                'id' => $tag->id,
                'name' => 'テストタグ',
            ]
        );
    }
}
