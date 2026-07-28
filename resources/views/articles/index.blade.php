@extends('layouts.app')

@section('title', 'Artikel & Publikasi')
@section('page_title', 'Artikel & Publikasi')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="glass-card">
            <h5 class="border-bottom border-secondary pb-3 mb-4">Daftar Artikel</h5>
            
            @if($articles->count() > 0)
                <div class="row g-4">
                    @foreach($articles as $article)
                        <div class="col-md-6 col-lg-4">
                            <div class="card bg-dark text-white border-secondary h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-primary">{{ $article->title }}</h5>
                                    <h6 class="card-subtitle mb-2 text-muted small">
                                        {{ $article->created_at->format('d M Y') }}
                                    </h6>
                                    <p class="card-text text-light">
                                        {{ Str::limit(strip_tags($article->content), 120) }}
                                    </p>
                                    <a href="{{ route('user.articles.show', $article->id) }}" class="btn btn-outline-info btn-sm">Baca Selengkapnya</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <div class="mt-4">
                    {{ $articles->links() }}
                </div>
            @else
                <div class="alert alert-secondary bg-dark text-white border-secondary">
                    Belum ada artikel yang dipublikasikan.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
