@component('mail::message')
# Notifikasi SIAP-SMK

Halo {{ $user->name }},

Anda menerima notifikasi baru dari sistem SIAP-SMK:

## {{ $notificationData['title'] ?? 'Notifikasi' }}

{{ $notificationData['message'] ?? '' }}

@if(isset($notificationData['action_url']))
@component('mail::button', ['url' => $notificationData['action_url']])
Lihat Detail
@endcomponent
@endif

---

**Jenis Notifikasi:** {{ ucfirst(str_replace('_', ' ', $notificationData['type'] ?? '')) }}  
**Waktu:** {{ $notificationData['timestamp'] ?? now()->format('d M Y H:i') }}

Jika Anda tidak ingin menerima email notifikasi, Anda dapat mengatur preferensi di pengaturan akun Anda.

Terima kasih,<br>
{{ config('app.name') }}
@endcomponent
