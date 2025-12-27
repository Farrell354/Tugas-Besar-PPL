<?php

namespace App\Http\Controllers;

use App\Models\TambalBan;
use App\Models\User;
use App\Models\Visitor; // Import Model Visitor
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class TambalBanController extends Controller
{
    // 1. Dashboard Admin (Hanya Muat Data Bengkel & User, Grafik via AJAX)
    public function index(Request $request): \Illuminate\View\View
    {
        $query = TambalBan::query();

        // Fitur Pencarian Bengkel
        if ($request->has('search') && $request->search != null) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_bengkel', 'LIKE', "%{$search}%")
                    ->orWhere('alamat', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->latest()->paginate(10); // Gunakan Paginate agar rapi
        $users = User::where('role', 'user')->latest()->paginate(5);

        // CATATAN: Data grafik ($visitors, $chartLabels) DIHAPUS dari sini
        // Karena sekarang diambil via AJAX agar loading dashboard cepat.

        return view('admin.dashboard', compact('data', 'users'));
    }

    // 2. API untuk Statistik Visitor (Realtime & Filter)
    public function getVisitorStats(Request $request)
    {
        // A. Filter Tanggal (Default: 7 Hari Terakhir)
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subDays(6);
        $endDate   = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();

        // B. Hitung Pengunjung Aktif (User yang buka web dalam 5 menit terakhir)
        // Menggunakan kolom 'last_activity' yang baru kita buat
        $activeVisitors = Visitor::where('last_activity', '>=', Carbon::now()->subMinutes(5))->count();

        // C. Hitung Total Pengunjung (Sesuai Rentang Tanggal)
        $totalVisitors = Visitor::whereBetween('visit_date', [$startDate->toDateString(), $endDate->toDateString()])->count();

        // D. Data Grafik (Group by Date)
        $chartDataRaw = Visitor::selectRaw('visit_date, count(*) as total')
            ->whereBetween('visit_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->groupBy('visit_date')
            ->orderBy('visit_date', 'asc')
            ->get();

        // Format Data agar Tanggal Kosong tetap muncul (Nilai 0)
        $labels = [];
        $data = [];
        $period = \Carbon\CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d'); // Format DB
            $displayDate = $date->format('d M');     // Format Tampilan (27 Dec)

            // Cari data di DB, kalau tidak ada isi 0
            $record = $chartDataRaw->firstWhere('visit_date', $formattedDate);

            $labels[] = $displayDate;
            $data[] = $record ? $record->total : 0;
        }

        return response()->json([
            'active' => $activeVisitors, // Mengirim data aktif
            'total' => $totalVisitors,   // Mengirim total sesuai filter
            'chart' => [
                'labels' => $labels,
                'data' => $data
            ]
        ]);
    }

    public function create()
    {
        $owners = User::where('role', 'owner')->get();
        return view('admin.create', compact('owners'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_bengkel' => 'required|string|max:255',
            'nomer_telepon' => 'required|numeric',
            'alamat' => 'nullable|string',
            'latitude' => 'required',
            'longitude' => 'required',
            'jam_buka' => 'required',
            'jam_tutup' => 'required',
            'kategori' => 'required',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $data = $request->all();

        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('bengkel_images', 'public');
            $data['gambar'] = $path;
        }

        TambalBan::create($data);

        return redirect()->route('dashboard')->with('success', 'Lokasi berhasil ditambahkan');
    }

    public function edit($id)
    {
        $tambalBan = TambalBan::findOrFail($id);
        $owners = User::where('role', 'owner')->get();
        return view('admin.edit', compact('tambalBan', 'owners'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_bengkel' => 'required|string|max:255',
            'nomer_telepon' => 'required|numeric',
            'alamat' => 'nullable|string',
            'latitude' => 'required',
            'longitude' => 'required',
            'jam_buka' => 'required',
            'jam_tutup' => 'required',
            'kategori' => 'required',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $tambalBan = TambalBan::findOrFail($id);
        $data = $request->all();

        if ($request->hasFile('gambar')) {
            if ($tambalBan->gambar && Storage::disk('public')->exists($tambalBan->gambar)) {
                Storage::disk('public')->delete($tambalBan->gambar);
            }
            $path = $request->file('gambar')->store('bengkel_images', 'public');
            $data['gambar'] = $path;
        }

        $tambalBan->update($data);

        return redirect()->route('dashboard')->with('success', 'Data berhasil diperbarui');
    }

    public function destroy($id)
    {
        $tambalBan = TambalBan::findOrFail($id);
        if ($tambalBan->gambar && Storage::disk('public')->exists($tambalBan->gambar)) {
            Storage::disk('public')->delete($tambalBan->gambar);
        }
        $tambalBan->delete();
        return redirect()->route('dashboard')->with('success', 'Lokasi berhasil dihapus');
    }

    public function liveMap()
    {
        $lokasi = TambalBan::all();
        return view('admin.live-map', compact('lokasi'));
    }

    public function userDashboard()
    {
        $lokasi = TambalBan::all();
        return view('dashboard', compact('lokasi'));
    }

    public function editOwner()
    {
        $bengkel = TambalBan::where('user_id', Auth::id())->first();

        if (!$bengkel) {
            return redirect()->route('owner.dashboard')->with('error', 'Anda belum memiliki profil bengkel. Hubungi Admin.');
        }

        return view('owner.bengkel.edit', compact('bengkel'));
    }

    public function updateOwner(Request $request, $id)
    {
        $bengkel = TambalBan::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'nama_bengkel' => 'required|string|max:255',
            'nomer_telepon' => 'required|numeric',
            'alamat' => 'required|string',
            'deskripsi' => 'nullable|string',
            'jam_buka' => 'nullable',
            'jam_tutup' => 'nullable',
            'latitude' => 'required',
            'longitude' => 'required',
            'harga_motor_dekat' => 'required|numeric|min:0',
            'harga_motor_jauh' => 'required|numeric|min:0',
            'harga_mobil_dekat' => 'required|numeric|min:0',
            'harga_mobil_jauh' => 'required|numeric|min:0',
        ]);

        $data = $request->all();
        $bengkel->update($data);

        return redirect()->back()->with('success', 'Profil dan Tarif Bengkel berhasil diperbarui!');
    }

    public function statsPage()
    {
        return view('admin.visitors.index');
    }
}
