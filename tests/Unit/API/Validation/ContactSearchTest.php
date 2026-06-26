<?php

namespace Tests\Unit\Requests\Api\V1;

use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ContactSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
    }

    private function validate(array $data)
    {
        $request = new IndexContactRequest();

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    public function test_有効な検索条件はバリデーションを通過する(): void
    {
        $category = Category::first();

        $validator = $this->validate([
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-06-26',
            'page' => 1,
            'per_page' => 10,
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_キーワードは文字列でなければならない(): void
    {
        $validator = $this->validate([
            'keyword' => ['山田'],
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('keyword', $validator->errors()->toArray());
    }

    public function test_キーワードは255文字以内でなければならない(): void
    {
        $validator = $this->validate([
            'keyword' => str_repeat('a', 256),
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('keyword', $validator->errors()->toArray());
    }

    public function test_性別は1_2_3のいずれかであればバリデーションを通過する(): void
    {
        foreach ([1, 2, 3] as $gender) {
            $validator = $this->validate([
                'gender' => $gender,
            ]);

            $this->assertFalse($validator->fails());
        }
    }

    public function test_性別の値が不正な場合はバリデーションエラーになる(): void
    {
        $validator = $this->validate([
            'gender' => 4,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());

        $this->assertSame(
            '性別の値が不正です',
            $validator->errors()->first('gender')
        );
    }

    public function test_カテゴリが存在する場合はバリデーションを通過する(): void
    {
        $category = Category::first();

        $validator = $this->validate([
            'category_id' => $category->id,
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_存在しないカテゴリを選択した場合はバリデーションエラーになる(): void
    {
        $validator = $this->validate([
            'category_id' => 999999,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());

        $this->assertSame(
            '選択されたカテゴリーが存在しません',
            $validator->errors()->first('category_id')
        );
    }

    public function test_日付がY_m_d形式であればバリデーションを通過する(): void
    {
        $validator = $this->validate([
            'date' => '2026-06-26',
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_日付形式が不正な場合はバリデーションエラーになる(): void
    {
        $validator = $this->validate([
            'date' => '2026/06/26',
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('date', $validator->errors()->toArray());
    }

    public function test_pageは1以上の整数であればバリデーションを通過する(): void
    {
        $validator = $this->validate([
            'page' => 1,
        ]);

        $this->assertFalse($validator->fails());
    }

    public function test_pageが1未満の場合はバリデーションエラーになる(): void
    {
        $validator = $this->validate([
            'page' => 0,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('page', $validator->errors()->toArray());
    }

    public function test_per_pageは1以上100以下の整数であればバリデーションを通過する(): void
    {
        foreach ([1, 10, 100] as $perPage) {
            $validator = $this->validate([
                'per_page' => $perPage,
            ]);

            $this->assertFalse($validator->fails());
        }
    }

    public function test_per_pageが1未満の場合はバリデーションエラーになる(): void
    {
        $validator = $this->validate([
            'per_page' => 0,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('per_page', $validator->errors()->toArray());
    }

    public function test_per_pageが100を超える場合はバリデーションエラーになる(): void
    {
        $validator = $this->validate([
            'per_page' => 101,
        ]);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('per_page', $validator->errors()->toArray());
    }
}