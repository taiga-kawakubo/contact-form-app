<?php

namespace Tests\Unit\Validation;

use App\Http\Requests\ContactExportRequest;
use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ContactExportRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
    }

    public function makeValidator(array $data)
    {
        $request = new ContactExportRequest;

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    private function validData(array $override = []): array
    {
        $category = Category::firstOrFail();

        return array_merge([
            'keyword' => '山田',
            'gender' => 1,
            'category_id' => $category->id,
            'date' => '2026-06-27',
        ], $override);
    }

    public function test_フィルタ未指定でもバリデーションを通過する(): void
    {
        $validator = $this->makeValidator([]);

        $this->assertFalse($validator->fails());
    }

    public function test_正しいフィルタ条件はバリデーションを通過する(): void
    {
        $validator = $this->makeValidator(
            $this->validData()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_キーワードが256文字の場合はバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'keyword' => str_repeat('a', 256),
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'keyword',
            $validator->errors()->toArray()
        );
    }

    public function test_genderが0の場合はバリデーションを通過する(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'gender' => 0,
            ])
        );

        $this->assertFalse($validator->fails());
    }

    public function test_性別は整数でなければならない(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'gender' => ['1'],
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'gender',
            $validator->errors()->toArray()
        );
    }

    public function test_不正な性別はバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'gender' => 999,
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'gender',
            $validator->errors()->toArray()
        );
    }

    public function test_存在するカテゴリidはバリデーションを通過する(): void
    {
        $category = Category::firstOrFail();

        $validator = $this->makeValidator(
            $this->validData([
                'category_id' => $category->id,
            ])
        );

        $this->assertFalse($validator->fails());
    }

    public function test_カテゴリidは整数でなければならない(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'category_id' => [1],
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'category_id',
            $validator->errors()->toArray()
        );
    }

    public function test_存在しないカテゴリidはバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'category_id' => 999999,
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'category_id',
            $validator->errors()->toArray()
        );
    }

    public function test_日付形式でない入力はバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'date' => 'abc',
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'date',
            $validator->errors()->toArray()
        );
    }
}
