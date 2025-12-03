<x-app-layout>
    <x-slot name="header"><h2 class="font-bold text-xl text-gray-800">Form Pemesanan Jasa</h2></x-slot>
    <div class="py-12"><div class="max-w-2xl mx-auto sm:px-6 lg:px-8"><div class="bg-white p-8 rounded-xl border border-gray-100">
        <h3 class="text-lg font-bold text-blue-600 mb-6">{{ $bengkel->nama_bengkel }}</h3>
        <form action="{{ route('booking.store') }}" method="POST" class="space-y-5" id="bookingForm">
            @csrf
            <input type="hidden" name="tambal_ban_id" value="{{ $bengkel->id }}">
            <input type="hidden" name="latitude" id="latitude"><input type="hidden" name="longitude" id="longitude">

            <div><label class="text-sm font-medium text-gray-700">Lokasi Anda (Wajib)</label><div id="mapPicker" class="w-full h-48 rounded-lg border mt-2"></div><button type="button" onclick="getLocation()" class="mt-2 text-xs bg-blue-50 text-blue-600 px-3 py-1.5 rounded-lg border border-blue-200 font-bold">Ambil Lokasi Saya</button></div>
            <div class="grid grid-cols-2 gap-4">
                <div><label class="text-sm font-medium">Nama</label><input type="text" name="nama_pemesan" value="{{ Auth::user()->name }}" class="w-full rounded-lg border-gray-300" required></div>
                <div><label class="text-sm font-medium">WhatsApp</label><input type="number" name="nomer_telepon" class="w-full rounded-lg border-gray-300" required></div>
            </div>
            <div><label class="text-sm font-medium">Alamat Lengkap</label><textarea name="alamat_lengkap" rows="2" class="w-full rounded-lg border-gray-300" required></textarea></div>
            <div><label class="text-sm font-medium">Kendaraan</label><div class="flex gap-4 mt-1"><label><input type="radio" name="jenis_kendaraan" value="motor" checked> Motor</label><label><input type="radio" name="jenis_kendaraan" value="mobil"> Mobil</label></div></div>
            <div><label class="text-sm font-medium">Keluhan</label><input type="text" name="keluhan" class="w-full rounded-lg border-gray-300"></div>

            <div class="mt-4 p-4 border rounded-xl bg-blue-50"><label class="flex items-center gap-2"><input type="radio" checked class="text-blue-600"><span class="font-bold text-gray-800">Tunai (COD)</span></label></div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl mt-4">Buat Pesanan</button>
        </form>
    </div></div></div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" /><script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var map = L.map('mapPicker').setView([-7.4478, 112.7183], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
        var marker;
        function updatePosition(lat, lng) {
            document.getElementById('latitude').value = lat; document.getElementById('longitude').value = lng;
            if (marker) marker.setLatLng([lat, lng]); else marker = L.marker([lat, lng], {draggable: true}).addTo(map);
        }
        function getLocation() {
            navigator.geolocation.getCurrentPosition((pos) => { updatePosition(pos.coords.latitude, pos.coords.longitude); map.setView([pos.coords.latitude, pos.coords.longitude], 17); });
        }
        getLocation();
        map.on('click', function(e) { updatePosition(e.latlng.lat, e.latlng.lng); });
    </script>
</x-app-layout>
