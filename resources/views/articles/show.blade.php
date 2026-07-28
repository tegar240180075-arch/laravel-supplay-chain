@extends('layouts.app')

@section('title', $article->title)
@section('page_title', 'Detail Artikel')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <a href="{{ route('user.articles.index') }}" class="btn btn-outline-light mb-3">
            <i class="fa-solid fa-arrow-left me-2"></i>Kembali ke Daftar
        </a>
        
        <div class="glass-card">
            <h2 class="text-primary mb-2">{{ $article->title }}</h2>
            <div class="d-flex align-items-center mb-4 text-muted small">
                <span class="me-3"><i class="fa-regular fa-calendar me-1"></i> {{ $article->created_at->format('d M Y, H:i') }}</span>
                @if($article->status === 'draft')
                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-pen-ruler me-1"></i> Draft</span>
                @endif
                @auth
                    @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.articles.edit', $article->id) }}" class="ms-auto btn btn-sm btn-outline-warning">
                            <i class="fa-solid fa-edit me-1"></i> Edit di Admin
                        </a>
                    @endif
                @endauth
            </div>
            
            <div class="article-content text-light" style="line-height: 1.8; font-size: 1.1rem;">
                {!! nl2br(e($article->content)) !!}
            </div>
        </div>
    </div>
</div>
@endsection
