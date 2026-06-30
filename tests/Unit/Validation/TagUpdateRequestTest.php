<?php

namespace Tests\Unit\Validation;

use App\Http\Requests\TagUpdateRequest;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class TagUpdateRequestTest extends TestCase
{
    use RefreshDatabase;

    private function makeValidator(array $data, Tag $tag)
    {
        $request = new TagUpdateRequest;

        $route = new Route(['PUT'], 'tags/{tag}', []);
        $route->bind($request);
        $route->setParameter('tag', $tag);

        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        return Validator::make(
            $data,
            $request->rules(),
            $request->messages()
        );
    }

    private function validData(array $override = []): array
    {
        return array_merge([
            'name' => 'テストタグ１',
        ], $override);
    }

    public function test_タグ名を変更できる(): void
    {
        $tag = Tag::create([
            'name' => 'テストタグ２',
        ]);

        $validator = $this->makeValidator(
            $this->validData([
                'name' => 'テストタグ１',
            ]),
            $tag
        );

        $this->assertFalse($validator->fails());
    }

    public function test_更新時に自身の名前維持は可能である(): void
    {
        $tag = Tag::create([
            'name' => 'テストタグ１',
        ]);

        $validator = $this->makeValidator(
            $this->validData([
                'name' => 'テストタグ１',
            ]),
            $tag
        );

        $this->assertFalse($validator->fails());
    }

    public function test_タグ名が空の場合はバリデーションエラーになる(): void
    {
        $tag = Tag::create([
            'name' => 'テストタグ１',
        ]);

        $validator = $this->makeValidator(
            $this->validData([
                'name' => '',
            ]),
            $tag
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }

    public function test_更新時にタグ名は文字列でなければならない(): void
    {
        $tag = Tag::create([
            'name' => 'テストタグ１',
        ]);

        $validator = $this->makeValidator(
            $this->validData([
                'name' => ['テストタグ１'],
            ]),
            $tag
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }

    public function test_更新時もタグ名は50文字まで入力できる(): void
    {
        $tag = Tag::create([
            'name' => 'テストタグ１',
        ]);

        $validator = $this->makeValidator(
            $this->validData([
                'name' => str_repeat('a', 50),
            ]),
            $tag
        );

        $this->assertFalse($validator->fails());
    }

    public function test_更新時にタグ名が50文字を超えるとバリデーションエラーになる(): void
    {
        $tag = Tag::create([
            'name' => 'テストタグ１',
        ]);

        $validator = $this->makeValidator(
            $this->validData([
                'name' => str_repeat('a', 51),
            ]),
            $tag
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }

    public function test_他で既に使用されているタグ名への変更はバリデーションエラーになる(): void
    {
        $existingTag = Tag::create([
            'name' => 'テストタグ１',
        ]);

        $targetTag = Tag::create([
            'name' => 'テストタグ２',
        ]);

        $validator = $this->makeValidator(
            $this->validData([
                'name' => $existingTag->name,
            ]),
            $targetTag
        );

        $this->assertTrue($validator->fails());

        $this->assertArrayHasKey(
            'name',
            $validator->errors()->toArray()
        );
    }
}
