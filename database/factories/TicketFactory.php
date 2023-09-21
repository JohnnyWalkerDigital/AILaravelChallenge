<?php

namespace Database\Factories;

use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'subject' => fake()->sentence(rand(1, 5)),
            'content' => fake()->sentence(rand(30, 70)),
            // 'status' not needed as default state is false
        ];
    }

    /**
     * Allow generation of tickets without user.
     *
     * @return $this
     */
    public function generateUser(): static
    {
        return $this->state(
            function (array $attributes) {
                // Get count to ensure there's at least one user
                $userCount = User::count();

                // Randomly create new users so there isn't a 1:1 between users and tickets
                if (!$userCount || rand(0, 1)) {
                    $user = User::factory()->create();
                } else {
                    $user = User::inRandomOrder()->first();
                }

                return [
                    'user_id' => $user->id,
                ];
            }
        );
    }

    /**
     * Create a ticket that's already processed.
     *
     * @return $this
     */
    public function processed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => true,
        ]);
    }

    /**
     * Create a ticket with a random processed status.
     *
     * @return $this
     */
    public function randomStatus(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => rand(0, 1),
        ]);
    }
}
