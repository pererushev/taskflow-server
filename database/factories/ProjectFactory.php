<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'created_by' => User::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
