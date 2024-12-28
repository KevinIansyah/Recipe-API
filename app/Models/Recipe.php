<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recipe extends Model
{
    /** @use HasFactory<\Database\Factories\RecipeFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'duration',
        'servings',
        'description',
        'image',
        'media',
        'like',
        'bookmark',
        'comment',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function ingredients()
    {
        return $this->hasMany(Ingredient::class);
    }

    public function steps()
    {
        return $this->hasMany(Step::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function bookmark()
    {
        return $this->hasMany(Bookmark::class);
    }

    public function comment()
    {
        return $this->hasMany(Comment::class);
    }
}
