<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Visitor;
use Carbon\Carbon;

class TrackVisitor
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $date = Carbon::now()->toDateString();
        
        // Cari data pengunjung hari ini berdasarkan IP
        $visitor = Visitor::where('ip_address', $ip)->where('visit_date', $date)->first();

        if ($visitor) {
            // Jika sudah ada, update jam terakhir aktif (untuk fitur 'Sedang Aktif')
            $visitor->update(['last_activity' => Carbon::now()]);
        } else {
            // Jika belum ada, buat baru
            Visitor::create([
                'ip_address' => $ip,
                'visit_date' => $date,
                'user_agent' => $request->userAgent(),
                'last_activity' => Carbon::now(),
            ]);
        }

        return $next($request);
    }
}