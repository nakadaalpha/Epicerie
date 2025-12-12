<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Keranjang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Support\Facades\DB;

class KioskController extends Controller
{
    // ID User khusus untuk Tablet (Sesuai database kamu tadi, ID 1 itu 'Tablet Toko')
    // Jadi semua transaksi di tablet ini akan tercatat atas nama ID 1.
    private $tabletUserId = 1;

    // === 1. MENAMPILKAN HALAMAN DEPAN (KATALOG) ===
    public function index()
    {
        // Ambil semua data produk dari database
        $produk = Produk::all();

        // Hitung ada berapa barang di keranjang (biar muncul angka di ikon tas belanja)
        $totalItemKeranjang = Keranjang::where('id_user', $this->tabletUserId)->sum('jumlah');

        // Kirim data produk & total keranjang ke tampilan (View)
        return view('kiosk.index', compact('produk', 'totalItemKeranjang'));
    }

    // === 2. LOGIKA TAMBAH KE KERANJANG ===
    public function addToCart($id)
    {
        // Cek dulu, barangnya ada gak? Stoknya cukup gak?
        $produk = Produk::find($id);
        
        if($produk->stok < 1) {
            // Kalau habis, balikin ke halaman sebelumnya
            return back()->with('error', 'Stok Habis!'); 
        }

        // Cek apakah barang ini UDAH ADA di keranjang sebelumnya?
        $cekKeranjang = Keranjang::where('id_user', $this->tabletUserId)
                                 ->where('id_produk', $id)
                                 ->first();

        if ($cekKeranjang) {
            // Kalau udah ada, kita tambah jumlahnya aja (+1)
            $cekKeranjang->jumlah += 1;
            $cekKeranjang->save();
        } else {
            // Kalau belum ada, kita bikin baris baru di keranjang
            Keranjang::create([
                'id_user' => $this->tabletUserId,
                'id_produk' => $id,
                'jumlah' => 1
            ]);
        }

        // Balik lagi ke katalog
        return redirect()->route('kiosk.index');
    }

    // === 3. HALAMAN CHECKOUT (LIHAT KERANJANG) ===
    public function checkout()
    {
        // Ambil isi keranjang punya si Tablet (ID 1)
        // with('produk') itu teknik Eager Loading, biar query database gak berat
        $keranjang = Keranjang::with('produk')->where('id_user', $this->tabletUserId)->get();

        // Hitung Total Rupiah yang harus dibayar
        $totalBayar = 0;
        foreach($keranjang as $item) {
            $totalBayar += $item->produk->harga_produk * $item->jumlah;
        }

        // Kalau keranjang kosong, jangan boleh masuk sini, tendang ke depan
        if ($keranjang->isEmpty()) {
            return redirect()->route('kiosk.index');
        }

        return view('kiosk.checkout', compact('keranjang', 'totalBayar'));
    }

    // === 4. PROSES PEMBAYARAN (TRANSAKSI FINISH) ===
    public function processPayment(Request $request)
    {
        // 1. Validasi: User harus pilih metode pembayaran
        $request->validate([
            'metode_pembayaran' => 'required'
        ]);

        // 2. Mulai Transaksi Database (Biar aman, kalau gagal satu, batal semua)
        DB::transaction(function () use ($request) {
            
            // Ambil data keranjang lagi
            $keranjang = Keranjang::with('produk')->where('id_user', $this->tabletUserId)->get();
            
            // Hitung total lagi (buat keamanan server side)
            $totalBayar = 0;
            foreach($keranjang as $item){
                $totalBayar += $item->produk->harga_produk * $item->jumlah;
            }

            // A. Simpan ke Tabel TRANSAKSI (Nota Utama)
            $transaksiBaru = Transaksi::create([
                'kode_transaksi' => 'TRX-' . time(), 
                'id_user_pembeli' => $this->tabletUserId, 
                'id_user_kasir' => $this->tabletUserId,
                'total_bayar' => $totalBayar,
                'metode_pembayaran' => $request->metode_pembayaran,
                'tanggal_transaksi' => now()
            ]);

            // B. Simpan ke Tabel DETAIL_TRANSAKSI & KURANGI STOK
            foreach($keranjang as $item){
                // Catat detail barang yg dibeli
                DetailTransaksi::create([
                    'id_transaksi' => $transaksiBaru->id_transaksi,
                    'id_produk' => $item->id_produk,
                    'jumlah' => $item->jumlah,
                    'harga_produk_saat_beli' => $item->produk->harga_produk,
                    // 'subtotal' => ...  <-- HAPUS BARIS INI JUGA! Database kamu gak punya kolom ini.
                ]);

                // Kurangi Stok Produk Asli
                $produkAsli = Produk::find($item->id_produk);
                $produkAsli->stok -= $item->jumlah;
                $produkAsli->save();
            }

            // C. Hapus Isi Keranjang (Karena udah dibayar)
            Keranjang::where('id_user', $this->tabletUserId)->delete();
        });

        // 3. Tampilkan Halaman Sukses
        return view('kiosk.success');
    }
}