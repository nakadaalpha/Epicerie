<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index()
    {
        $transaksi = Transaksi::with(['user', 'kurir', 'detailTransaksi.produk']) // Tambah 'kurir'
            ->orderBy('created_at', 'desc')
            ->get();

        return view('transaksi.index', compact('transaksi'));
    }

    public function updateStatus(Request $request, $id)
    {
        $trx = Transaksi::findOrFail($id);
        $statusBaru = $request->status;

        // UPDATE: Izinkan jika status sekarang 'Dikemas' ATAU 'diproses'
        if ($statusBaru == 'Dikirim' && in_array($trx->status, ['Dikemas', 'diproses'])) {
            $trx->status = 'Dikirim';
            $trx->id_karyawan = auth()->id(); 
        } 
        elseif ($statusBaru == 'Selesai' && $trx->status == 'Dikirim') {
            $trx->status = 'Selesai';
        }

        $trx->save();
        return back()->with('success', 'Status pesanan diperbarui!');
    }

    public function laporan()
    {
        // Query Laporan tetap sama, tapi handle case-insensitive buat status 'Selesai'/'selesai'
        $laporan = Transaksi::selectRaw('DATE(created_at) as tanggal, SUM(total_bayar) as total')
            ->whereRaw('LOWER(status) = ?', ['selesai']) // Pakai LOWER biar aman
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        $totalOmzet = Transaksi::whereRaw('LOWER(status) = ?', ['selesai'])->sum('total_bayar');
        $totalTransaksi = Transaksi::whereRaw('LOWER(status) = ?', ['selesai'])->count();

        return view('laporan.index', compact('laporan', 'totalOmzet', 'totalTransaksi'));
    }
}