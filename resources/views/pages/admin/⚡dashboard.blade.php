<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Vacancy;
use App\Models\Skill;
use App\Models\VacancyApplication;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    // Search & Filter state
    public string $search = '';
    public string $roleFilter = 'all'; // all, student, organization, admin
    public bool $onlyBlocked = false;

    // Block reason inputs indexed by user_id
    public array $blockReasons = [];

    public function mount(): void
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }
    }

    public function blockUser(string $userId): void
    {
        $this->validate([
            'blockReasons.' . $userId => ['required', 'string', 'max:255'],
        ], [
            'blockReasons.' . $userId . '.required' => 'Alasan pemblokiran wajib diisi.',
        ]);

        $user = User::findOrFail($userId);
        $user->update([
            'blocked_at' => now(),
            'block_reason' => $this->blockReasons[$userId],
        ]);

        unset($this->blockReasons[$userId]);
        session()->flash('status', "Pengguna \"{$user->name}\" berhasil dinonaktifkan.");
    }

    public function unblockUser(string $userId): void
    {
        $user = User::findOrFail($userId);
        $user->update([
            'blocked_at' => null,
            'block_reason' => null,
        ]);

        session()->flash('status', "Akun pengguna \"{$user->name}\" kembali diaktifkan.");
    }

    public function deleteEvent(string $eventId): void
    {
        $event = Event::findOrFail($eventId);
        $name = $event->event_name;
        $event->delete();

        session()->flash('status', "Event \"{$name}\" beserta lowongan terkait berhasil dihapus.");
    }

    public function deleteVacancy(string $vacancyId): void
    {
        $vacancy = Vacancy::findOrFail($vacancyId);
        $division = $vacancy->division;
        $vacancy->delete();

        session()->flash('status', "Lowongan divisi \"{$division}\" berhasil dihapus.");
    }
}; ?>

