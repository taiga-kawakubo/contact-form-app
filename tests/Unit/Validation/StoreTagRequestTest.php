<?php

namespace Tests\Unit\Validation;

use App\Http\Requests\StoreTagRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;
use Tests\TestCase;

class StoreTagRequestTest extends TestCase
{
    use RefreshDatabase;

    /**
     * StoreTagRequestのルールでバリデーターを作成する
     */
    private function makeValidator(array $data): ValidationValidator
    {
        $request = new StoreTagRequest;

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    /**
     * 正常なタグ入力データを作成し、必要な項目だけ上書きできるようにする
     */
    private function validData(array $override = []): array
    {
        return array_merge([
            'name' => 'テストタグ１',
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

    public function test_タグ名は文字列でなければならない(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'name' => ['テストタグ１'],
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
            'name' => 'テストタグ１',
        ]);

        $validator = $this->makeValidator(
            $this->validData([
                'name' => 'テストタグ１',
            ])
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }
}
