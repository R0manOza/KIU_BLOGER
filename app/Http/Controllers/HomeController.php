<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Landing page with a hero section and the most recent posts.
     */
    public function index(): View
    {
        $featured = Post::with(['user', 'category'])
            ->withCount('comments')
            ->withSum('votes', 'value')
            ->published()
            ->latest()
            ->take(3)
            ->get();

        $categories = Category::withCount('posts')->orderBy('name')->get();
        $postsCount = Post::published()->count();

        return view('home', compact('featured', 'categories', 'postsCount'));
    }
}
