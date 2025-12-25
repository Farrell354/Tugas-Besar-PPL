<?php

namespace App\Http\Controllers;

use App\Models\TambalBan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class TambalBanController extends Controller
{
    public function index(Request $request): \Illuminate\View\View
    {
        $query = TambalBan::query();

        if ($request->has('search') && $request->search != null) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_bengkel', 'LIKE', "%{$search}%")
                  ->orWhere('alamat', 'LIKE', "%{$search}%");
            });
        }

        $data = $query->latest()->paginate(10)->withQueryString();

        $users = \App\Models\User::where('role', 'user')->latest()->paginate(5);

        return view('admin.dashboard', compact('data', 'users'));
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
}