<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company,
            'subdomain' => $this->faker->unique()->slug,
            'email' => $this->faker->unique()->safeEmail,
            'phone' => $this->faker->phoneNumber,
            'logo' => null,
            'address' => $this->faker->address,
            'package_id' => null,
            'subscription_ends_at' => now()->addMonths(6),
            'trial_ends_at' => now()->addDays(14),
            'max_users' => 10,
            'max_branches' => 3,
            'locale' => 'ar',
            'is_active' => true,
        ];
    }
}
