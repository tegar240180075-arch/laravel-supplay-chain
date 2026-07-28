@extends('layouts.app')

@section('title', 'Buat Artikel Baru')
@section('page_title', 'Buat Artikel Baru')

@section('content')
<div class="row mb-4">
    <div class="col-12 col-xl-8">
        <a href="{{ route('admin.articles.index') }}" class="btn btn-outline-light mb-3">
            <i class="fa-solid fa-arrow-left me-2"></i>Kembali
        </a>

        <div class="glass-card">
            <h5 class="border-bottom border-secondary pb-3 mb-4">Formulir Artikel Baru</h5>

            @if($errors->any())
                <div class="alert alert-danger bg-dark text-danger border-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('admin.articles.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="title" class="form-label text-light">Judul Artikel</label>
                    <input type="text" class="form-control bg-dark text-white border-secondary" id="title" name="title" value="{{ old('title') }}" required autofocus>
                </div>
                
                <div class="mb-3">
                    <label for="status" class="form-label text-light">Status</label>
                    <select class="form-select bg-dark text-white border-secondary" id="status" name="status" required>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Dipublikasikan</option>
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="content" class="form-label text-light">Konten Artikel</label>
                    <textarea class="form-control bg-dark text-white border-secondary" id="content" name="content" rows="15" required>{{ old('content') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary px-4">
                    <i class="fa-solid fa-save me-2"></i>Simpan Artikel
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
