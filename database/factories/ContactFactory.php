<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Category;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::inRandomOrder()->first()->id,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'gender' => fake()->numberBetween(1, 3),
            'email' => fake()->email(),
            'tel' => fake()->numerify('###########'),
            'address' => fake()->address(),
            'building' => fake()->optional(0.8)->randomElement([
                'グリーンハイツ101',
                'サンライズマンション202',
                'メゾン中央305',
                'コーポ青山102',
                'スカイレジデンス501',
            ]),
            'detail' => fake()->realText(120),
        ];
    }
}
