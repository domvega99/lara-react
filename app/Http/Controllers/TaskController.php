<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TaskController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            // new Middleware('auth:sanctum', except: ['index', 'show'])
            new Middleware('auth:sanctum')
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Task::query();
    
        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $searchTerm . '%');
        }
    
        $page = $request->input('page', 0); 
        $perPage = $request->input('per_page', 5); 
    
        $tasks = $query->paginate($perPage, ['*'], 'page', $page + 1); 
    
        $tasks->setPageName('page');
        $tasks->appends($request->except('page')); 
    
        return response()->json($tasks);
    }    
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
        ]);

        $task = Task::create($request);

        return [ 
            'task' => $task
        ];
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return $task;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $request = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
        ]);

        $task->update($request);

        return $task;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return [
            'message' => 'Task deleted successfully'
        ];
    }
}
