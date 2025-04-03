<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Seeder;

class UpdateEmployeeImagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        Employee::chunk(1000, function ($employees) use ($faker) {
            foreach ($employees as $i => $employee) {
                $this->command->info('Seeding photo: ' . $i . ' count.');

                $gender = $faker->randomElement(['men', 'women']);
                $photoId = $faker->numberBetween(0, 99);

                $employee->image_path = "https://randomuser.me/api/portraits/{$gender}/{$photoId}.jpg";
                $employee->save();
            }
        });
    }


}
