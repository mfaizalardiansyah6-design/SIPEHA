<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<User>
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nip' => fake()->unique()->numerify('#################').'1',
            'name' => fake('id_ID')->name(),
            'email' => fake()->unique()->safeEmail(),
            'jabatan' => fake()->randomElement(['Petugas Registrasi', 'Petugas Bimkemas', 'Petugas Kamtib', 'Petugas Kesehatan']),
            'role' => UserRole::User,
            'password' => 'password',
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => null,
        ]);
    }
}
