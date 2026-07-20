@extends('layouts.app')

@section('title', 'Register')
@section('page_title', 'Buat Akun Baru')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="glass-card">
            <div class="text-center mb-4">
                <i class="fa-solid fa-user-plus fa-3x text-success mb-3"></i>
                <h4 class="mb-0 fw-bold">SC Risk Monitor</h4>
                <p class="text-muted small">Pendaftaran Akun Baru</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show bg-danger bg-opacity-10 text-danger border-danger" role="alert">
                    <ul class="mb-0 px-3">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('register') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label class="form-label text-muted small">Nama Lengkap</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-user"></i></span>
                        <input type="text" name="name" class="form-control bg-dark text-light border-secondary" value="{{ old('name') }}" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control bg-dark text-light border-secondary" value="{{ old('email') }}" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" class="form-control bg-dark text-light border-secondary" required>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label text-muted small">Konfirmasi Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-dark border-secondary text-muted"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password_confirmation" class="form-control bg-dark text-light border-secondary" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-success w-100 fw-bold py-2 mb-3">
                    <i class="fa-solid fa-user-plus me-2"></i> Register
                </button>

                <div class="text-center text-muted small">
                    Sudah punya akun? <a href="{{ route('login') }}" class="text-success text-decoration-none">Login di sini</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
