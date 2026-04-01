<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Project::class);

        $projects = Project::with('owner')
            ->when($request->user(), fn($query) => $query->where('owner_id', $request->user()->id))
            ->paginate(15);

        return ProjectResource::collection($projects);
    }

    public function store(ProjectRequest $request)
    {
        Gate::authorize('create', Project::class);

        $project = Project::create([
            'name' => $request->name,
            'owner_id' => $request->user()->id,
        ]);

        return new ProjectResource($project);
    }

    public function show(Project $project)
    {
        Gate::authorize('view', $project);

        return new ProjectResource($project->load('owner'));
    }

    public function update(ProjectRequest $request, Project $project)
    {
        Gate::authorize('update', $project);

        $project->update($request->validated());

        return new ProjectResource($project);
    }

    public function destroy(Project $project)
    {
        Gate::authorize('delete', $project);

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }
}
