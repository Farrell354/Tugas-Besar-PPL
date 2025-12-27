<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VisitorController extends Controller
{
    // API untuk mengambil data statistik (JSON)
    public function getStats(Request $request)
    {
        // 1. Ambil Filter Tanggal (Default: 7 Hari Terakhir)
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subDays(6);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        // 2. Hitung Pengunjung Aktif (Yang akses dalam 5 menit terakhir)
        // Kita hitung global (hari ini) agar realtime saat ini
        $activeVisitors = Visitor::where('last_activity', '>=', Carbon::now()->subMinutes(5))->count();

        // 3. Hitung Total Pengunjung (Sesuai Filter Tanggal)
        $totalVisitors = Visitor::whereBetween('visit_date', [$startDate->toDateString(), $endDate->toDateString()])->count();

        // 4. Data Grafik (Group by Date)
        $chartDataRaw = Visitor::selectRaw('visit_date, count(*) as total')
            ->whereBetween('visit_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('visit_date')
            ->orderBy('visit_date', 'asc')
            ->get();

        // Format data untuk Chart.js
        $labels = [];
        $data = [];

        // Loop untuk memastikan tanggal yang kosong tetap muncul (nilai 0)
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');
            $displayDate = $date->format('d M');
            
            // Cari apakah ada data di tanggal ini
            $record = $chartDataRaw->firstWhere('visit_date', $formattedDate);
            
            $labels[] = $displayDate;
            $data[] = $record ? $record->total : 0;
        }

        return response()->json([
            'active' => $activeVisitors,
            'total' => $totalVisitors,
            'chart' => [
                'labels' => $labels,
                'data' => $data
            ]
        ]);
    }

    public function statsPage()
    {
        return view('admin.visitors.index');
    }
}