<?php

namespace Tests\Unit\Validation;

use App\Http\Requests\ContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ContactStoreRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
    }

    private function makeValidator(array $data)
    {
        $request = new ContactRequest;

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    private function validData(array $override = []): array
    {
        $category = Category::firstOrFail();

        $tag1 = Tag::firstOrCreate([
            'name' => 'テスト1',
        ]);

        $tag2 = Tag::firstOrCreate([
            'name' => 'テスト2',
        ]);

        return array_merge([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容',
            'tag_ids' => [
                $tag1->id,
                $tag2->id,
            ],
        ], $override);
    }

    public function test_全ての必須項目とタグ入力があればバリデーションを通過する(): void
    {
        $validator = $this->makeValidator(
            $this->validData()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_タグ入力が配列であればバリデーションを通過する(): void
    {
        $tagIds = Tag::query()
            ->pluck('id')
            ->toArray();

        $validator = $this->makeValidator(
            $this->validData([
                'tag_ids' => $tagIds,
            ])
        );

        $this->assertFalse($validator->fails());
    }

    public function test_不正な電話番号形式はバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'tel' => 'abcde',
            ])
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'tel',
            $validator->errors()->toArray()
        );
    }

    public function test_電話番号にハイフンが含まれる場合はバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'tel' => '090-1234-5678',
            ])
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'tel',
            $validator->errors()->toArray()
        );
    }

    public function test_必須項目が空の場合はバリデーションエラーになる(): void
    {
        $requiredFields = [
            'first_name',
            'last_name',
            'gender',
            'email',
            'tel',
            'address',
            'category_id',
            'detail',
        ];

        foreach ($requiredFields as $field) {
            $validator = $this->makeValidator(
                $this->validData([
                    $field => '',
                ])
            );

            $this->assertTrue($validator->fails());

            $this->assertArrayHasKey(
                $field,
                $validator->errors()->toArray()
            );
        }
    }
}
