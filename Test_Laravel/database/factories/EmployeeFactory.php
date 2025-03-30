<?php

namespace Database\Factories;

use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'image_path' => $this->faker->imageUrl(200, 200, 'people'),
            'employee_name' => $this->faker->name(),
            'user_id' => User::factory(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => '+380' . $this->faker->unique()->numberBetween(500000000, 999999999),
            'position_name' => $this->faker->jobTitle(),
            'position_id' => Str::uuid(),
            'salary' => $this->faker->randomFloat(2, 2000, 10000),
            'supervisor_name' => $this->faker->name(),
            'supervisor_id' => User::factory(),
            'employment_date' => $this->faker->date(),
            'admin_created_id' => Str::uuid(),
            'admin_updated_id' => Str::uuid(),
        ];
    }
}
