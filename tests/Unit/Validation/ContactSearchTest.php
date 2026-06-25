<?php

namespace Tests\Unit\Validation;

use App\Models\Category;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    public function test_キーワード検索ができる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
        ]);
        Contact::factory()->create([
            'first_name' => '佐藤',
            'last_name' => '花子',
            'email' => 'sato@example.com',
        ]);

        $response = $this->get(
            '/admin?keyword=山田'
        );
        $response->assertSee('山田');
        $response->assertDontSee('佐藤');
    }

    public function test_性別検索ができる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Contact::factory()->create([
            'first_name' => '男性ユーザー',
            'gender' => 1,
        ]);

        Contact::factory()->create([
            'first_name' => '女性ユーザー',
            'gender' => 2,
        ]);

        $response = $this->get(
            '/admin?gender=1'
        );
        $response->assertSee('男性ユーザー');
        $response->assertDontSee('女性ユーザー');
    }

    public function test_カテゴリー検索ができる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category1 = Category::find(1);
        $category2 = Category::find(2);
        Contact::factory()->create([
            'first_name' => '対象',
            'category_id' => $category1->id,
        ]);
        Contact::factory()->create([
            'first_name' => '対象外',
            'category_id' => $category2->id,
        ]);

        $response = $this->get(
            "/admin?category_id={$category1->id}"
        );

        $response->assertSee('対象');
        $response->assertDontSee('対象外');
    }

    public function test_日付検索ができる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Contact::factory()->create([
            'first_name' => '今日のデータ',
            'created_at' => '2026-06-24',
        ]);
        Contact::factory()->create([
            'first_name' => '昨日のデータ',
            'created_at' => '2026-06-23',
        ]);

        $response = $this->get(
            '/admin?date=2026-06-24'
        );
        $response->assertSee('今日のデータ');
        $response->assertDontSee('昨日のデータ');
    }

    public function test_複数の条件で検索ができる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $category = Category::find(1);
        Contact::factory()->create([
            'first_name' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
        ]);
        Contact::factory()->create([
            'first_name' => '山田',
            'gender' => 2,
            'category_id' => $category->id,
        ]);

        $response = $this->get(
            "/admin?keyword=山田&gender=1&category_id={$category->id}"
        );
        $response->assertSee('山田');
    }

    public function test_存在しない性別idはバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(
            '/admin?gender=999'
        );

        $response->assertSessionHasErrors([
            'gender',
        ]);
    }

    public function test_存在する性別idはバリデーションエラーにならない(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        foreach ([1, 2, 3] as $gender) {
            $response = $this->get(
                "/admin?gender={$gender}"
            );
        }

        $response->assertSessionHasNoErrors();
    }

    public function test_存在しないカテゴリー_idはバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(
            '/admin?category_id=999'
        );

        $response->assertSessionHasErrors([
            'category_id',
        ]);
    }

    public function test_存在するカテゴリー_idはバリデーションエラーにならない(): void
    {
        $user = User::factory()->create();
        $category = Category::find(1);
        $this->actingAs($user);

        $response = $this->get(
            "/admin?category_id={$category->id}"
        );

        $response->assertSessionHasNoErrors();
    }

    public function test_日付形式でない入力はバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(
            '/admin?date=abc'
        );

        $response->assertSessionHasErrors([
            'date',
        ]);
    }

    public function test_日付形式での入力はバリデーションエラーにならないこと(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(
            '/admin?date=2026-06-24'
        );

        $response->assertSessionHasNoErrors();
    }

    public function test_キーワード検索で文字数が超えた場合はバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $keyword = str_repeat('a', 256);

        $response = $this->get(
            "/admin?keyword={$keyword}"
        );

        $response->assertSessionHasErrors([
            'keyword',

        ]);
    }

    public function test_キーワード検索で文字数が超えない場合はバリデーションエラーにならない(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $keyword = str_repeat('a', 255);

        $response = $this->get(
            "/admin?keyword={$keyword}"
        );

        $response->assertSessionHasNoErrors();
    }

    public function test_キーワード検索で検索欄が空の場合もバリデーションエラーにならない(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(
            '/admin?keyword='
        );

        $response->assertSessionHasNoErrors();
    }
}
