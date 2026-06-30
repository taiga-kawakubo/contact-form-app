<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(UserSeeder::class);
    }

    public function test_ログイン画面が表示できる(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_正しい認証情報でログインできる(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();

        $response = $this->post(
            route('login.store'),
            [
                'email' => 'test@example.com',
                'password' => 'password',
            ]
        );

        $response->assertRedirect(route('admin.index'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_存在しないメールアドレスではログインに失敗しエラーメッセージが返る(): void
    {
        $response = $this
            ->from(route('login'))
            ->post(
                route('login.store'),
                [
                    'email' => 'notfound@example.com',
                    'password' => 'password',
                ]
            );

        $response->assertRedirect(
            route('login')
        );

        $response->assertSessionHasErrors([
            'email' => __('auth.failed'),
        ]);

        $this->assertGuest();
    }

    public function test_間違ったパスワードではログインに失敗しエラーメッセージが返る(): void
    {
        $response = $this
            ->from(route('login'))
            ->post(
                route('login.store'),
                [
                    'email' => 'test@example.com',
                    'password' => 'notfoundpassword',
                ]
            );

        $response->assertRedirect(
            route('login')
        );

        $response->assertSessionHasErrors([
            'email' => __('auth.failed'),
        ]);

        $this->assertGuest();
    }

    public function test_メールアドレスが空の場合バリデーションエラー(): void
    {
        $response = $this
            ->from(route('login'))
            ->post(
                route('login.store'),
                [
                    'email' => '',
                    'password' => 'password',
                ]
            );

        $response->assertRedirect(
            route('login')
        );

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
        $this->assertGuest();
    }

    public function test_メールアドレスがメール形式でない場合ログインに失敗する(): void
    {
        $response = $this
            ->from(route('login'))
            ->post(
                route('login.store'),
                [
                    'email' => 'abc',
                    'password' => 'password',
                ]
            );

        $response->assertRedirect(route('login'));

        $response->assertSessionHasErrors([
            'email' => __('auth.failed'),
        ]);
        $this->assertGuest();
    }

    public function test_パスワードが空の場合バリデーションエラー(): void
    {
        $response = $this
            ->from(route('login'))
            ->post(
                route('login.store'),
                [
                    'email' => 'test@example.com',
                    'password' => '',
                ]
            );

        $response->assertRedirect(
            route('login')
        );

        $response->assertSessionHasErrors([
            'password',
        ]);
        $this->assertGuest();
    }

    public function test_ログアウトできる(): void
    {
        $user = User::where('email', 'test@example.com')->firstOrFail();
        $this->actingAs($user);
        $this->assertAuthenticatedAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
