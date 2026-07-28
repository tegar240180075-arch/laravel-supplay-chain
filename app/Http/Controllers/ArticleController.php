<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::where('status', 'published')->latest()->paginate(10);
        return view('articles.index', compact('articles'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        if ($article->status !== 'published' && !auth()->user()->isAdmin()) {
            abort(404);
        }
        return view('articles.show', compact('article'));
    }
}
