<?php

namespace Tests\Feature;

use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 検証に必要なユーザーを作成する。
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(UserSeeder::class);
    }

    public function test_登録画面が表示できる(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_正しい入力内容でユーザー登録でき管理画面へ遷移する(): void
    {
        $response = $this->post(
            route('register.store'),
            [
                'name' => '山田太郎',
                'email' => 'new-user@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]
        );

        $response->assertRedirect(route('admin.index'));

        $this->assertDatabaseHas('users', [
            'name' => '山田太郎',
            'email' => 'new-user@example.com',
        ]);

        $this->assertAuthenticated();
    }

    public function test_登録時に必須項目が空の場合はバリデーションエラーになる(): void
    {
        $response = $this
            ->from(route('register'))
            ->post(
                route('register.store'),
                [
                    'name' => '',
                    'email' => '',
                    'password' => '',
                    'password_confirmation' => '',
                ]
            );

        $response->assertRedirect(route('register'));

        $response->assertSessionHasErrors([
            'name',
            'email',
            'password',
        ]);

        $this->assertGuest();
    }

    public function test_登録時に既に存在するメールアドレスは使用できない(): void
    {
        $response = $this
            ->from(route('register'))
            ->post(
                route('register.store'),
                [
                    'name' => '山田太郎',
                    'email' => 'test@example.com',
                    'password' => 'password',
                    'password_confirmation' => 'password',
                ]
            );

        $response->assertRedirect(route('register'));

        $response->assertSessionHasErrors([
            'email',
        ]);

        $this->assertGuest();
    }
}
