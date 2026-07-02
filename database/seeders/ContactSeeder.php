<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contacts = Contact::factory()
            ->count(20)
            ->create();

        $contacts->each(function (Contact $contact): void {
            $tagIds = Tag::inRandomOrder()
                ->limit(random_int(1, 3))
                ->pluck('id');

            $contact->tags()->attach($tagIds);
        });
    }
}
