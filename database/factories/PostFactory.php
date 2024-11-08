<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $users = User::all();
        $user = $users->random();
        $user_id = $user->id;
        $user_tenant = $user->tenant_id;
        return [
            'title' => fake()->title(),
            'content' => fake()->text(100),
            'user_id' => $user_id,
            'tenant_id' => $user_tenant
        ];
    }
}
