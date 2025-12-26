<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TambalBan;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    // 1. Tampilkan Formulir Pemesanan
    public function create($id)
    {
        $bengkel = TambalBan::findOrFail($id);
        return view('booking.create', compact('bengkel'));
    }

    // 2. Simpan Pesanan ke Database
    public function store(Request $request)
    {
        $request->validate([
            'tambal_ban_id' => 'required',
            'nama_pemesan' => 'required',
            'nomer_telepon' => 'required',
            'alamat_lengkap' => 'required',
            'jenis_kendaraan' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        Order::create([
            'user_id' => Auth::id(),
            'tambal_ban_id' => $request->tambal_ban_id,
            'nama_pemesan' => $request->nama_pemesan,
            'nomer_telepon' => $request->nomer_telepon,
            'alamat_lengkap' => $request->alamat_lengkap,
            'jenis_kendaraan' => $request->jenis_kendaraan,
            'keluhan' => $request->keluhan,
            'status' => 'pending',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        return redirect()->route('booking.history')->with('success', 'Pesanan berhasil dibuat! Mohon tunggu konfirmasi bengkel.');
    }

    // 3. Lihat Riwayat Pesanan User
    public function history()
    {
        $orders = Order::where('user_id', Auth::id())
                        ->with('tambalBan')
                        ->latest()
                        ->get();

        return view('booking.history', compact('orders'));
    }

    public function show($id)
    {
        // Ambil data order beserta data bengkelnya
        $order = Order::with('tambalBan')->findOrFail($id);

        // Keamanan: Pastikan yang melihat adalah pemilik pesanan
        if ($order->user_id !== Auth::id()) {
            abort(403, 'Akses Ditolak');
        }

        return view('booking.show', compact('order'));
    }

    public function adminIndex()
    {
        $orders = Order::with(['user', 'tambalBan'])->latest()->get();
        return view('admin.orders.index', compact('orders'));
    }

    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        if ($request->has('action')) {
            // Logic Tombol Cepat (Owner)
            if ($request->action == 'accept') {
                $order->update(['status' => 'proses']);
            } elseif ($request->action == 'reject') {
                $order->update(['status' => 'batal', 'alasan_batal' => $request->alasan ?? 'Ditolak Bengkel']);
            } elseif ($request->action == 'finish') {
                $order->update(['status' => 'selesai']);
                // Jika COD, otomatis set Lunas saat selesai
                if ($order->metode_pembayaran == 'cod') {
                    $order->update(['payment_status' => 'paid']);
                }
            }
        } else {
            // Logic Dropdown Admin
            $data = ['status' => $request->status];
            if ($request->status == 'batal' && $request->alasan_batal) {
                $data['alasan_batal'] = $request->alasan_batal;
            }
            $order->update($data);
        }

        return back()->with('success', 'Status pesanan diperbarui.');
    }

}
