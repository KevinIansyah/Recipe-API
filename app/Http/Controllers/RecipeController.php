<?php

namespace App\Http\Controllers;

use App\Models\Recipe;
use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Models\Ingredient;
use App\Models\Step;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RecipeController extends Controller implements HasMiddleware
{
    public static function middleware()
    {
        return [
            new Middleware('auth:sanctum', except: ['index', 'show'])
        ];
    }

    public function index()
    {
        $recipes = Recipe::with('user:id,name')->orderBy('created_at', 'desc')->paginate(20);

        return response()->json($recipes, 200);
    }

    public function store(Request $request)
    {
        $fields = $request->validate([
            'user_id' => 'required|integer',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|integer|min:1',
            'servings' => 'required|integer|min:1',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'media' => 'nullable|url',
            'ingredients' => 'required|array|min:1',
            'ingredients.*' => 'required|string|max:255',
            'steps' => 'required|array|min:1',
            'steps.*' => 'required|string',
        ]);

        if ($request->hasFile('image')) {
            $timestamp = now()->timestamp;
            $slugName = Str::slug($fields['name']);
            $extension = $request->file('image')->getClientOriginalExtension();
            $fileName = "{$timestamp}-{$slugName}.{$extension}";

            $fields['image'] = $request->file('image')->storeAs('recipes/images', $fileName, 'public');
        }
        $fields['media'] = $request->input('media', null);

        $recipe = Recipe::create($fields);

        foreach ($fields['ingredients'] as $index => $ingredient) {
            Ingredient::create([
                'recipe_id' => $recipe->id,
                'description' => $ingredient,
                'order' => $index + 1,
            ]);
        }

        foreach ($fields['steps'] as $index => $step) {
            Step::create([
                'recipe_id' => $recipe->id,
                'description' => $step,
                'order' => $index + 1,
            ]);
        }

        return response()->json($recipe, 201);
    }

    public function show($id)
    {
        $recipe = Recipe::with([
            'ingredients' => function ($query) {
                $query->select('id', 'recipe_id', 'order', 'description')->orderBy('order', 'asc');
            },
            'steps' => function ($query) {
                $query->select('id', 'recipe_id', 'order', 'description')->orderBy('order', 'asc');
            },
            'user' => function ($query) {
                $query->select('id', 'name', 'image');
            },
        ])->where('id', $id)->first();

        if (!$recipe) {
            return response()->json(['message' => 'Recipe not found'], 404);
        }

        return response()->json($recipe, 200);
    }

    public function update(Request $request, Recipe $recipe)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|integer|min:1',
            'servings' => 'required|integer|min:1',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'media' => 'nullable|url',
            'ingredients' => 'nullable|array|min:1',
            'ingredients.*' => 'nullable|string|max:255',
            'steps' => 'nullable|array|min:1',
            'steps.*' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $timestamp = now()->timestamp;
            $slugName = Str::slug($fields['name']);
            $extension = $request->file('image')->getClientOriginalExtension();
            $fileName = "{$timestamp}-{$slugName}.{$extension}";

            $fields['image'] = $request->file('image')->storeAs('recipes/images', $fileName, 'public');

            if ($recipe->image) {
                Storage::disk('public')->delete($recipe->image);
            }
        }

        $recipe->update($fields);

        if (isset($fields['ingredients'])) {
            $recipe->ingredients()->delete();

            foreach ($fields['ingredients'] as $index => $ingredient) {
                Ingredient::create([
                    'recipe_id' => $recipe->id,
                    'description' => $ingredient,
                    'order' => $index + 1,
                ]);
            }
        }

        if (isset($fields['steps'])) {
            $recipe->steps()->delete();

            foreach ($fields['steps'] as $index => $step) {
                Step::create([
                    'recipe_id' => $recipe->id,
                    'description' => $step,
                    'order' => $index + 1,
                ]);
            }
        }

        return response()->json($recipe, 200);
    }

    public function destroy(Recipe $recipe)
    {
        if ($recipe->image) {
            Storage::disk('public')->delete($recipe->image);
        }

        $recipe->delete();

        return response()->json(['message' => 'The recipe was deleted'], 200);
    }
}
