<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="//unpkg.com/alpinejs" defer></script>

    <x-slot name="header">
        <h2 class="font-bold text-xl text-gray-800 leading-tight">
            {{ __('Statistik Pengunjung') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="visitorAnalytics()" x-init="initCharts()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h3 class="font-bold text-lg text-gray-800">Analisa Trafik Website</h3>
                    <p class="text-sm text-gray-500">Pantau perkembangan pengunjung secara real-time.</p>
                </div>
                
                <div class="flex items-center gap-2 bg-gray-50 p-2 rounded-lg border border-gray-200">
                    <span class="text-xs font-bold text-gray-500 uppercase px-2">Periode:</span>
                    <input type="date" x-model="startDate" @change="fetchData()" class="border-none bg-transparent text-sm focus:ring-0 p-0 text-gray-700 font-bold">
                    <span class="text-gray-400">-</span>
                    <input type="date" x-model="endDate" @change="fetchData()" class="border-none bg-transparent text-sm focus:ring-0 p-0 text-gray-700 font-bold">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-6 rounded-xl shadow-lg text-white relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="bg-blue-500/30 p-1.5 rounded-lg"><i class="fa-solid fa-users-viewfinder"></i></span>
                            <span class="text-sm font-medium text-blue-100">Sedang Online</span>
                        </div>
                        <div class="flex items-baseline gap-3">
                            <h3 class="text-5xl font-extrabold" x-text="activeVisitors">0</h3>
                            <span class="flex h-3 w-3 relative top-[-10px]">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-400"></span>
                            </span>
                        </div>
                        <p class="text-xs text-blue-200 mt-2">User aktif dalam 5 menit terakhir</p>
                    </div>
                    <i class="fa-solid fa-globe absolute -right-6 -bottom-6 text-9xl text-white opacity-10"></i>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <span class="bg-orange-100 text-orange-600 p-1.5 rounded-lg"><i class="fa-solid fa-chart-simple"></i></span>
                            <span class="text-sm font-bold text-gray-600">Total Kunjungan</span>
                        </div>
                        <span class="text-xs text-gray-400 bg-gray-50 px-2 py-1 rounded" x-text="formatDate(startDate) + ' - ' + formatDate(endDate)"></span>
                    </div>
                    <h3 class="text-4xl font-extrabold text-gray-800" x-text="totalVisitors">0</h3>
                    <p class="text-sm text-gray-500 mt-2">Jumlah kunjungan unik berdasarkan rentang tanggal.</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-gray-800 text-lg">Tren Kunjungan Harian</h3>
                    <button @click="fetchData()" class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        <i class="fa-solid fa-rotate-right"></i> Refresh Data
                    </button>
                </div>
                <div class="relative h-96 w-full">
                    <canvas id="realtimeChart"></canvas>
                </div>
            </div>

        </div>
    </div>

    <script>
        function visitorAnalytics() {
            return {
                startDate: new Date(new Date().setDate(new Date().getDate() - 6)).toISOString().split('T')[0],
                endDate: new Date().toISOString().split('T')[0],
                activeVisitors: 0,
                totalVisitors: 0,
                chartInstance: null,

                initCharts() {
                    const ctx = document.getElementById('realtimeChart').getContext('2d');
                    this.chartInstance = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: [],
                            datasets: [{
                                label: 'Pengunjung',
                                data: [],
                                borderColor: '#4F46E5', // Warna Indigo
                                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                borderWidth: 3,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: '#4F46E5',
                                pointRadius: 5,
                                pointHoverRadius: 7
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: '#f3f4f6' } },
                                x: { grid: { display: false } }
                            },
                            plugins: { legend: { display: false } }
                        }
                    });

                    this.fetchData();
                    setInterval(() => { this.fetchData(); }, 5000); 
                },

                fetchData() {
                    fetch(`/api/visitor-stats?start_date=${this.startDate}&end_date=${this.endDate}`)
                        .then(response => response.json())
                        .then(data => {
                            this.activeVisitors = data.active;
                            this.totalVisitors = data.total;
                            this.chartInstance.data.labels = data.chart.labels;
                            this.chartInstance.data.datasets[0].data = data.chart.data;
                            this.chartInstance.update();
                        });
                },

                formatDate(dateString) {
                    const options = { day: 'numeric', month: 'short' };
                    return new Date(dateString).toLocaleDateString('id-ID', options);
                }
            }
        }
    </script>
</x-app-layout>