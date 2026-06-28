<?php

namespace Tests\Unit;

use App\Http\Requests\ExportContactRequest;
use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ExportContactRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
    }

    public function makeValidator(array $data)
    {
        $request = new ExportContactRequest;

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

    public function test_genderが0の場合はバリデーションを通過する(): void
    {
        $validatator = $this->makeValidator(
            $this->validData([
                'gender' => 0,
            ])
        );

        $this->assertFalse($validatator->fails());
    }

    public function test_不正な性別はバリデーションエラーになる(): void
    {
        $validatator = $this->makeValidator(
            $this->validData([
                'gender' => 999,
            ])
        );

        $this->assertTrue($validatator->fails());
        $this->assertArrayHasKey(
            'gender',
            $validatator->errors()->toArray()
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
}
