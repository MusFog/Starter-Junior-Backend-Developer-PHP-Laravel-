<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $start = microtime(true);

            $supervisors = [];
            $ns = config('factory.numberSpecimens');
            $mel = config('factory.max_employee_level');

            for ($i = 0; $i < $ns; $i++) {
                $this->command->info('Seeding took: ' . $i . ' count.');
                $users = User::factory(rand(1, 3))->create();

                $position = Position::factory()->create([
                    'admin_created_id' => $users[0]->id,
                    'admin_updated_id' => $users[1]->id ?? $users[0]->id,
                ]);

                $position->admins()->attach([
                    $users[0]->id,
                    $users[1]->id ?? $users[0]->id,
                ]);

                $block = $i % $mel;

                $supervisor = $block > 0 && isset($supervisors[$block - 1])
                    ? $supervisors[$block - 1]
                    : null;

                $employee = Employee::factory()->create([
                    'position_id' => $position->id,
                    'position_name' => $position->name,
                    'admin_created_id' => $users[0]->id,
                    'admin_updated_id' => $users[1]->id ?? $users[0]->id,
                    'level' => 4 - $block,
                    'supervisor_id' => $supervisor?->id,
                    'supervisor_name' => $supervisor?->employee_name,
                ]);

                $supervisors[$block] = $employee;
            }

            $admin = User::factory()->create([
                'email' => 'admin@gmail.com',
                'name' => 'Super Admin',
                'password' => bcrypt('admin'),
            ]);

            $allPositions = Position::all();
            foreach ($allPositions as $position) {
                $position->admins()->syncWithoutDetaching([(string)$admin->id]);
            }


        $duration = microtime(true) - $start;

        $this->command->info('Seeding took: ' . round($duration, 2) . ' seconds.');
    }
}
