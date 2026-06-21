<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Semua Notifikasi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if($notifications->count() > 0)
                        <div class="space-y-4">
                            @foreach($notifications as $notification)
                                <div class="flex items-start gap-4 p-4 rounded-lg border {{ $notification->read_at ? 'border-gray-200 bg-gray-50' : 'border-blue-200 bg-blue-50' }}">
                                    <span class="text-3xl">{{ $notification->data['icon'] ?? '📢' }}</span>
                                    
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <h3 class="font-semibold text-gray-900">
                                                {{ $notification->data['title'] ?? 'Notifikasi' }}
                                            </h3>
                                            @if(!$notification->read_at)
                                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                                    Baru
                                                </span>
                                            @endif
                                        </div>
                                        
                                        <p class="text-gray-600 mt-1">
                                            {{ $notification->data['message'] ?? '' }}
                                        </p>
                                        
                                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                                            <span>{{ $notification->created_at->diffForHumans() }}</span>
                                            
                                            @if($notification->data['action_url'])
                                                <a href="{{ $notification->data['action_url'] }}" 
                                                   class="text-blue-600 hover:text-blue-800 font-medium">
                                                    Lihat Detail →
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="text-gray-500 text-lg">Tidak ada notifikasi</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
