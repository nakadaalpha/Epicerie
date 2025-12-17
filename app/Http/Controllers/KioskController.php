<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Keranjang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use Illuminate\Support\Facades\DB;

class KioskController extends Controller
{
    private $tabletUserId = 1;

    // === 0. KONFIGURASI PAKET (RESEP RAHASIA) ===
    // Di sini kamu atur isi paketnya. Sistem akan cari barang yg namanya MIRIP.
    private function getPaketConfig()
    {
        return [
            'anak_kos' => [
                'nama' => 'Paket Anak Kos',
                'ikon' => '🎓',
                'warna' => 'bg-gradient-to-r from-orange-400 to-red-500',
                'harga_display' => 'Hemat banget!', 
                'items' => [
                    ['keyword' => 'Indomie', 'qty' => 5],
                    ['keyword' => 'Telur', 'qty' => 2],
                    ['keyword' => 'Minuman', 'qty' => 1], // Pastikan ada produk yg namanya mengandung 'Minuman' atau ganti 'Teh'/'Kopi'
                ]
            ],
            'sembako_ibu' => [
                'nama' => 'Paket Sembako',
                'ikon' => '🏠',
                'warna' => 'bg-gradient-to-r from-green-400 to-teal-500',
                'harga_display' => 'Lengkap!',
                'items' => [
                    ['keyword' => 'Beras', 'qty' => 1],
                    ['keyword' => 'Minyak', 'qty' => 1],
                    ['keyword' => 'Gula', 'qty' => 1],
                    ['keyword' => 'Telur', 'qty' => 5],
                ]
            ],
            'sarapan' => [
                'nama' => 'Paket Sarapan',
                'ikon' => '☕',
                'warna' => 'bg-gradient-to-r from-blue-400 to-indigo-500',
                'harga_display' => 'Praktis',
                'items' => [
                    ['keyword' => 'Roti', 'qty' => 1],
                    ['keyword' => 'Selai', 'qty' => 1],
                    ['keyword' => 'Susu', 'qty' => 1],
                ]
            ]
        ];
    }

    // === 1. MENAMPILKAN HALAMAN DEPAN ===
    public function index(Request $request)
    {
        $query = Produk::query();

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nama_produk', 'LIKE', '%' . $search . '%')
                  ->orWhere('deskripsi_produk', 'LIKE', '%' . $search . '%');
        }

        if ($request->has('kategori') && $request->kategori != 'semua') {
            $query->where('id_kategori', $request->kategori);
        }

        $produk = $query->get();
        $kategoriList = Kategori::all(); 

        $keranjangItems = Keranjang::where('id_user', $this->tabletUserId)
                                   ->pluck('jumlah', 'id_produk')
                                   ->toArray();
        $totalItemKeranjang = array_sum($keranjangItems);

        // Ambil Daftar Paket buat ditampilin
        $daftarPaket = $this->getPaketConfig();

