<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactAdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 検証に必要なカテゴリーを作成する
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategorySeeder::class);
    }

    public function test_認証済みユーザーは管理画面にアクセスできる(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(
            route('admin.index')
        );

        $response->assertOk();

        $response->assertViewIs('admin.index');
    }

    public function test_未認証ユーザーは管理画面にアクセスできない(): void
    {
        $response = $this->get(
            route('admin.index')
        );

        $response->assertRedirect(
            route('login')
        );
    }

    public function test_管理画面でお問い合わせ一覧が表示される(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $category = Category::firstOrFail();

        Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
        ]);

        $response = $this->get(
            route('admin.index')
        );

        $response->assertOk();

        $response->assertViewIs('admin.index');

        $response->assertSeeText('山田');
        $response->assertSeeText('太郎');
        $response->assertSeeText('yamada@example.com');
        $response->assertSeeText($category->content);
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
            route('admin.index', [
                'keyword' => '山田',
            ])
        );
        $response->assertOk();
        $response->assertSeeText('山田');
        $response->assertDontSeeText('佐藤');
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
            route('admin.index', [
                'gender' => 1,
            ])
        );
        $response->assertOk();
        $response->assertSeeText('男性ユーザー');
        $response->assertDontSeeText('女性ユーザー');
    }

    public function test_カテゴリー検索ができる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

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

        $response = $this->get(
            route('admin.index', [
                'category_id' => $category1->id,
            ])
        );
        $response->assertOk();
        $response->assertSeeText('対象');
        $response->assertDontSeeText('対象外');
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
            route('admin.index', [
                'date' => '2026-06-24',
            ])
        );
        $response->assertOk();
        $response->assertSeeText('今日のデータ');
        $response->assertDontSeeText('昨日のデータ');
    }

    public function test_複数の条件で検索ができる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category1 = Category::findOrFail(1);
        $category2 = Category::findOrFail(2);

        // すべての条件に一致するデータ
        Contact::factory()->create([
            'first_name' => '山田男性',
            'last_name' => '対象',
            'gender' => 1,
            'category_id' => $category1->id,
            'email' => 'male@example.com',
        ]);

        // gender が一致しないデータ
        Contact::factory()->create([
            'first_name' => '山田女性',
            'last_name' => '対象外',
            'gender' => 2,
            'category_id' => $category1->id,
            'email' => 'female@example.com',
        ]);

        // category_id が一致しないデータ
        Contact::factory()->create([
            'first_name' => '山田別カテゴリ',
            'last_name' => '対象外',
            'gender' => 1,
            'category_id' => $category2->id,
            'email' => 'other-category@example.com',
        ]);

        // keyword が一致しないデータ
        Contact::factory()->create([
            'first_name' => '佐藤男性',
            'last_name' => '対象外',
            'gender' => 1,
            'category_id' => $category1->id,
            'email' => 'sato@example.com',
        ]);

        $response = $this->get(
            route('admin.index', [
                'keyword' => '山田',
                'gender' => 1,
                'category_id' => $category1->id,
            ])
        );

        $response->assertOk();

        $response->assertSeeText('山田男性');

        $response->assertDontSeeText('山田女性');
        $response->assertDontSeeText('山田別カテゴリ');
        $response->assertDontSeeText('佐藤男性');
    }

    public function test_管理画面のお問い合わせ一覧は7件ごとにページネーションされる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::firstOrFail();

        Contact::factory()
            ->count(8)
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

        $response = $this->get(route('admin.index'));

        $response->assertOk();

        $contacts = $response->viewData('contacts');

        $this->assertCount(7, $contacts);
        $this->assertSame(8, $contacts->total());
        $this->assertSame(2, $contacts->lastPage());
    }

    public function test_存在しない性別idはバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(
            route('admin.index', [
                'gender' => 999,
            ])
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
                route('admin.index', [
                    'gender' => $gender,
                ])
            );

            $response->assertSessionHasNoErrors();
        }
    }

    public function test_存在しないカテゴリーidはバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(
            route('admin.index', [
                'category_id' => 999,
            ])
        );

        $response->assertSessionHasErrors([
            'category_id',
        ]);
    }

    public function test_存在するカテゴリー_idはバリデーションエラーにならない(): void
    {
        $user = User::factory()->create();
        $category = Category::findOrFail(1);
        $this->actingAs($user);

        $response = $this->get(
            route('admin.index', [
                'category_id' => $category->id,
            ])
        );

        $response->assertSessionHasNoErrors();
    }

    public function test_日付形式でない入力はバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(
            route('admin.index', [
                'date' => 'abc',
            ])
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
            route('admin.index', [
                'date' => '2026-06-24',
            ])
        );

        $response->assertSessionHasNoErrors();
    }

    public function test_キーワード検索で256文字はバリデーションエラーになる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $keyword = str_repeat('a', 256);

        $response = $this->get(
            route('admin.index', [
                'keyword' => $keyword,
            ])
        );

        $response->assertSessionHasErrors([
            'keyword',
        ]);
    }

    public function test_キーワード検索で255文字はバリデーションエラーにならない(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $keyword = str_repeat('a', 255);

        $response = $this->get(
            route('admin.index', [
                'keyword' => $keyword,
            ])
        );

        $response->assertSessionHasNoErrors();
    }

    public function test_キーワード検索で検索欄が空の場合もバリデーションエラーにならない(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(
            route('admin.index', [
                'keyword' => '',
            ])
        );

        $response->assertSessionHasNoErrors();
    }

    public function test_お問い合わせ詳細ページでカテゴリとタグが表示できる(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::firstOrFail();

        $tag = Tag::create([
            'name' => '重要タグ',
        ]);

        $contact = Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
        ]);

        $contact->tags()->attach($tag->id);

        $response = $this->get(
            route('admin.show', $contact)
        );

        $response->assertOk();

        $response->assertViewIs('admin.show');

        $response->assertSeeText('山田');
        $response->assertSeeText('太郎');
        $response->assertSeeText('yamada@example.com');
        $response->assertSeeText('09012345678');
        $response->assertSeeText('東京都渋谷区');
        $response->assertSeeText('テストビル');
        $response->assertSeeText('お問い合わせ内容');

        $response->assertSeeText($category->content);
        $response->assertSeeText('重要タグ');
    }

    public function test_未認証ユーザーはお問い合わせ詳細画面にアクセスできない(): void
    {
        $category = Category::firstOrFail();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->get(
            route('admin.show', $contact)
        );

        $response->assertRedirect(route('login'));
    }

    public function test_指定したお問い合わせが削除され管理ページに遷移する(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = Category::firstOrFail();

        $contact = Contact::factory()->create([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
        ]);

        $response = $this->delete(
            route('admin.delete', $contact)
        );

        $response->assertRedirect(
            route('admin.index')
        );

        $this->assertDatabaseMissing(
            'contacts',
            [
                'id' => $contact->id,
            ]
        );
    }

    public function test_未認証ユーザーはお問い合わせを削除できない(): void
    {
        $category = Category::firstOrFail();

        $contact = Contact::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->delete(
            route('admin.delete', $contact)
        );

        $response->assertRedirect(route('login'));

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
        ]);
    }
}
