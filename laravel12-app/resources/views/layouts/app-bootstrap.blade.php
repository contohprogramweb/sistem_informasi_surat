<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistem Informasi Arsip & Persuratan - SMK">
    <meta name="theme-color" content="#0d6efd">
    
    <!-- PWA Manifest -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('icons/icon-72x72.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    
    <title>@yield('title', config('app.name', 'SIAP-SMK'))</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap 5 CSS (CDN) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- DataTables CSS (CDN) -->
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="{{ asset('css/responsive-custom.css') }}" rel="stylesheet">
    
    @stack('styles')
</head>
<body class="antialiased bg-light">
    <div class="min-vh-100 d-flex flex-column">
        <!-- Navigation -->
        @include('layouts.navigation-bootstrap')
        
        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Main Content -->
        <main class="py-4 flex-grow-1">
            <div class="container-fluid">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>{{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="bi bi-info-circle me-2"></i>{{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @yield('content')
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="bg-white border-top py-3 mt-auto">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-6 text-center text-md-start">
                        <small class="text-muted">&copy; {{ date('Y') }} SIAP-SMK. All rights reserved.</small>
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <small class="text-muted">Versi 1.0.0</small>
                    </div>
                </div>
            </div>
        </footer>
    </div>
    
    <!-- jQuery (CDN) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap 5 JS Bundle (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS (CDN) -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Chart.js (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Sidebar Toggle for Mobile
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.toggle('active');
                    }
                });
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    if (sidebar) {
                        sidebar.classList.remove('active');
                    }
                    sidebarOverlay.classList.remove('active');
                });
            }
            
            // Close sidebar on window resize if screen is large
            window.addEventListener('resize', function() {
                if (window.innerWidth > 991 && sidebar) {
                    sidebar.classList.remove('active');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.remove('active');
                    }
                }
            });
            
            // Register Service Worker for PWA
            if ('serviceWorker' in navigator) {
                window.addEventListener('load', function() {
                    navigator.serviceWorker.register('/sw.js')
                        .then(function(registration) {
                            console.log('[SW] Registration successful:', registration.scope);
                        })
                        .catch(function(error) {
                            console.log('[SW] Registration failed:', error);
                        });
                });
            }
            
            // Real-time notification polling (every 60 seconds)
            @auth
            function fetchNotificationCounts() {
                fetch('{{ route("api.notification-counts") }}')
                    .then(response => response.json())
                    .then(data => {
                        const badge = document.getElementById('notificationBadge');
                        const menu = document.getElementById('notificationMenu');
                        const total = data.total || 0;
                        
                        if (total > 0) {
                            badge.textContent = total > 99 ? '99+' : total;
                            badge.style.display = 'inline-block';
                            
                            // Update notification menu
                            if (menu && data.notifications) {
                                let html = '<li><h6 class="dropdown-header">Notifikasi</h6></li>';
                                html += '<li><hr class="dropdown-divider"></li>';
                                
                                data.notifications.forEach(notif => {
                                    html += `<li><a class="dropdown-item small" href="${notif.url}">${notif.message}</a></li>`;
                                });
                                
                                if (data.notifications.length === 0) {
                                    html += '<li><a class="dropdown-item text-center small" href="#">Tidak ada notifikasi</a></li>';
                                }
                                
                                menu.innerHTML = html;
                            }
                        } else {
                            badge.style.display = 'none';
                        }
                    })
                    .catch(error => console.error('Notification fetch error:', error));
            }
            
            // Initial fetch and then every 60 seconds
            fetchNotificationCounts();
            setInterval(fetchNotificationCounts, 60000);
            @endauth
        });
    </script>
    
    @stack('scripts')
</body>
</html>
