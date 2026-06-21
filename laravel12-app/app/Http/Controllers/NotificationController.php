<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display notifications dropdown content
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $unreadCount = $user->unreadNotifications()->count();

        return view('components.notification-bell', compact('notifications', 'unreadCount'));
    }

    /**
     * Mark notification as read and redirect
     */
    public function markAsRead(string $id, Request $request)
    {
        $user = Auth::user();
        
        $notification = $user->notifications()->find($id);
        
        if ($notification) {
            $notification->markAsRead();
            
            // Get action URL from notification data
            $actionUrl = $notification->data['action_url'] ?? route('dashboard');
            
            return redirect($actionUrl);
        }

        return redirect()->route('dashboard');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $user->unreadNotifications->markAsRead();
        
        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi ditandai sebagai dibaca',
        ]);
    }

    /**
     * Record first read receipt for disposisi
     */
    public function recordReadReceipt(Request $request): JsonResponse
    {
        $request->validate([
            'disposisi_id' => 'required|exists:disposisis,id',
        ]);

        $disposisi = \App\Models\Disposisi::findOrFail($request->disposisi_id);
        
        // Only record if this is the first time
        if (!$disposisi->is_read_first) {
            $disposisi->update([
                'is_read_first' => true,
                'first_read_at' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'first_read_at' => $disposisi->first_read_at->format('d M Y H:i'),
                'message' => 'Waktu pembacaan tercatat',
            ]);
        }

        return response()->json([
            'success' => true,
            'already_read' => true,
            'first_read_at' => $disposisi->first_read_at?->format('d M Y H:i'),
        ]);
    }

    /**
     * Get unread count (for AJAX polling)
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        
        return response()->json([
            'count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Get all notifications with pagination
     */
    public function getAll(Request $request)
    {
        $user = Auth::user();
        
        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }
}
