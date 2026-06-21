@extends('layouts.app-bootstrap')

@section('title', $code . ' - ' . $title)

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-lg border-0">
                <div class="card-body text-center p-5">
                    <!-- Error Code Icon -->
                    <div class="mb-4">
                        @if($code == 404)
                            <i class="bi bi-search display-1 text-warning"></i>
                        @elseif($code == 403)
                            <i class="bi bi-lock display-1 text-danger"></i>
                        @elseif($code == 500)
                            <i class="bi bi-exclamation-triangle display-1 text-danger"></i>
                        @elseif($code == 503)
                            <i class="bi bi-wifi-off display-1 text-secondary"></i>
                        @else
                            <i class="bi bi-exclamation-circle display-1 text-secondary"></i>
                        @endif
                    </div>

                    <!-- Error Code -->
                    <h1 class="display-3 fw-bold text-{{ $code >= 500 ? 'danger' : 'warning' }}">
                        {{ $code }}
                    </h1>

                    <!-- Title -->
                    <h2 class="h4 fw-semibold mt-3 mb-2">{{ $title }}</h2>

                    <!-- Message -->
                    <p class="text-muted mb-4">{{ $message }}</p>

                    <!-- Action Buttons -->
                    <div class="d-grid gap-2 d-sm-flex justify-content-sm-center mt-4">
                        <a href="{{ url('/dashboard') }}" class="btn btn-primary px-4">
                            <i class="bi bi-house-door me-2"></i>Kembali ke Dashboard
                        </a>
                        <button onclick="history.back()" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-arrow-left me-2"></i>Kembali
                        </button>
                    </div>

                    <!-- Contact Support (for 500 errors) -->
                    @if($code >= 500)
                        <div class="alert alert-info mt-4 mb-0" role="alert">
                            <small>
                                <i class="bi bi-info-circle me-1"></i>
                                Jika masalah berlanjut, hubungi administrator sistem.
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Additional Info -->
            <div class="text-center mt-4">
                <p class="text-muted small">
                    Kode Error: <strong>{{ $code }}</strong> | 
                    Timestamp: <strong>{{ now()->format('d M Y H:i:s') }}</strong>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .card {
        border-radius: 1rem;
    }
    .display-1 {
        font-size: 5rem;
    }
    @media (max-width: 576px) {
        .display-1 {
            font-size: 3.5rem;
        }
        .display-3 {
            font-size: 3rem;
        }
        .card-body {
            padding: 2rem !important;
        }
    }
</style>
@endsection
