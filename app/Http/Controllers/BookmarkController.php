<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Models\Recipe;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->query('user_id');
        $recipeId = $request->query('recipe_id');
        $checkBookmark = $request->query('check_bookmark');

        if ($checkBookmark) {
            $bookmark = Bookmark::where('user_id', $userId)
                ->where('recipe_id', $recipeId)
                ->first();

            return response()->json($bookmark, 200);
        }

        $bookmarks = Bookmark::with(['recipe', 'recipe.user:id,name'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($bookmarks->isEmpty()) {
            return response()->json(['message' => 'No bookmarks available'], 404);
        }
        return response()->json($bookmarks, 200);
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'user_id' => 'required|integer',
            'recipe_id' => 'required|integer',
        ]);

        $bookmark = Bookmark::create($fields);

        $recipe = Recipe::find($fields['recipe_id']);
        if (!$recipe) {
            return response()->json(['message' => 'Recipe not found'], 404);
        }
        $recipe->increment('bookmark');

        return response()->json([
            'bookmark' => $bookmark,
            'recipe_bookmarks' => $recipe->bookmark
        ], 201);
    }

    public function show(Request $request, $id) {}

    public function update(Request $request, Bookmark $bookmark) {}

    public function destroy(Bookmark $bookmark)
    {
        if (!$bookmark) {
            return response()->json(['message' => 'Bookmark not found'], 404);
        }

        $recipe = $bookmark->recipe;
        $recipe->decrement('bookmark');
        $bookmark->delete();

        return response()->json(['message' => 'Bookmark was deleted'], 200);
    }
}
