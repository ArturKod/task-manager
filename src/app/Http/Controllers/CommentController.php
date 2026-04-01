<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Comment::class);

        $comments = Comment::with(['user', 'task'])
        ->when($request->task_id, fn($q) => $q->where('task_id', $request->task_id))
        ->paginate(15);

        return CommentResource::collection($comments);
    }

    public function store(CommentRequest $request)
    {
        Gate::authorize('create', Comment::class);

        $comment = Comment::create([
            'task_id' => $request->task_id,
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);

        return new CommentResource($comment->load('user'));
    }

    public function show(Comment $comment)
    {
        Gate::authorize('view', $comment);

        return new CommentResource($comment->load(['user', 'task']));
    }

    public function update(CommentRequest $request, Comment $comment)
    {
        Gate::authorize('update', $comment);

        $comment->update($request->only('body'));

        return new CommentResource($comment->fresh()->load('user'));
    }

    public function destroy(Comment $comment)
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return response()->json([
            'message' => 'Comment deleted successfully'
        ]);
    }   
}
