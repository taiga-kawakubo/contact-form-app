<?php

namespace Tests\Unit\Validation;

use App\Http\Requests\TagStoreRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TagStoreRequestTest extends TestCase
{
    use RefreshDatabase;

    private function makeValidator(array $data)
    {
        $request = new TagStoreRequest;

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    private function validData(array $override = []): array
    {
        return array_merge([
            'name' => 'Laravel',
        ], $override);
    }

    public function test_タグ名が入力されていればバリデーションを通過する(): void
    {
        $validator = $this->makeValidator(
            $this->validData()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_タグ名が空の場合はバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'name' => '',
            ])
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }

    public function test_タグ名は50文字まで入力できる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'name' => str_repeat('a', 50),
            ])
        );

        $this->assertFalse($validator->fails());
    }

    public function test_タグ名が50文字を超えるとバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'name' => str_repeat('a', 51),
            ])
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }

    public function test_すでに存在するタグ名では新規登録できない(): void
    {
        Tag::create([
            'name' => 'Laravel',
        ]);

        $validator = $this->makeValidator(
            $this->validData([
                'name' => 'Laravel',
            ])
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }
}
