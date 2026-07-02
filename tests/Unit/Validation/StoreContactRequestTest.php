<?php

namespace Tests\Unit\Validation;

use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
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

    /**
     * StoreContactRequestのルールでバリデーターを作成する
     */
    private function makeValidator(array $data): ValidationValidator
    {
        $request = new StoreContactRequest;

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    /**
     * 正常なお問い合わせ入力データを作成し、必要な項目だけ上書きできるようにする
     */
    private function validData(array $override = []): array
    {
        $category = Category::firstOrFail();

        $tag1 = Tag::firstOrCreate(['name' => 'テスト1']);

        $tag2 = Tag::firstOrCreate(['name' => 'テスト2']);

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

    public function test_電話番号は10桁または11桁ならバリデーションを通過する(): void
    {
        foreach (['0312345678', '09012345678'] as $tel) {
            $validator = $this->makeValidator(
                $this->validData([
                    'tel' => $tel,
                ])
            );

            $this->assertFalse($validator->fails());
        }
    }

    public function test_建物名が空でもバリデーションを通過する(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'building' => null,
            ])
        );

        $this->assertFalse($validator->fails());
    }

    public function test_タグ入力が配列であればバリデーションを通過する(): void
    {
        $tag1 = Tag::create([
            'name' => 'タグ1',
        ]);

        $tag2 = Tag::create([
            'name' => 'タグ2',
        ]);

        $validator = $this->makeValidator(
            $this->validData([
                'tag_ids' => [
                    $tag1->id,
                    $tag2->id,
                ],
            ])
        );

        $this->assertFalse($validator->fails());
    }

    public function test_タグ未選択でもバリデーションを通過する(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'tag_ids' => [],
            ])
        );

        $this->assertFalse($validator->fails());
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

    public function test_メールアドレス形式でない場合はバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'email' => 'invalid-email',
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'email',
            $validator->errors()->toArray()
        );
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

    public function test_電話番号は9桁または12桁の場合バリデーションエラーになる(): void
    {
        foreach (['123456789', '123456789012'] as $tel) {
            $validator = $this->makeValidator(
                $this->validData([
                    'tel' => $tel,
                ])
            );

            $this->assertTrue($validator->fails());
            $this->assertArrayHasKey(
                'tel',
                $validator->errors()->toArray()
            );
        }
    }

    public function test_存在しないカテゴリーidはバリデーションエラーになる(): void
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

    public function test_お問い合わせ内容が121文字の場合はバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'detail' => str_repeat('あ', 121),
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'detail',
            $validator->errors()->toArray()
        );
    }

    public function test_タグ入力が配列でない場合はバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'tag_ids' => '1',
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'tag_ids',
            $validator->errors()->toArray()
        );
    }

    public function test_存在しないタグidはバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'tag_ids' => [999999],
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey(
            'tag_ids.0',
            $validator->errors()->toArray()
        );
    }
}
