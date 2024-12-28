<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Recipe;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $recipe_id = $request->query('recipe_id');

        $comments = Comment::with('user:id,name,image')
            ->where('recipe_id', $recipe_id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        if ($comments->isEmpty()) {
            return response()->json(['message' => 'No comments available'], 404);
        }

        return response()->json($comments, 200);
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'user_id' => 'required|integer',
            'recipe_id' => 'required|integer',
            'description' => 'required|string',
        ]);

        $comment = Comment::create($fields);

        $recipe = Recipe::find($fields['recipe_id']);
        if (!$recipe) {
            return response()->json(['message' => 'Recipe not found'], 404);
        }
        $recipe->increment('comment');

        return response()->json([
            'comment' => $comment,
            'recipe_comment' => $recipe->comment
        ], 201);
    }

    public function show(Comment $comment) {}

    public function update(Request $request, Comment $comment) {}

    public function destroy(Comment $comment)
    {
        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $recipe = $comment->recipe;
        $recipe->decrement('comment');
        $comment->delete();

        return response()->json(['message' => 'Comment was deleted'], 200);
    }
}
