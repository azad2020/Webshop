<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'customer_id' => function () {
                return \App\Models\Customer::factory()->create()->id;
            },
            'paid' => $this->faker->boolean,
        ];
    }
}
