@extends('layouts.app')

@section('title', 'Kelola Artikel')
@section('page_title', 'Kelola Artikel')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success bg-dark text-success border-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="glass-card">
            <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-3 mb-4">
                <h5 class="mb-0">Daftar Artikel (Admin)</h5>
                <a href="{{ route('admin.articles.create') }}" class="btn btn-primary">
                    <i class="fa-solid fa-plus me-2"></i>Buat Artikel Baru
                </a>
            </div>
            
            <div class="table-responsive">
                <table class="table table-dark table-hover border-secondary align-middle">
                    <thead>
                        <tr>
                            <th scope="col" width="5%">ID</th>
                            <th scope="col" width="30%">Judul</th>
                            <th scope="col" width="20%">Status</th>
                            <th scope="col" width="20%">Tanggal Dibuat</th>
                            <th scope="col" width="25%" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($articles as $article)
                            <tr>
                                <td>{{ $article->id }}</td>
                                <td>
                                    <div class="fw-bold">{{ Str::limit($article->title, 50) }}</div>
                                </td>
                                <td>
                                    @if($article->status === 'published')
                                        <span class="badge bg-success">Dipublikasikan</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Draft</span>
                                    @endif
                                </td>
                                <td>{{ $article->created_at->format('d M Y H:i') }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.articles.show', $article->id) }}" class="btn btn-sm btn-info" title="Lihat">
                                        <i class="fa-solid fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.articles.edit', $article->id) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fa-solid fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.articles.destroy', $article->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus artikel ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada artikel. Silakan buat artikel pertama Anda.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-3">
                {{ $articles->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
