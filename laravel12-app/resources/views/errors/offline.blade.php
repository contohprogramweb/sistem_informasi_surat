<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline - SIAP-SMK</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .offline-container {
            text-align: center;
            color: white;
            padding: 2rem;
        }
        .offline-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.7; transform: scale(1.1); }
        }
        .btn-light {
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 50px;
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon">📡</div>
        <h1 class="display-4 fw-bold mb-3">Anda Offline</h1>
        <p class="lead mb-4">
            Koneksi internet Anda terputus. Beberapa fitur mungkin tidak tersedia.<br>
            Halaman yang pernah dibuka masih dapat diakses dari cache.
        </p>
        <button onclick="window.location.reload()" class="btn btn-light btn-lg">
            🔄 Coba Lagi
        </button>
        <p class="mt-4 small opacity-75">
            Periksa koneksi internet Anda dan klik tombol di atas untuk memuat ulang.
        </p>
    </div>
</body>
</html>