        return view('kiosk.index', compact('produk', 'totalItemKeranjang', 'keranjangItems', 'kategoriList', 'daftarPaket'));
    }

    // === 2. LOGIKA TAMBAH KE KERANJANG ===
    public function addToCart($id)
    {
        $produk = Produk::find($id);
        
        if($produk->stok < 1) {
            return back()->with('error', 'Stok Habis!'); 
        }

        $cekKeranjang = Keranjang::where('id_user', $this->tabletUserId)
                                 ->where('id_produk', $id)
                                 ->first();

        $rekomendasi = collect();

        if ($cekKeranjang) {
            $cekKeranjang->jumlah += 1;
            $cekKeranjang->save();
        } else {
            Keranjang::create([
                'id_user' => $this->tabletUserId,
                'id_produk' => $id,
                'jumlah' => 1
            ]);

            // LOGIKA REKOMENDASI
            $kamusJodoh = [
                'Selai'   => ['Roti', 'Tawar'],       
                'Roti'    => ['Selai', 'Mentega', 'Susu'], 
                'Indomie' => ['Telur', 'Sawi', 'Kornet'], 
                'Mie'     => ['Telur', 'Sawi'],
                'Kopi'    => ['Gula', 'Susu', 'Krimer'],   
                'Teh'     => ['Gula', 'Lemon'],
                'Tepung'  => ['Minyak', 'Gula'],
                'Rokok'   => ['Korek', 'Permen'],
                'Nasi'    => ['Ayam', 'Telur', 'Kerupuk'],
            ];

            $namaProdukDibeli = $produk->nama_produk;
            $keywordPencarian = [];

            foreach ($kamusJodoh as $kunci => $target) {
                if (stripos($namaProdukDibeli, $kunci) !== false) {
                    $keywordPencarian = $target;
                    break;
                }
            }

            if (!empty($keywordPencarian)) {
                $barangDiKeranjang = Keranjang::where('id_user', $this->tabletUserId)
                                              ->pluck('id_produk')
                                              ->toArray();

                $rekomendasi = Produk::where('id_produk', '!=', $id)
                    ->whereNotIn('id_produk', $barangDiKeranjang)
                    ->where('stok', '>', 0)
                    ->where(function($query) use ($keywordPencarian) {
                        foreach ($keywordPencarian as $word) {
                            $query->orWhere('nama_produk', 'LIKE', '%' . $word . '%');
                        }
                    })
                    ->inRandomOrder()
                    ->take(2)
                    ->get();
            }
        }

        return redirect()->route('kiosk.index')->with([
            'success' => 'Berhasil masuk keranjang!',
            'rekomendasi_produk' => $rekomendasi 
        ]);
    }

    // === 3. HALAMAN CHECKOUT ===
    public function checkout()
    {
        $keranjang = Keranjang::with('produk')->where('id_user', $this->tabletUserId)->get();

        $totalBayar = 0;
        foreach($keranjang as $item) {
            $totalBayar += $item->produk->harga_produk * $item->jumlah;
        }

        if ($keranjang->isEmpty()) {
            return redirect()->route('kiosk.index');
        }

        return view('kiosk.checkout', compact('keranjang', 'totalBayar'));
    }

    // === 4. PROSES PEMBAYARAN ===
    public function processPayment(Request $request)
    {
        $request->validate([
            'metode_pembayaran' => 'required'
        ]);

        DB::transaction(function () use ($request) {
            $keranjang = Keranjang::with('produk')->where('id_user', $this->tabletUserId)->get();
            
            $totalBayar = 0;
            foreach($keranjang as $item){
                $totalBayar += $item->produk->harga_produk * $item->jumlah;
            }

            $transaksiBaru = Transaksi::create([
                'kode_transaksi' => 'TRX-' . time(), 
                'id_user_pembeli' => $this->tabletUserId, 
                'id_user_kasir' => $this->tabletUserId,
                'total_bayar' => $totalBayar,
                'metode_pembayaran' => $request->metode_pembayaran,
                'tanggal_transaksi' => now(),
                'status' => 'selesai'
            ]);

            foreach($keranjang as $item){
                DetailTransaksi::create([
                    'id_transaksi' => $transaksiBaru->id_transaksi,
                    'id_produk' => $item->id_produk,
                    'jumlah' => $item->jumlah,
                    'harga_produk_saat_beli' => $item->produk->harga_produk,
                ]);

                $produkAsli = Produk::find($item->id_produk);
                $produkAsli->stok -= $item->jumlah;
                $produkAsli->save();
            }

            Keranjang::where('id_user', $this->tabletUserId)->delete();
        });

        return view('kiosk.success');
    }

    // === 5. FUNGSI HOLD ORDER ===
    public function holdOrder(Request $request)
    {
        $request->validate(['nama_hold' => 'required']);

        DB::transaction(function () use ($request) {
            $keranjang = Keranjang::with('produk')->where('id_user', $this->tabletUserId)->get();
            
            if ($keranjang->isEmpty()) return;

            $totalBayar = 0;
            foreach($keranjang as $item) {
                $totalBayar += $item->produk->harga_produk * $item->jumlah;
            }

            $transaksi = Transaksi::create([
                'id_user_kasir' => $this->tabletUserId, 
                'id_user_pembeli' => $this->tabletUserId,
                'kode_transaksi' => 'HLD-' . time(), 
                'total_bayar' => $totalBayar,
                'tanggal_transaksi' => now(),
                'status' => 'pending',
                'nama_pelanggan_hold' => $request->nama_hold,
                'metode_pembayaran' => null,
            ]);

            foreach ($keranjang as $item) {
                DetailTransaksi::create([
                    'id_transaksi' => $transaksi->id_transaksi,
                    'id_produk' => $item->id_produk,
                    'jumlah' => $item->jumlah,
                    'harga_produk_saat_beli' => $item->produk->harga_produk,
                ]);
            }

            Keranjang::where('id_user', $this->tabletUserId)->delete();
        });

        return redirect()->route('kiosk.index')->with('success', 'Pesanan berhasil di-hold!');
    }

    // === 6. HALAMAN DAFTAR PENDING ===
    public function listPending()
    {
        $pendingOrders = Transaksi::where('status', 'pending')
                                  ->orderBy('tanggal_transaksi', 'desc')
                                  ->get();
                                  
        return view('kiosk.pending', compact('pendingOrders'));
    }

    // === 7. FUNGSI RECALL ===
    public function recallOrder($id)
    {
        DB::transaction(function () use ($id) {
            $transaksi = Transaksi::with('detailTransaksi')->findOrFail($id);
            Keranjang::where('id_user', $this->tabletUserId)->delete();
            foreach ($transaksi->detailTransaksi as $detail) {
                Keranjang::create([
                    'id_user' => $this->tabletUserId,
                    'id_produk' => $detail->id_produk,
                    'jumlah' => $detail->jumlah,
                ]);
            }
            DetailTransaksi::where('id_transaksi', $id)->delete();
            $transaksi->delete(); 
        });

        return redirect()->route('kiosk.index')->with('success', 'Pesanan dikembalikan ke kasir!');
    }

    // === 8. KURANGI ITEM ===
    public function decreaseItem($id)
    {
        $item = Keranjang::where('id_user', $this->tabletUserId)
                         ->where('id_produk', $id)
                         ->first();
        if ($item) {
            if ($item->jumlah > 1) {
                $item->jumlah -= 1;
                $item->save();
            } else {
                $item->delete(); 
            }
        }
        return back()->with('success', 'Item berhasil dikurangi');
    }

    // === 9. HAPUS ITEM ===
    public function removeItem($id)
    {
        Keranjang::where('id_user', $this->tabletUserId)
                 ->where('id_produk', $id)
                 ->delete();
        return back()->with('success', 'Item dihapus dari keranjang');
    }

    // === 10. TAMBAH ITEM (CHECKOUT) ===
    public function increaseItem($id)
    {
        $item = Keranjang::where('id_user', $this->tabletUserId)
                         ->where('id_produk', $id)
                         ->first();
        
        $produk = Produk::find($id);
        
        if ($item && $produk->stok > $item->jumlah) {
            $item->jumlah += 1;
            $item->save();
            return back()->with('success', 'Jumlah berhasil ditambah');
        } elseif ($item) {
            return back()->with('error', 'Stok tidak cukup!');
        }

        return back();
    }

    // === 11. ATUR JUMLAH SPESIFIK ===
    public function setCartQuantity(Request $request, $id)
    {
        $request->validate(['qty' => 'required|numeric|min:1']);
        $jumlahBaru = $request->qty;
        $produk = Produk::find($id);

        if ($produk->stok < $jumlahBaru) {
            return back()->with('error', 'Stok tidak cukup! Sisa: ' . $produk->stok);
        }

        $item = Keranjang::where('id_user', $this->tabletUserId)
                         ->where('id_produk', $id)
                         ->first();

        if ($item) {
            $item->jumlah = $jumlahBaru;
            $item->save();
        } else {
            Keranjang::create([
                'id_user' => $this->tabletUserId,
                'id_produk' => $id,
                'jumlah' => $jumlahBaru
            ]);
        }
        return back()->with('success', 'Jumlah berhasil diatur!');
    }

    // === 12. FITUR BARU: ADD PAKET TO CART (EKSEKUTOR PAKET) ===
    public function addPacketToCart($key)
    {
        $configs = $this->getPaketConfig();
        
        // Cek apakah paket ada di resep
        if (!array_key_exists($key, $configs)) {
            return back()->with('error', 'Paket tidak ditemukan!');
        }

        $paket = $configs[$key];
        $itemsAdded = 0;

        DB::transaction(function () use ($paket, &$itemsAdded) {
            foreach ($paket['items'] as $item) {
                // Cari produk yg namanya mengandung Keyword (misal: "Indomie")
                // Ambil yg pertama ketemu aja
                $produk = Produk::where('nama_produk', 'LIKE', '%' . $item['keyword'] . '%')
                                ->where('stok', '>', 0)
                                ->first();

                if ($produk) {
                    $cekKeranjang = Keranjang::where('id_user', $this->tabletUserId)
                                             ->where('id_produk', $produk->id_produk)
                                             ->first();

                    if ($cekKeranjang) {
                        $cekKeranjang->jumlah += $item['qty'];
                        $cekKeranjang->save();
                    } else {
                        Keranjang::create([
                            'id_user' => $this->tabletUserId,
                            'id_produk' => $produk->id_produk,
                            'jumlah' => $item['qty']
                        ]);
                    }
                    $itemsAdded++;
                }
            }
        });

        if ($itemsAdded > 0) {
            return back()->with('success', 'Paket ' . $paket['nama'] . ' berhasil ditambahkan!');
        } else {
            return back()->with('error', 'Stok barang dalam paket sedang habis semua.');
        }
    }
}