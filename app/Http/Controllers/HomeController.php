<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function userRecipe(Request $request)
    {
        $userId = $request->query('user_id');

        $recipes = Recipe::where('user_id', $userId)->orderBy('created_at', 'desc')->get();

        return response()->json($recipes, 200);
    }

    public function bestRecipe()
    {
        $recipes = Recipe::with('user:id,name')->orderBy('like', 'desc')->limit(10)->get();

        return response()->json($recipes, 200);
    }

    public function search(Request $request)
    {
        $query = $request->query->get('query');

        $recipes = Recipe::where('name', 'LIKE', "%{$query}%")
            ->orWhere('description', 'LIKE', "%{$query}%")
            ->orderBy('like', 'desc')
            ->paginate(20);

        return response()->json($recipes, 200);
    }
}
