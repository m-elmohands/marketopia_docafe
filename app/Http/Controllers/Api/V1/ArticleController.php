<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ArticleStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ArticleResource;
use App\Models\Article;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ArticleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ArticleResource::collection(Article::query()
            ->with(['translations', 'media'])
            ->where('status', ArticleStatus::Published)
            ->where('published_at', '<=', now())
            ->when(request('featured'), fn ($query, $featured) => $query->where('is_featured', filter_var($featured, FILTER_VALIDATE_BOOL)))
            ->when(request('search'), fn ($query, $search) => $query->whereHas('translations', fn ($query) => $query->where('title', 'like', '%'.$search.'%')))
            ->latest('published_at')
            ->paginate(min(max((int) request('per_page', 15), 1), 50)));
    }

    public function show(string $slug): ArticleResource
    {
        return new ArticleResource(Article::query()
            ->with(['translations', 'media'])
            ->where('status', ArticleStatus::Published)
            ->whereHas('translations', fn ($query) => $query->where('slug', $slug))
            ->firstOrFail());
    }
}
