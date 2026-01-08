<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User; // Tambahkan Model User
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Dashboard extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // 1. RINGKASAN ATAS (Quick Stats)
        // Omzet Hari Ini
        $omzetHariIni = Transaksi::whereDate('tanggal_transaksi', $today)
            ->where('status', 'selesai') // Pastikan hanya yg selesai
            ->sum('total_bayar');

        // Jumlah Transaksi Hari Ini
        $totalTransaksiHariIni = Transaksi::whereDate('tanggal_transaksi', $today)->count();

        // Total Produk & User
        $totalProduk = Produk::count();
        // Asumsi ada kolom 'role', hitung yg bukan admin (pelanggan)
        $totalUser = User::where('role', '!=', 'Admin')->count();


        // 2. LOGIC: STOK HAMPIR HABIS
        // Mengambil produk dengan stok kurang dari 15
        $stokHampirHabis = Produk::where('stok', '<=', 15)
            ->orderBy('stok', 'asc')
            ->take(5)
            ->get();


        // 3. LOGIC: PRODUK TERLARIS (Top 5)
        // Kita gunakan Join agar bisa langsung dapat nama_produk dan total_terjual dalam satu object
        $produkTerlaris = Produk::select('produk.nama_produk', 'produk.stok', DB::raw('COALESCE(SUM(detail_transaksi.jumlah), 0) as total_terjual'))
            ->leftJoin('detail_transaksi', 'produk.id_produk', '=', 'detail_transaksi.id_produk')
            ->groupBy('produk.id_produk', 'produk.nama_produk', 'produk.stok')
            ->orderByDesc('total_terjual')
            ->take(5)
            ->get();


        // 4. DATA GRAFIK (7 HARI TERAKHIR)
        // Kita loop 7 hari ke belakang agar grafiknya detail per hari
        $chartLabels = [];
        $chartData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartLabels[] = $date->format('D, d M'); // Label: Senin, 01 Jan

            $total = Transaksi::whereDate('tanggal_transaksi', $date->format('Y-m-d'))
                ->where('status', 'selesai')
                ->sum('total_bayar');

            $chartData[] = $total;
        }

        // Kirim semua data ke View
        return view('dashboard.index', compact(
            'omzetHariIni',
            'totalTransaksiHariIni',
            'totalProduk',
            'totalUser',
            'stokHampirHabis',
            'produkTerlaris',
            'chartLabels', // Array label tanggal
            'chartData'    // Array nominal omzet
        ));
    }
}
