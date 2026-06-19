<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckVerifiedOrganization
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        if ($user->role !== 'organization') {
            abort(403, 'Hanya akun organisasi yang dapat mengakses halaman ini.');
        }

        $profile = $user->organizationProfile;

        if (!$profile || $profile->verification_status !== 'verified') {
            $statusMsg = 'Akun organisasi Anda belum diverifikasi oleh admin.';
            if ($profile && $profile->verification_status === 'rejected') {
                $statusMsg = 'Akun organisasi Anda ditolak oleh admin.';
            }
            return redirect()->route('dashboard')->with('status', $statusMsg);
        }

        return $next($request);
    }
}
