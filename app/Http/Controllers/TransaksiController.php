<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use App\Models\DetailTransaksi; // Pastikan model ini di-import
use App\Models\Produk;          // Pastikan model ini di-import
use App\Models\User;            // Pastikan model ini di-import
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    // =================================================
    // 1. HALAMAN UTAMA (RIWAYAT TRANSAKSI)
    // =================================================
    public function index(Request $request)
    {
        $query = Transaksi::with(['user', 'kurir', 'detailTransaksi.produk']);

        // Logika PENCARIAN
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_transaksi', 'like', '%' . $search . '%')
                    ->orWhereHas('user', function ($subQ) use ($search) {
                        $subQ->where('nama', 'like', '%' . $search . '%');
                    });
            });
        }

        // Logika PENGURUTAN
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
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $transaksi = $query->paginate(10)->withQueryString();

        return view('transaksi.index', compact('transaksi'));
    }

    // =================================================
    // 2. HALAMAN KASIR (POS)
    // =================================================
    public function create()
    {
        // Ambil Produk (Stok > 0)
        $produk = Produk::where('stok', '>', 0)->get();

        // Ambil Kategori
        $kategori = \App\Models\Kategori::all();

        // Ambil Data Pelanggan (Untuk Dropdown/Search Member)
        $pelanggan = User::where('role', 'pelanggan')
            ->get()
            ->append(['membership', 'membership_color']);

        // Generate Kode Transaksi Otomatis
        $today = date('Ymd');
        $lastTrx = Transaksi::whereDate('created_at', today())->latest()->first();
        $urut = $lastTrx ? (int)substr($lastTrx->kode_transaksi, -3) + 1 : 1;
        $kode_transaksi = 'TRX-' . $today . '-' . sprintf('%03d', $urut);

        return view('transaksi.create', compact('produk', 'kategori', 'kode_transaksi', 'pelanggan'));
    }

    // =================================================
    // 3. PROSES SIMPAN TRANSAKSI (STORE)
    // =================================================
    public function store(Request $request)
    {
        // Validasi Input
        $request->validate([
            'cart_data' => 'required',
            'bayar_diterima' => 'required|numeric',
        ]);

        // Decode Data Keranjang dari JSON
        $items = json_decode($request->cart_data);

        if (empty($items)) {
            return back()->with('error', 'Keranjang belanja kosong!');
        }

        // --- HITUNG ULANG TOTAL DI BACKEND (KEAMANAN) ---
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item->price * $item->qty;
        }

        // Hitung Diskon Membership
        $diskon = 0;
        $id_user_pembeli = $request->id_user; // Ambil ID dari hidden input

        if ($id_user_pembeli) {
            $user = User::find($id_user_pembeli);
            if ($user) {
                switch ($user->membership) {
                    case 'Gold': $diskon = $subtotal * 0.10; break;
                    case 'Silver': $diskon = $subtotal * 0.05; break;
                    case 'Bronze': $diskon = $subtotal * 0.02; break;
                    default: $diskon = 0; break;
                }
            }
        }

        // Total Akhir yang harus dibayar
        $final_total = $subtotal - $diskon;

        // Cek Pembayaran
        if ($request->bayar_diterima < $final_total) {
            return back()->with('error', 'Uang pembayaran kurang!');
        }

        // --- SIMPAN KE DATABASE (TRANSACTION) ---
        DB::transaction(function () use ($request, $items, $final_total, $id_user_pembeli) {
            
            // 1. Simpan Header Transaksi
            $trx = Transaksi::create([
                'kode_transaksi' => $request->kode_transaksi,
                'total_bayar'    => $final_total,
                'status'         => 'Selesai', // Langsung selesai karena POS
                
                // PERBAIKAN UTAMA: Menggunakan 'id_user_pembeli' sesuai database
                'id_user_pembeli' => $id_user_pembeli, 
                
                // Nama Pelanggan: Jika member ambil dari DB, jika umum ambil inputan
                'nama_pelanggan' => $id_user_pembeli 
                                    ? User::find($id_user_pembeli)->nama 
                                    : ($request->nama_pelanggan ?? 'Umum'),
            ]);

            // 2. Simpan Detail & Kurangi Stok
            foreach ($items as $item) {
                DetailTransaksi::create([
                    'id_transaksi' => $trx->id_transaksi,
                    'id_produk' => $item->id,
                    'jumlah' => $item->qty,
                    'harga_produk_saat_beli' => $item->price,
                ]);

                // Kurangi Stok Produk
                $produk = Produk::find($item->id);
                if ($produk) {
                    $produk->decrement('stok', $item->qty);
                }
            }
        });

        return redirect()->route('transaksi.index')->with('success', 'Transaksi Berhasil Disimpan!');
    }

    // =================================================
    // 4. UPDATE STATUS (UNTUK PESANAN ONLINE)
    // =================================================
    public function updateStatus(Request $request, $id)
    {
        $trx = Transaksi::findOrFail($id);
        $statusBaru = $request->status;

        // Validasi Alur Status
        if ($statusBaru == 'Dikirim' && in_array($trx->status, ['Dikemas', 'diproses'])) {
            $trx->status = 'Dikirim';
            $trx->id_karyawan = auth()->id(); // Catat siapa yang memproses
        } elseif ($statusBaru == 'Selesai' && $trx->status == 'Dikirim') {
            $trx->status = 'Selesai';
        }

        $trx->save();
        return back()->with('success', 'Status pesanan diperbarui!');
    }

    // =================================================
    // 5. LAPORAN KEUANGAN
    // =================================================
    public function laporan(Request $request)
    {
        // Tentukan Rentang Waktu
        $range = $request->range ?? '1_minggu';
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

        // Query Data Grafik (Group by Date)
        $laporan = Transaksi::selectRaw('DATE(created_at) as tanggal, SUM(total_bayar) as total')
            ->whereRaw('LOWER(status) = ?', ['selesai'])
            ->where('created_at', '>=', $startDate)
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
            ->get();

        $laporan->transform(function ($item) {
            $item->tanggal = Carbon::parse($item->tanggal)->format('d M');
            return $item;
        });

        // Query Total Ringkasan
        $totalOmzet = Transaksi::whereRaw('LOWER(status) = ?', ['selesai'])
            ->where('created_at', '>=', $startDate)
            ->sum('total_bayar');

        $totalTransaksi = Transaksi::whereRaw('LOWER(status) = ?', ['selesai'])
            ->where('created_at', '>=', $startDate)
            ->count();

        return view('laporan.index', compact('laporan', 'totalOmzet', 'totalTransaksi', 'labelPeriode'));
    }
    
    // Fitur Hapus (Tambahan jika diperlukan)
    public function destroy($id)
    {
        $trx = Transaksi::findOrFail($id);
        
        // Opsional: Kembalikan stok jika transaksi dihapus
        // foreach($trx->detailTransaksi as $detail) {
        //     $detail->produk->increment('stok', $detail->jumlah);
        // }
        
        $trx->delete();
        return back()->with('success', 'Data transaksi dihapus.');
    }
    
    // Fitur Detail (Tambahan untuk Modal Detail)
    public function show($id)
    {
        $transaksi = Transaksi::with(['detailTransaksi.produk', 'user', 'kurir'])->findOrFail($id);
        return view('transaksi.show', compact('transaksi')); // Atau return JSON jika pakai AJAX murni
    }
}