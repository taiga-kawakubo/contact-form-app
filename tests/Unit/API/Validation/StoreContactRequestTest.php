<?php

namespace Tests\Unit\Requests\Api\V1;

use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Models\Category;
use App\Models\Tag;
use Database\Seeders\CategorySeeder;
use Database\Seeders\TagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreContactRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);
        $this->seed(TagSeeder::class);
    }

    private function makeValidator(array $data)
    {
        $request = new StoreContactRequest;

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    private function validData(array $override = []): array
    {
        $category = Category::firstOrFail();
        $tag = Tag::firstOrFail();

        return array_merge([
            'first_name' => '山田',
            'last_name' => '太郎',
            'gender' => 1,
            'email' => 'yamada@example.com',
            'tel' => '09012345678',
            'address' => '東京都渋谷区',
            'building' => 'テストビル',
            'category_id' => $category->id,
            'detail' => 'お問い合わせ内容です',
            'tag_ids' => [$tag->id],
        ], $override);
    }

    public function test_有効な入力値はバリデーションを通過する(): void
    {
        $validator = $this->makeValidator(
            $this->validData()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_姓は必須である(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'first_name' => '',
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('first_name', $validator->errors()->toArray());

        $this->assertSame(
            '姓を入力してください',
            $validator->errors()->first('first_name')
        );
    }

    public function test_姓は255文字以内でなければならない(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'first_name' => str_repeat('a', 256),
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('first_name', $validator->errors()->toArray());
    }

    public function test_名は必須である(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'last_name' => '',
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('last_name', $validator->errors()->toArray());
        $this->assertSame(
            '名を入力してください',
            $validator->errors()->first('last_name')
        );
    }

    public function test_名は255文字以内でなければならない(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'last_name' => str_repeat('a', 256),
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('last_name', $validator->errors()->toArray());
    }

    public function test_性別は必須である(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'gender' => '',
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());

        $this->assertSame(
            '性別を選択してください',
            $validator->errors()->first('gender')
        );
    }

    public function test_性別は1_2_3のいずれかであればバリデーションを通過する(): void
    {
        foreach ([1, 2, 3] as $gender) {
            $validator = $this->makeValidator(
                $this->validData([
                    'gender' => $gender,
                ])
            );

            $this->assertFalse($validator->fails());
        }
    }

    public function test_性別の値が不正な場合はバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'gender' => 4,
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('gender', $validator->errors()->toArray());
        $this->assertSame(
            '性別の値が不正です',
            $validator->errors()->first('gender')
        );
    }

    public function test_メールアドレスは必須である(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'email' => '',
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertSame(
            'メールアドレスを入力してください',
            $validator->errors()->first('email')
        );
    }

    public function test_メールアドレスはメール形式でなければならない(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'email' => 'invalid-email',
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
        $this->assertSame(
            'メールアドレスはメール形式で入力してください',
            $validator->errors()->first('email')
        );
    }

    public function test_メールアドレスは255文字以内でなければならない(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'email' => str_repeat('a', 256).'@example.com',
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('email', $validator->errors()->toArray());
    }

    public function test_電話番号は必須である(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'tel' => '',
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tel', $validator->errors()->toArray());
        $this->assertSame(
            '電話番号を入力してください',
            $validator->errors()->first('tel')
        );
    }

    public function test_電話番号がハイフンを含む場合はバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'tel' => '090-1234-5678',
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tel', $validator->errors()->toArray());

        $this->assertSame(
            '電話番号はハイフンなしの10〜11桁で入力してください',
            $validator->errors()->first('tel')
        );
    }

    public function test_電話番号が9桁の場合はバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'tel' => '123456789',
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tel', $validator->errors()->toArray());
    }

    public function test_電話番号が12桁の場合はバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'tel' => '123456789012',
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tel', $validator->errors()->toArray());
    }

    public function test_住所は必須である(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'address' => '',
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('address', $validator->errors()->toArray());

        $this->assertSame(
            '住所を入力してください',
            $validator->errors()->first('address')
        );
    }

    public function test_住所は255文字以内でなければならない(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'address' => str_repeat('あ', 256),
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('address', $validator->errors()->toArray());
    }

    public function test_建物名は任意である(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'building' => null,
            ])
        );

        $this->assertFalse($validator->fails());
    }

    public function test_建物名は255文字以内でなければならない(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'building' => str_repeat('あ', 256),
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('building', $validator->errors()->toArray());
    }

    public function test_お問い合わせ種類は必須である(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'category_id' => '',
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());

        $this->assertSame(
            'お問い合わせの種類を選択してください',
            $validator->errors()->first('category_id')
        );
    }

    public function test_存在するカテゴリーidはバリデーションを通過する(): void
    {
        $category = Category::firstOrFail();

        $validator = $this->makeValidator(
            $this->validData([
                'category_id' => $category->id,
            ])
        );

        $this->assertFalse($validator->fails());
    }

    public function test_存在しないカテゴリーを選択した場合はバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'category_id' => 999999,
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('category_id', $validator->errors()->toArray());

        $this->assertSame(
            '選択されたカテゴリーが存在しません',
            $validator->errors()->first('category_id')
        );
    }

    public function test_お問い合わせ内容は必須である(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'detail' => '',
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('detail', $validator->errors()->toArray());

        $this->assertSame(
            'お問い合わせ内容を入力してください',
            $validator->errors()->first('detail')
        );
    }

    public function test_お問い合わせ内容は120文字以内でなければならない(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'detail' => str_repeat('あ', 121),
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('detail', $validator->errors()->toArray());

        $this->assertSame(
            'お問い合わせ内容は120文字以内で入力してください',
            $validator->errors()->first('detail')
        );
    }

    public function test_タグidは配列であればバリデーションを通過する(): void
    {
        $tagIds = Tag::query()
            ->take(2)
            ->pluck('id')
            ->toArray();

        $validator = $this->makeValidator(
            $this->validData([
                'tag_ids' => $tagIds,
            ])
        );

        $this->assertFalse($validator->fails());
    }

    public function test_タグidは任意である(): void
    {
        $data = $this->validData();

        unset($data['tag_ids']);

        $validator = $this->makeValidator($data);

        $this->assertFalse($validator->fails());
    }

    public function test_タグidが配列でない場合はバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'tag_ids' => 1,
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tag_ids', $validator->errors()->toArray());
    }

    public function test_存在しないタグidを指定した場合はバリデーションエラーになる(): void
    {
        $validator = $this->makeValidator(
            $this->validData([
                'tag_ids' => [999999],
            ])
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('tag_ids.0', $validator->errors()->toArray());

        $this->assertSame(
            '選択されたタグが存在しません',
            $validator->errors()->first('tag_ids.0')
        );
    }
}
