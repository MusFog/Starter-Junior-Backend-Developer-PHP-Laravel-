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











//            $supervisors = [];
//            $ns = config('factory.numberSpecimens');
//            $mel = config('factory.max_employee_level');
//
//            for ($i = 0; $i < $ns; $i++) {
//                $this->command->info('Seeding took: ' . $i . ' count.');
//                $users = User::factory(rand(1, 3))->create();
//
//                $position = Position::factory()->create([
//                    'admin_created_id' => $users[0]->id,
//                    'admin_updated_id' => $users[1]->id ?? $users[0]->id,
//                ]);
//
//                $position->admins()->attach([
//                    $users[0]->id,
//                    $users[1]->id ?? $users[0]->id,
//                ]);
//
//                $block = $i % $mel;
//
//                $supervisor = $block > 0 && isset($supervisors[$block - 1])
//                    ? $supervisors[$block - 1]
//                    : null;
//
//                $employee = Employee::factory()->create([
//                    'position_id' => $position->id,
//                    'position_name' => $position->name,
//                    'admin_created_id' => $users[0]->id,
//                    'admin_updated_id' => $users[1]->id ?? $users[0]->id,
//                    'level' => 4 - $block,
//                    'supervisor_id' => $supervisor?->id,
//                    'supervisor_name' => $supervisor?->employee_name,
//                ]);
//
//                $supervisors[$block] = $employee;
//            }
//
//            $admin = User::factory()->create([
//                'email' => 'admin@gmail.com',
//                'name' => 'Super Admin',
//                'password' => bcrypt('admin'),
//            ]);
//
//            $allPositions = Position::all();
//            foreach ($allPositions as $position) {
//                $position->admins()->syncWithoutDetaching([(string)$admin->id]);
//            }


        DB::transaction(function () {
            $ns = config('factory.numberSpecimens');
            $mel = config('factory.max_employee_level');

            $allUsersData = [];
            $users = [];
            $positionsData = [];
            $positionUserData = [];
            $employeesData = [];

            $supervisors = [];

            for ($i = 0; $i < $ns; $i++) {
                $this->command->info('Seeding took: ' . $i . ' count.');
                $mainUserId = (string) Str::uuid();
                $users[$i] = [
                    'id' => $mainUserId,
                    'name' => fake()->name(),
                    'email' => strtolower(fake()->firstName()) . rand(1000,999999) . '@example.com',
                    'phone' => '+380' . fake()->unique()->numberBetween(500000000, 999999999),
                    'password' => bcrypt('password'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $countUsers = rand(1, 3);
                $tempUsers = [];
                for ($u = 0; $u < $countUsers; $u++) {
                    $tempUsers[] = [
                        'id' => (string) Str::uuid(),
                        'name' => fake()->name(),
                        'email' => strtolower(fake()->firstName()) . rand(1000,999999) . '@example.com',
                        'phone' => '+380' . fake()->unique()->numberBetween(500000000, 999999999),
                        'password' => bcrypt('password'),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                foreach ($tempUsers as $usr) {
                    $allUsersData[] = $usr;
                }


                $userA = $tempUsers[0];
                $userB = $tempUsers[1] ?? $tempUsers[0];

                $posId = (string) Str::uuid();
                $posName = fake()->jobTitle();

                $positionsData[] = [
                    'id' => $posId,
                    'name' => $posName,
                    'admin_created_id' => $userA['id'],
                    'admin_updated_id' => $userB['id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];


                $positionUserData[] = [
                    'position_id' => $posId,
                    'user_id' => $userA['id'],
                ];
                if ($userB !== $userA) {
                    $positionUserData[] = [
                        'position_id' => $posId,
                        'user_id' => $userB['id'],
                    ];
                }

                $block = $i % $mel;
                $supervisor = ($block > 0 && isset($supervisors[$block - 1]))
                    ? $supervisors[$block - 1]
                    : null;

                $empId = (string) Str::uuid();
                $supervisorId = $supervisor['id'] ?? null;
                $supervisorName = $supervisor['employee_name'] ?? null;

                $employeesData[] = [
                    'id' => $empId,
                    'image_path' => fake()->imageUrl(200, 200, 'people'),
                    'employee_name' => fake()->name(),

                    'user_id' => $users[$i]['id'],

                    'email' => fake()->unique()->safeEmail(),
                    'phone' => '+380' . fake()->unique()->numberBetween(500000000, 999999999),

                    'position_id' => $posId,
                    'position_name' => $posName,
                    'level' => 4 - $block,
                    'salary' => fake()->randomFloat(2, 2000, 10000),

                    'supervisor_id' => $supervisorId,
                    'supervisor_name' => $supervisorName,
                    'employment_date' => fake()->date(),

                    'admin_created_id' => $userA['id'],
                    'admin_updated_id' => $userB['id'],

                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $supervisors[$block] = [
                    'id' => $empId,
                    'employee_name' => $employeesData[count($employeesData) - 1]['employee_name'],
                ];
            }

            User::insert($allUsersData);
            User::insert($users);

            Position::insert($positionsData);
            DB::table('position_user')->insert($positionUserData);
            Employee::insert($employeesData);



            $admin = User::factory()->create([
                'email' => 'admin@gmail.com',
                'name' => 'Super Admin',
                'password' => bcrypt('admin'),
            ]);

            $allPositions = Position::all();

            $pivotData = [];
            foreach ($allPositions as $position) {
                $pivotData[] = [
                    'position_id' => $position->id,
                    'user_id' => $admin->id,
                ];
            }

            DB::table('position_user')->insert($pivotData);
        });














        $duration = microtime(true) - $start;

        $this->command->info('Seeding took: ' . round($duration, 2) . ' seconds.');
    }


}
