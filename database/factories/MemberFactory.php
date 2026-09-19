<?php

namespace Database\Factories;

use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'member_code' => fake()->unique()->regexify('EM-[0-9]{4}'),
            'type' => fake()->randomElement(['pelajar', 'non_pelajar']),
            'name' => fake()->name(),
            'whatsapp' => fake()->phoneNumber(),
            'photo' => null,
            'gender' => fake()->randomElement(['laki-laki', 'perempuan']),
            'birth_date' => fake()->date(),
            'address' => fake()->address(),
        ];
    }
}
