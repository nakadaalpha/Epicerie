<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use Carbon\Carbon;          // <--- PENTING BUAT TANGGAL
use Illuminate\Support\Facades\DB; // <--- PENTING BUAT QUERY MANUAL

class TransaksiController extends Controller
{
    // 1. HALAMAN DAFTAR PESANAN (OPERASIONAL)
    public function index()
    {
        $transaksi = Transaksi::with(['user', 'detailTransaksi.produk'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('transaksi.index', compact('transaksi'));
    }

    // 2. LOGIKA UBAH STATUS
    public function updateStatus(Request $request, $id)
    {
        $trx = Transaksi::findOrFail($id);
        $statusBaru = $request->status;

        if ($statusBaru == 'Dikirim' && $trx->status == 'Dikemas') {
            $trx->status = 'Dikirim';
            
            // Simpan ID user login (Karyawan) ke kolom 'id_karyawan'
            $trx->id_karyawan = auth()->id(); 
        } 
        elseif ($statusBaru == 'Selesai' && $trx->status == 'Dikirim') {
            $trx->status = 'Selesai';
        }

        $trx->save();
        return back()->with('success', 'Status pesanan diperbarui!');
    }

    // 3. HALAMAN LAPORAN PENJUALAN (ANALYTICS)
    public function laporan()
    {
        // Ambil data penjualan 7 hari terakhir yang statusnya 'Selesai'
        // Kita kelompokkan per tanggal biar bisa jadi grafik
        $laporan = Transaksi::selectRaw('DATE(created_at) as tanggal, SUM(total_bayar) as total')
            ->where('status', 'Selesai') 
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        // Total Omzet Keseluruhan (Semua Waktu)
        $totalOmzet = Transaksi::where('status', 'Selesai')->sum('total_bayar');

        // Total Transaksi Berhasil
        $totalTransaksi = Transaksi::where('status', 'Selesai')->count();

        return view('laporan.index', compact('laporan', 'totalOmzet', 'totalTransaksi'));
    }
}