<div>
    <h1>{{ __('Dasbor Statistik & Manajemen Akun Admin') }}</h1>

    @if (session('status'))
        <div style="border: 1px solid green; padding: 10px; margin-bottom: 20px; background-color: #e6ffe6; color: green;">
            {{ session('status') }}
        </div>
    @endif

    <!-- Statistics Section -->
    <div style="margin-bottom: 30px;">
        <h2>{{ __('Statistik Platform') }}</h2>
        <ul>
            <li><strong>{{ __('Total Mahasiswa: ') }}</strong>{{ User::where('role', 'student')->count() }}</li>
            <li><strong>{{ __('Total Organisasi: ') }}</strong>{{ User::where('role', 'organization')->count() }}</li>
            <li><strong>{{ __('Total Event: ') }}</strong>{{ Event::count() }}</li>
            <li><strong>{{ __('Total Lowongan: ') }}</strong>{{ Vacancy::count() }}</li>
        </ul>

        <h3>{{ __('Status Lamaran Lowongan') }}</h3>
        <ul>
            <li><strong>{{ __('Pending: ') }}</strong>{{ VacancyApplication::where('status', 'pending')->count() }}</li>
            <li><strong>{{ __('Interviewing: ') }}</strong>{{ VacancyApplication::where('status', 'interviewing')->count() }}</li>
            <li><strong>{{ __('Diterima (Accepted): ') }}</strong>{{ VacancyApplication::where('status', 'accepted')->count() }}</li>
            <li><strong>{{ __('Ditolak (Rejected): ') }}</strong>{{ VacancyApplication::where('status', 'rejected')->count() }}</li>
        </ul>

        <h3>{{ __('Keahlian Paling Banyak Dibutuhkan (Top 5 Required Skills)') }}</h3>
        <ol>
            @foreach (Skill::withCount('vacancies')->orderByDesc('vacancies_count')->take(5)->get() as $sk)
                <li>{{ $sk->skill_name }} (Dibutuhkan di {{ $sk->vacancies_count }} lowongan)</li>
            @endforeach
        </ol>
    </div>

    <hr />

    <!-- User Management Section -->
    <div style="margin-top: 30px; margin-bottom: 30px;">
        <h2>{{ __('Manajemen Akun Pengguna') }}</h2>

        <!-- Search and Filters Form -->
        <div style="margin-bottom: 20px;">
            <input type="text" wire:model.live="search" placeholder="Cari berdasarkan nama, email, NIM, atau tingkat..." style="width: 300px; padding: 5px;" />
            
            <select wire:model.live="roleFilter" style="padding: 5px; margin-left: 10px;">
                <option value="all">{{ __('Semua Peran') }}</option>
                <option value="student">{{ __('Mahasiswa') }}</option>
                <option value="organization">{{ __('Organisasi') }}</option>
                <option value="admin">{{ __('Admin') }}</option>
            </select>

            <label style="margin-left: 15px;">
                <input type="checkbox" wire:model.live="onlyBlocked" />
                {{ __('Hanya Pengguna Diblokir') }}
            </label>
        </div>

        @php
            $query = User::query()
                ->leftJoin('student_profile', 'users.user_id', '=', 'student_profile.user_id')
                ->leftJoin('organization_profile', 'users.user_id', '=', 'organization_profile.user_id')
                ->select('users.*', 'student_profile.student_id', 'organization_profile.organization_level')
                ->where('users.user_id', '!=', auth()->id());

            if ($search) {
                $searchTerm = '%' . $search . '%';
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('users.name', 'like', $searchTerm)
                      ->orWhere('users.email', 'like', $searchTerm)
                      ->orWhere('student_profile.student_id', 'like', $searchTerm)
                      ->orWhere('organization_profile.organization_level', 'like', $searchTerm);
                });
            }

            if ($roleFilter !== 'all') {
                $query->where('users.role', $roleFilter);
            }

            if ($onlyBlocked) {
                $query->whereNotNull('users.blocked_at');
            }

            $usersList = $query->orderBy('users.created_at', 'desc')->get();
        @endphp

        @if ($usersList->isEmpty())
            <p><em>{{ __('Tidak ada pengguna yang cocok dengan kriteria pencarian.') }}</em></p>
        @else
            <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; text-align: left;">
                <thead>
                    <tr>
                        <th>{{ __('Nama') }}</th>
                        <th>{{ __('Email') }}</th>
                        <th>{{ __('Peran') }}</th>
                        <th>{{ __('Detail Profil') }}</th>
                        <th>{{ __('Tanggal Join') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Tindakan Pemblokiran') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($usersList as $u)
                        <tr>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ ucfirst($u->role) }}</td>
                            <td>
                                @if ($u->role === 'student')
                                    NIM: {{ $u->student_id ?: '-' }}
                                @elseif ($u->role === 'organization')
                                    Tingkat: {{ ucfirst(str_replace('_', ' ', $u->organization_level)) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $u->created_at->format('d M Y') }}</td>
                            <td>
                                @if ($u->blocked_at)
                                    <span style="color: red;"><strong>{{ __('Diblokir') }}</strong></span>
                                    <br /><small>Alasan: {{ $u->block_reason }}</small>
                                @else
                                    <span style="color: green;">{{ __('Aktif') }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($u->blocked_at)
                                    <button onclick="confirm('Buka blokir pengguna ini?') || event.stopImmediatePropagation()" wire:click="unblockUser('{{ $u->user_id }}')">
                                        {{ __('Aktifkan Kembali') }}
                                    </button>
                                @else
                                    <form wire:submit.prevent="blockUser('{{ $u->user_id }}')" style="display: inline-flex; gap: 5px;">
                                        <input type="text" wire:model="blockReasons.{{ $u->user_id }}" placeholder="Tulis alasan..." style="padding: 2px;" required />
                                        <button type="submit">{{ __('Blokir') }}</button>
                                    </form>
                                    @error('blockReasons.' . $u->user_id)
                                        <br /><span style="color: red; font-size: 11px;">{{ $message }}</span>
                                    @enderror
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <hr />

    <!-- Events Auditing Section -->
    <div style="margin-top: 30px; margin-bottom: 30px;">
        <h2>{{ __('Audit Event Kepanitiaan') }}</h2>

        @php
            $eventsList = Event::orderBy('created_at', 'desc')->get();
        @endphp

        @if ($eventsList->isEmpty())
            <p><em>{{ __('Tidak ada event yang terdaftar di platform.') }}</em></p>
        @else
            <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; text-align: left; margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th>{{ __('Nama Event') }}</th>
                        <th>{{ __('Tanggal Pelaksanaan') }}</th>
                        <th>{{ __('Sifat Event') }}</th>
                        <th>{{ __('Tindakan') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($eventsList as $e)
                        <tr>
                            <td>{{ $e->event_name }}</td>
                            <td>{{ $e->event_date->format('d M Y') }}</td>
                            <td>{{ $e->is_official ? __('Resmi Kampus') : __('Informal / Mahasiswa') }}</td>
                            <td>
                                <button onclick="confirm('Apakah Anda yakin ingin menghapus event ini secara permanen? Semua lowongan dan berkas lamaran terkait akan ikut terhapus.') || event.stopImmediatePropagation()" wire:click="deleteEvent('{{ $e->event_id }}')">
                                    {{ __('Hapus Event') }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <hr />

    <!-- Vacancies Auditing Section -->
    <div style="margin-top: 30px; margin-bottom: 30px;">
        <h2>{{ __('Audit Lowongan Divisi') }}</h2>

        @php
            $vacanciesList = Vacancy::with('event')->orderBy('created_at', 'desc')->get();
        @endphp

        @if ($vacanciesList->isEmpty())
            <p><em>{{ __('Tidak ada lowongan divisi yang terbit saat ini.') }}</em></p>
        @else
            <table border="1" cellpadding="8" cellspacing="0" style="width: 100%; text-align: left;">
                <thead>
                    <tr>
                        <th>{{ __('Event') }}</th>
                        <th>{{ __('Divisi Kepanitiaan') }}</th>
                        <th>{{ __('Status Lowongan') }}</th>
                        <th>{{ __('Tindakan') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vacanciesList as $v)
                        <tr>
                            <td>{{ $v->event ? $v->event->event_name : '-' }}</td>
                            <td>{{ $v->division }}</td>
                            <td>{{ $v->status }}</td>
                            <td>
                                <button onclick="confirm('Apakah Anda yakin ingin menghapus lowongan divisi ini?') || event.stopImmediatePropagation()" wire:click="deleteVacancy('{{ $v->vacancy_id }}')">
                                    {{ __('Hapus Lowongan') }}
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
