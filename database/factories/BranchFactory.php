<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->city . ' فرع',
            'address' => $this->faker->address,
            'phone' => $this->faker->phoneNumber,
            'is_main' => false,
            'is_active' => true,
            'company_id' => Company::factory(),
        ];
    }
}
