<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function show($id)
    {
        $produk = Produk::findOrFail($id);

        // --- ALGORITMA REKOMENDASI HYBRID ---

        // A. COLLABORATIVE FILTERING (Item-based)
        // Mencari produk yang sering dibeli bersamaan dalam satu transaksi
        $collaborativeIds = DB::table('detail_transaksi as dt1')
            ->join('detail_transaksi as dt2', 'dt1.id_transaksi', '=', 'dt2.id_transaksi')
            ->where('dt1.id_produk', $id)       // Cari transaksi yang memuat produk ini
            ->where('dt2.id_produk', '!=', $id) // Ambil produk pasangannya
            ->select('dt2.id_produk', DB::raw('COUNT(*) as frekuensi'))
            ->groupBy('dt2.id_produk')
            ->orderByDesc('frekuensi')          // Urutkan dari yang paling sering dibeli bareng
            ->limit(6)                          // Ambil maksimal 6
            ->pluck('dt2.id_produk')
            ->toArray();

        // B. CONTENT-BASED FILTERING (Fallback)
        // Mengisi sisa slot rekomendasi dengan produk satu kategori
        $sisaSlot = 6 - count($collaborativeIds);
        $contentBasedIds = [];

        if ($sisaSlot > 0) {
            $contentBasedIds = Produk::where('id_kategori', $produk->id_kategori)
                ->where('id_produk', '!=', $id)
                ->whereNotIn('id_produk', $collaborativeIds) // Jangan ambil yang sudah ada di collaborative
                ->inRandomOrder()
                ->limit($sisaSlot)
                ->pluck('id_produk')
                ->toArray();
        }

        // C. PENGGABUNGAN (MERGE)
        // Gabungkan ID dari Collaborative (di awal) dan Content-Based (di akhir)
        $rekomendasiIds = array_merge($collaborativeIds, $contentBasedIds);

        if (!empty($rekomendasiIds)) {
            // Ambil Data Produk & Urutkan sesuai prioritas ID tadi (FIELD function MySQL)
            $idsString = implode(',', $rekomendasiIds);
            $produkLain = Produk::whereIn('id_produk', $rekomendasiIds)
                ->orderByRaw("FIELD(id_produk, $idsString)")
                ->get();
        } else {
            // Fallback terakhir jika toko masih kosong transaksi & kategori cuma 1 produk
            $produkLain = Produk::where('id_produk', '!=', $id)
                ->inRandomOrder()
                ->limit(6)
                ->get();
        }

        $cartData = $this->getCartSummary();

        return view('kiosk.show', array_merge(compact('produk', 'produkLain'), $cartData));
    }
}
