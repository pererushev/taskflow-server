// tests/Feature/Api/TaskControllerTest.php
it('allows team member to create task', function () {
    $user = User::factory()->create();
    $team = Team::factory()->hasAttached($user, ['role' => 'member'])->create();
    $project = Project::factory()->for($team)->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/tasks', [
            'project_id' => $project->id,
            'title' => 'Implement API',
            'status' => 'todo'
        ]);

    $response->assertCreated();
    expect(Task::count())->toBe(1);
});