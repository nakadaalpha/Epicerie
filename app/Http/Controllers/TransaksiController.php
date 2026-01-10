<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        // 1. Query Dasar dengan Relasi
        $query = Transaksi::with(['user', 'kurir', 'detailTransaksi.produk']);

        // 2. Logika PENCARIAN (Search)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($subQ) use ($search) {
                        $subQ->where('nama', 'like', '%' . $search . '%');
                    });
            });
        }

        // 3. Logika PENGURUTAN (Sorting)
        if ($request->has('sort')) {
            switch ($request->sort) {
                case 'terlama':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'terbesar':
                    $query->orderBy('total_bayar', 'desc');
                    break;
                case 'terkecil':
                    $query->orderBy('total_bayar', 'asc');
                    break;
                default: // 'terbaru'
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            // Default: Transaksi Terbaru di atas
            $query->orderBy('created_at', 'desc');
        }

        // 4. Pagination (Ganti get() dengan paginate())
        $transaksi = $query->paginate(10)->withQueryString();

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
        } elseif ($statusBaru == 'Selesai' && $trx->status == 'Dikirim') {
            $trx->status = 'Selesai';
        }

        $trx->save();
        return back()->with('success', 'Status pesanan diperbarui!');
    }

    public function laporan(Request $request)
    {
        // 1. Tentukan Rentang Waktu berdasarkan Filter
        $range = $request->range ?? '1_minggu'; // Default jika tidak ada pilihan
        $startDate = now();
        $labelPeriode = '';

        switch ($range) {
            case 'hari_ini':
                $startDate = now()->startOfDay();
                $labelPeriode = 'Hari Ini';
                break;
            case '1_bulan':
                $startDate = now()->subMonth();
                $labelPeriode = '1 Bulan Terakhir';
                break;
            case '6_bulan':
                $startDate = now()->subMonths(6);
                $labelPeriode = '6 Bulan Terakhir';
                break;
            case '1_tahun':
                $startDate = now()->subYear();
                $labelPeriode = '1 Tahun Terakhir';
                break;
            default: // '1_minggu'
                $startDate = now()->subDays(7);
                $labelPeriode = '7 Hari Terakhir';
                break;
        }

        // 2. Query Data Grafik (Dikelompokkan per Tanggal)
        $laporan = Transaksi::selectRaw('DATE(created_at) as tanggal, SUM(total_bayar) as total')
            ->whereRaw('LOWER(status) = ?', ['selesai']) // Case-insensitive check
            ->where('created_at', '>=', $startDate)      // Filter Tanggal Dinamis
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        // Format Tanggal agar lebih cantik di Grafik (contoh: "10 Jan")
        $laporan->transform(function ($item) {
            $item->tanggal = Carbon::parse($item->tanggal)->format('d M');
            return $item;
        });

        // 3. Query Total Omzet & Transaksi (Sesuai Rentang Waktu)
        $totalOmzet = Transaksi::whereRaw('LOWER(status) = ?', ['selesai'])
            ->where('created_at', '>=', $startDate)
            ->sum('total_bayar');

        $totalTransaksi = Transaksi::whereRaw('LOWER(status) = ?', ['selesai'])
            ->where('created_at', '>=', $startDate)
            ->count();

        return view('laporan.index', compact('laporan', 'totalOmzet', 'totalTransaksi', 'labelPeriode'));
    }
}
