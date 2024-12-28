<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Recipe;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    public function index(Request $request)
    {

        $user_id = $request->query('user_id');
        $recipe_id = $request->query('recipe_id');

        $like = Like::where('user_id', $user_id)
            ->where('recipe_id', $recipe_id)
            ->first();

        if (!$like) {
            return response()->json(['message' => 'Like not found'], 404);
        }

        return response()->json($like, 200);
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'user_id' => 'required|integer',
            'recipe_id' => 'required|integer',
        ]);

        $like = Like::create($fields);

        $recipe = Recipe::find($fields['recipe_id']);
        if (!$recipe) {
            return response()->json(['message' => 'Recipe not found'], 404);
        }
        $recipe->increment('like');

        return response()->json([
            'like' => $like,
            'recipe_likes' => $recipe->like
        ], 201);
    }

    public function show(Like $like) {}

    public function update(Request $request, Like $like) {}

    public function destroy(Like $like)
    {
        if (!$like) {
            return response()->json(['message' => 'Like not found'], 404);
        }

        $recipe = $like->recipe;
        $recipe->decrement('like');
        $like->delete();

        return response()->json(['message' => 'Like was deleted'], 200);
    }
}
