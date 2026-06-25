<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
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
            'category_id' => Category::inRandomOrder()->value('id'),
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
