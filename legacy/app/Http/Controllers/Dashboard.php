<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Dashboard extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        // 1. RINGKASAN ATAS (Quick Stats)
        // Omzet Hari Ini (Hanya status 'selesai')
        $omzetHariIni = Transaksi::whereDate('created_at', $today)
            ->where('status', 'selesai')
            ->sum('total_bayar');

        // Jumlah Transaksi Hari Ini (Semua status)
        $totalTransaksiHariIni = Transaksi::whereDate('created_at', $today)->count();

        // Total Produk & User
        $totalProduk = Produk::count();
        $totalUser = User::where('role', '!=', 'Admin')->count();


        // 2. LOGIC: STOK HAMPIR HABIS
        // Mengambil produk dengan stok <= 15
        $stokHampirHabis = Produk::where('stok', '<=', 15)
            ->orderBy('stok', 'asc')
            ->take(5)
            ->get();


        // 3. LOGIC: PRODUK TERLARIS (Top 5)
        // Join ke detail_transaksi untuk hitung jumlah terjual
        $produkTerlaris = Produk::select('produk.nama_produk', 'produk.stok', DB::raw('COALESCE(SUM(detail_transaksi.jumlah), 0) as total_terjual'))
            ->leftJoin('detail_transaksi', 'produk.id_produk', '=', 'detail_transaksi.id_produk')
            ->groupBy('produk.id_produk', 'produk.nama_produk', 'produk.stok')
            ->orderByDesc('total_terjual')
            ->take(5)
            ->get();


        // 4. DATA GRAFIK (7 HARI TERAKHIR)
        $chartLabels = [];
        $chartData = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartLabels[] = $date->format('D, d M'); // Label: Senin, 01 Jan

            // Hitung total bayar per tanggal tersebut (hanya yang selesai)
            $total = Transaksi::whereDate('created_at', $date->format('Y-m-d'))
                ->where('status', 'selesai')
                ->sum('total_bayar');

            $chartData[] = $total;
        }

        // 5. TRANSAKSI TERBARU (Untuk Card Kanan Dashboard)
        // Load relasi agar data di modal lengkap (user, kurir, produk, alamat)
        $transaksiTerbaru = Transaksi::with(['user', 'kurir', 'detailTransaksi.produk', 'alamat'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Kirim semua data ke View
        return view('dashboard.index', compact(
            'omzetHariIni',
            'totalTransaksiHariIni',
            'totalProduk',
            'totalUser',
            'stokHampirHabis',
            'produkTerlaris',
            'chartLabels',
            'chartData',
            'transaksiTerbaru' // Data baru untuk list di kanan
        ));
    }
}
