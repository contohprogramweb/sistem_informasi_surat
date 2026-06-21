<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        .error-container {
            text-align: center;
            padding: 3rem 1rem;
            max-width: 600px;
        }
        .error-code {
            font-size: 6rem;
            font-weight: 700;
            color: #dc3545;
            line-height: 1;
        }
        .error-message {
            font-size: 1.5rem;
            color: #6c757d;
            margin: 1.5rem 0;
        }
        .btn-primary {
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <i class="bi bi-exclamation-octagon display-1 text-danger"></i>
        <h1 class="error-code">@yield('code')</h1>
        <p class="error-message">@yield('message')</p>
        <p class="text-muted mb-4">@yield('description')</p>
        <a href="{{ url('/') }}" class="btn btn-primary btn-lg">
            <i class="bi bi-house-door me-2"></i>Kembali ke Dashboard
        </a>
    </div>
</body>
</html>
