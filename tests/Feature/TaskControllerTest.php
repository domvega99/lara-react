<?php

namespace Tests\Feature;

use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase; // Reset the database for each test

    // Test 1: Check if tasks can be retrieved without authentication (unauthenticated)
    public function test_tasks_can_be_retrieved_without_authentication()
    {
        $response = $this->getJson('/api/tasks'); // Assuming the route for the index method is '/api/tasks'
        
        // Assert that it returns an unauthorized response
        $response->assertStatus(401); 
    }

    // Test 2: Check if tasks can be retrieved with authentication
    public function test_tasks_can_be_retrieved_with_authentication()
    {
        // Create a test user and authenticate with Sanctum
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');
        
        // Create some tasks
        Task::factory()->count(5)->create();
        
        // Request the tasks
        $response = $this->getJson('/api/tasks');
        
        // Assert that it returns a successful response
        $response->assertStatus(200);

        // Assert that the response contains the correct pagination structure
        $response->assertJsonStructure([
            'current_page',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ],
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
            'links' => [
                '*' => [
                    'url',
                    'label',
                    'active',
                ],
            ],
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total',
        ]);
    }

    
    // Test 3: Test searching functionality in tasks
    public function test_search_functionality_for_tasks()
    {
        // Create a test user and authenticate
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');
        
        // Create some tasks with specific titles and descriptions
        Task::factory()->create(['title' => 'Test Task 1', 'description' => 'First task description']);
        Task::factory()->create(['title' => 'Test Task 2', 'description' => 'Second task description']);
        Task::factory()->create(['title' => 'Another Task', 'description' => 'Another task description']);
        
        // Search for tasks with the term 'Test'
        $response = $this->getJson('/api/tasks?search=Test');
        
        // Assert that the response is successful
        $response->assertStatus(200);

        // Assert that the returned tasks only contain the ones with 'Test' in the title or description
        $response->assertJsonFragment(['title' => 'Test Task 1']);
        $response->assertJsonFragment(['title' => 'Test Task 2']);
        
        // Ensure that tasks with 'Test' in the title are returned
        $response->assertJsonMissing(['title' => 'Another Task']);
    }

    // Test 4: Test pagination functionality
    public function test_task_pagination()
    {
        // Create a test user and authenticate
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');
        
        // Create 20 tasks for pagination test
        Task::factory()->count(20)->create();
        
        // Test the first page with 5 tasks per page
        $response = $this->getJson('/api/tasks?page=0&per_page=5');
        
        // Assert that the first page contains 5 tasks
        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');

        // Test the second page with 5 tasks per page
        $response = $this->getJson('/api/tasks?page=1&per_page=5');
        
        // Assert that the second page contains 5 tasks
        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    public function test_task_can_be_created()
    {
        // Create a test user and authenticate with Sanctum (if required)
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');
        
        // Data for the new task
        $taskData = [
            'title' => 'Test Task',
            'description' => 'This is a description of the test task.',
        ];
        
        // Send a POST request to store a new task
        $response = $this->postJson('/api/tasks', $taskData);
        
        // Assert that the task was created successfully (status code 201)
        $response->assertStatus(200);
        
        // Assert that the response contains the task data
        $response->assertJsonStructure([
            'task' => [
                'id',
                'title',
                'description',
                'created_at',
                'updated_at',
            ],
        ]);
        
        // Assert that the task is stored in the database
        $this->assertDatabaseHas('tasks', [
            'title' => 'Test Task',
            'description' => 'This is a description of the test task.',
        ]);
    }

    public function test_task_can_be_retrieved()
    {
        // Create a test user and authenticate (if required)
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');
        
        // Create a task
        $task = Task::factory()->create();

        // Send a GET request to retrieve the task
        $response = $this->getJson("/api/tasks/{$task->id}");
        
        // Assert the response status is 200 (OK)
        $response->assertStatus(200);

        // Assert the task is returned with the expected fields
        $response->assertJsonStructure([
            'id',
            'title',
            'description',
            'created_at',
            'updated_at',
        ]);
        
        // Optionally, assert that the returned task matches the created task
        $response->assertJson([
            'id' => $task->id,
            'title' => $task->title,
            'description' => $task->description,
        ]);
    }

    public function test_task_can_be_updated()
    {
        // Create a test user and authenticate (if required)
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');
        
        // Create a task
        $task = Task::factory()->create();
        
        // Data to update the task
        $updatedData = [
            'title' => 'Updated Task Title',
            'description' => 'Updated task description.',
        ];

        // Send a PUT request to update the task
        $response = $this->putJson("/api/tasks/{$task->id}", $updatedData);
        
        // Assert the response status is 200 (OK)
        $response->assertStatus(200);
        
        // Assert that the task's title and description were updated
        $response->assertJson([
            'id' => $task->id,
            'title' => 'Updated Task Title',
            'description' => 'Updated task description.',
        ]);
        
        // Optionally, assert that the task in the database was updated
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Task Title',
            'description' => 'Updated task description.',
        ]);
    }

    public function test_task_can_be_deleted()
    {
        // Create a test user and authenticate (if required)
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user, 'sanctum');
        
        // Create a task
        $task = Task::factory()->create();
        
        // Send a DELETE request to delete the task
        $response = $this->deleteJson("/api/tasks/{$task->id}");
        
        // Assert the response status is 200 (OK)
        $response->assertStatus(200);
        
        // Assert the response contains the success message
        $response->assertJson([
            'message' => 'Task deleted successfully',
        ]);
        
        // Assert the task is deleted from the database
        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);
    }
}
