<?php

use App\Models\OrganizationProfile;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public function mount(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }
    }

    public function approve(string $profileId): void
    {
        $profile = OrganizationProfile::findOrFail($profileId);
        $profile->update([
            'verification_status' => 'verified',
            'verified_at' => now(),
        ]);

        session()->flash('status', "Organisasi \"{$profile->user->name}\" berhasil disetujui!");
    }

    public function reject(string $userId): void
    {
        $user = User::findOrFail($userId);
        $name = $user->name;
        
        $user->delete();

        session()->flash('status', "Organisasi \"{$name}\" ditolak dan akun telah dihapus.");
    }
}; ?>

<div>
    <h1>{{ __('Persetujuan Akun Organisasi (Ormawa)') }}</h1>

    @if (session('status'))
        <div style="border: 1px solid green; padding: 10px; margin-bottom: 20px; background-color: #e6ffe6; color: green;">
            {{ session('status') }}
        </div>
    @endif

    @php
        $pendingProfiles = OrganizationProfile::where('verification_status', 'pending')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    @endphp

    @if ($pendingProfiles->isEmpty())
        <p><em>{{ __('Tidak ada pendaftaran organisasi yang memerlukan persetujuan.') }}</em></p>
    @else
        <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; text-align: left;">
            <thead>
                <tr>
                    <th>{{ __('Nama Organisasi') }}</th>
                    <th>{{ __('Email') }}</th>
                    <th>{{ __('Tingkat') }}</th>
                    <th>{{ __('Tanggal Daftar') }}</th>
                    <th>{{ __('Deskripsi') }}</th>
                    <th>{{ __('Tindakan') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pendingProfiles as $profile)
                    @if ($profile->user)
                        <tr>
                            <td>{{ $profile->user->name }}</td>
                            <td>{{ $profile->user->email }}</td>
                            <td>{{ ucfirst(str_replace('_', ' ', $profile->organization_level)) }}</td>
                            <td>{{ $profile->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <details>
                                    <summary>{{ __('Lihat Deskripsi') }}</summary>
                                    <p>{{ $profile->description ?: 'Tidak ada deskripsi.' }}</p>
                                </details>
                            </td>
                            <td>
                                <button wire:click="approve('{{ $profile->organization_profile_id }}')">
                                    {{ __('Setujui') }}
                                </button>
                                <button onclick="confirm('Apakah Anda yakin ingin menolak dan menghapus akun organisasi ini?') || event.stopImmediatePropagation()" wire:click="reject('{{ $profile->user_id }}')">
                                    {{ __('Tolak & Hapus') }}
                                </button>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endif
</div>
