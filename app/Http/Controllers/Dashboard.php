<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Dashboard extends Controller
{
    public function index()
    {
        // 1. Logic: getOmzetHariIni()
        // Mengambil total 'total_bayar' dari transaksi hari ini
        $omzetHariIni = Transaksi::whereDate('tanggal_transaksi', Carbon::today())
                        ->sum('total_bayar');

        // 2. Logic: getStokHampirHabis()
        // Mengambil produk dengan stok kurang dari 15 (sebagai ambang batas)
        $stokHampirHabis = Produk::where('stok', '<=', 15)
                            ->orderBy('stok', 'asc')
                            ->take(5)
                            ->get();

        // 3. Logic: produkTerlaris
        // Menggabungkan tabel detail_transaksi untuk menghitung jumlah item terjual
        $produkTerlaris = DetailTransaksi::select('id_produk', DB::raw('SUM(jumlah) as total_terjual'))
                            ->with('produk') // Load relasi nama produk
                            ->groupBy('id_produk')
                            ->orderByDesc('total_terjual')
                            ->take(4) // Ambil 4 teratas sesuai desain
                            ->get();

        // 4. Data untuk Grafik (Laporan Penjualan Mingguan/Bulanan)
        // Contoh: Data 6 bulan terakhir
        $chartData = Transaksi::select(
                            DB::raw('SUM(total_bayar) as total'), 
                            DB::raw("DATE_FORMAT(tanggal_transaksi, '%Y-%m') as bulan")
                        )
                        ->groupBy('bulan')
                        ->orderBy('bulan', 'asc')
                        ->take(6)
                        ->get();

        // Kirim semua data ke View 'dashboard'
        return view('dashboard.index', compact(
            'omzetHariIni', 
            'stokHampirHabis', 
            'produkTerlaris', 
            'chartData'
        ));
    }
}