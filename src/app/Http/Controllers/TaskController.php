<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Task::class);

        $tasks = Task::with('project')
            ->whereHas('project', function($query) {
                $query->where('owner_id', Auth::id());
            })
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->priority, fn($q, $priority) => $q->where('priority', $priority))
            ->when($request->search, fn($q, $search) => $q->where('title', 'like', '%' . $search . '%'))
            ->paginate(15);

        return TaskResource::collection($tasks);
    }

    public function store(TaskRequest $request)
    {
        Gate::authorize('create', Task::class);

        $project = Project::where('id', $request->project_id)
            ->where('owner_id', Auth::id())
            ->firstOrFail();
        
        $task = Task::create([
            'project_id' => $project->id,
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'new',
            'priority' => $request->priority ?? 'normal',
            'due_date' => $request->due_date,
        ]);

        return new TaskResource($task->load('project'));
    }

    public function show(Task $task)
    {
        Gate::authorize('view', $task);
        
        return new TaskResource($task->load('project'));
    }

    public function update(TaskRequest $request, Task $task)
    {
        Gate::authorize('update', $task);
        
        $task->update($request->validated());

        return new TaskResource($task->fresh()->load('project'));
    }

    public function destroy(Task $task)
    {
        Gate::authorize('delete', $task);
        
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }
}