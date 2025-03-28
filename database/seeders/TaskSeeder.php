<?php

namespace Database\Seeders;

use App\Models\Task;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Task::create([
            'id' => 1,
            'title' => 'First Task',
            'description' => 'This is the content of the first task.',
        ]);

        Task::create([
            'id' => 2,
            'title' => 'Second Task',
            'description' => 'This is the content of the second task.',
        ]);
    }
}
