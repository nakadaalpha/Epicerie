<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Midtrans\Config;
use Midtrans\Snap;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Keranjang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\User;
use App\Models\Slider;

class KioskController extends Controller
{
    // === KONFIGURASI CONSTANT ===
    const SHOP_LAT = -7.73326;
    const SHOP_LNG = 110.33121;
    const MAX_DISTANCE_KM = 3.0;
    const ONGKIR_FLAT = 5000;
    const MIN_BELANJA = 30000;

    // === HELPER PENTING: HITUNG SUBTOTAL (JANGAN DIHAPUS) ===
    // Fungsi ini memperbaiki masalah stuck di keranjang karena harga dianggap 0
    private function hitungSubtotal($keranjang)
    {
        return $keranjang->sum(function ($item) {
            // Ambil harga dan diskon dengan aman
            $harga = $item->produk->harga_produk ?? 0;
            $diskon = $item->produk->persen_diskon ?? 0;

            // Hitung harga final setelah diskon
            $hargaFinal = $harga - ($harga * ($diskon / 100));

            return $hargaFinal * $item->jumlah;
        });
    }

    // === HELPER: Data Ringkas Keranjang (Untuk Navbar) ===
    private function getCartSummary()
    {
        if (!Auth::check()) return ['totalItemKeranjang' => 0, 'keranjangItems' => []];
        $keranjangItems = Keranjang::where('id_user', Auth::id())->pluck('jumlah', 'id_produk')->toArray();
        return ['totalItemKeranjang' => array_sum($keranjangItems), 'keranjangItems' => $keranjangItems];
    }

    // ========================================================================
    // 1. HALAMAN UTAMA & PRODUK
    // ========================================================================

    public function index()
    {
        try {
            $sliders = Slider::where('is_active', 1)->orderBy('urutan', 'asc')->get();
        } catch (\Exception $e) {
            $sliders = collect([]);
        }

        if ($sliders->isEmpty()) {
            $sliders = collect([
                (object)['gambar' => 'https://placehold.co/1200x400/3b82f6/ffffff?text=Promo+Spesial', 'judul' => 'Promo 1', 'is_dummy' => true],
                (object)['gambar' => 'https://placehold.co/1200x400/f97316/ffffff?text=Gratis+Ongkir', 'judul' => 'Promo 2', 'is_dummy' => true],
            ]);
        }

        $produkTerbaru = Produk::orderBy('created_at', 'desc')->limit(6)->get();

        $produkTerlaris = Produk::select('produk.*', DB::raw('COALESCE(SUM(detail_transaksi.jumlah), 0) as total_terjual'))
            ->leftJoin('detail_transaksi', 'produk.id_produk', '=', 'detail_transaksi.id_produk')
            ->groupBy('produk.id_produk')
            ->orderBy('total_terjual', 'desc')
            ->limit(6)
            ->get();

        $produk = Produk::inRandomOrder()->limit(12)->get();
        $kategoriList = Kategori::all();
        $cartData = $this->getCartSummary();

        return view('kiosk.index', array_merge(
            compact('produk', 'produkTerbaru', 'produkTerlaris', 'kategoriList', 'sliders'),
            $cartData
        ));
    }

    public function search(Request $request)
    {
        $keyword = $request->search;
        $selectedKategori = $request->kategori ?? [];
        $minPrice = $request->min_price;
        $maxPrice = $request->max_price;

        // 1. QUERY UTAMA (Pencarian User)
        $query = \App\Models\Produk::query();

        if ($keyword) {
            $query->where('nama_produk', 'like', '%' . $keyword . '%');
        }

        if (!empty($selectedKategori)) {
            $query->whereIn('id_kategori', $selectedKategori);
        }

        if ($minPrice) {
            $query->where('harga_produk', '>=', $minPrice);
        }

        if ($maxPrice) {
            $query->where('harga_produk', '<=', $maxPrice);
        }

        $produk = $query->get();
        $allCategories = \App\Models\Kategori::withCount('produk')->get();

        // --- 2. LOGIKA CONTENT-BASED RECOMMENDATION ---
        $rekomendasi = collect();

        if ($produk->isNotEmpty()) {
            // A. Jika ada hasil, ambil kategori dari produk-produk yang muncul
            // Kita ambil ID Kategori yang paling sering muncul di hasil pencarian
            $kategoriIds = $produk->pluck('id_kategori')->unique()->take(3); // Ambil maksimal 3 kategori relevan
            $excludeIds = $produk->pluck('id_produk')->toArray(); // Jangan tampilkan lagi produk yang sudah ada di hasil

            $rekomendasi = \App\Models\Produk::whereIn('id_kategori', $kategoriIds)
                ->whereNotIn('id_produk', $excludeIds) // Exclude produk yang sedang dilihat
                ->inRandomOrder()
                ->limit(4)
                ->get();
        }

        // B. Fallback: Jika rekomendasi kosong (atau hasil pencarian 0), cari berdasarkan kemiripan nama parsial
        if ($rekomendasi->isEmpty() && $keyword) {
            // Pecah keyword, misal "Roti Coklat" jadi ["Roti", "Coklat"]
            $words = explode(' ', $keyword);

            $queryRek = \App\Models\Produk::query();
            $queryRek->where(function ($q) use ($words) {
                foreach ($words as $word) {
                    if (strlen($word) > 2) { // Abaikan kata pendek
                        $q->orWhere('nama_produk', 'like', '%' . $word . '%');
                    }
                }
            });

            // Exclude hasil pencarian utama jika ada
            if ($produk->isNotEmpty()) {
                $queryRek->whereNotIn('id_produk', $produk->pluck('id_produk'));
            }

            $rekomendasi = $queryRek->limit(4)->inRandomOrder()->get();
        }

        // C. Fallback Terakhir: Jika masih kosong juga, tampilkan produk terlaris/random
        if ($rekomendasi->isEmpty()) {
            $rekomendasi = \App\Models\Produk::inRandomOrder()->limit(4)->get();
        }

        return view('kiosk.search', compact('produk', 'allCategories', 'keyword', 'selectedKategori', 'minPrice', 'maxPrice', 'rekomendasi'));
    }

    public function show($id)
    {
        // 1. Ambil data produk yang sedang dilihat
        $produk = \App\Models\Produk::with('kategori')->findOrFail($id);

        // 2. Persiapkan Kata Kunci untuk Content-Based Filtering
        // Contoh: "Kopi Susu Gula Aren" -> ["Kopi", "Susu", "Gula", "Aren"]
        $keywords = explode(' ', $produk->nama_produk);

        // Filter kata yang terlalu pendek (misal: "di", "ke", "yg" agar pencarian lebih akurat)
        $keywords = array_filter($keywords, function ($word) {
            return strlen($word) > 2;
        });

        // 3. Query Rekomendasi
        $produkLain = \App\Models\Produk::where('id_produk', '!=', $id) // Jangan tampilkan produk yang sedang dilihat
            ->where(function ($query) use ($produk, $keywords) {
                // Rule A: Cari yang KATEGORI-nya sama (Bobot paling tinggi)
                $query->where('id_kategori', $produk->id_kategori)

                    // Rule B: ATAU cari yang NAMANYA mirip (mengandung salah satu kata kunci)
                    ->orWhere(function ($subQuery) use ($keywords) {
                        foreach ($keywords as $word) {
                            $subQuery->orWhere('nama_produk', 'like', '%' . $word . '%');
                        }
                    });
            })
            // 4. Scoring / Pengurutan (Ranking)
            // Prioritaskan produk yang Kategori-nya SAMA persis
            ->orderByRaw("CASE WHEN id_kategori = ? THEN 1 ELSE 2 END", [$produk->id_kategori])
            // Lalu urutkan berdasarkan yang terbaru
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // 5. Fallback (Jaga-jaga jika hasil di atas kosong/kurang dari 6)
        if ($produkLain->count() < 6) {
            $excludeIds = $produkLain->pluck('id_produk')->push($id);
            $tambahan = \App\Models\Produk::whereNotIn('id_produk', $excludeIds)
                ->orderBy('terjual', 'desc') // Isi kekosongan dengan produk terlaris
                ->limit(6 - $produkLain->count())
                ->get();

            $produkLain = $produkLain->merge($tambahan);
        }

        return view('kiosk.show', compact('produk', 'produkLain'));
    }

    // ========================================================================
    // 2. KERANJANG (CART) & CHECKOUT
    // ========================================================================

    // Halaman Keranjang
    public function cart()
    {
        if (!Auth::check()) return redirect()->route('login');

        $keranjang = Keranjang::with('produk')->where('id_user', Auth::id())->get();

        $subtotal = $this->hitungSubtotal($keranjang);
        $minBelanja = self::MIN_BELANJA;

        // TAMBAHAN: Ambil 4 produk acak sebagai rekomendasi
        $rekomendasi = Produk::inRandomOrder()->limit(6)->get();

        // Jangan lupa passing 'rekomendasi' ke view
        return view('kiosk.cart', compact('keranjang', 'subtotal', 'minBelanja', 'rekomendasi'));
    }

    // Halaman Checkout
    public function checkoutPage()
    {
        if (!Auth::check()) return redirect()->route('login');
        $user = Auth::user();

        $keranjang = Keranjang::with('produk')->where('id_user', $user->id_user)->get();
        if ($keranjang->isEmpty()) return redirect()->route('kiosk.cart');

        // GUNAKAN HELPER HITUNG MANUAL
        $subtotal = $this->hitungSubtotal($keranjang);

        // Validasi Min Belanja
        if ($subtotal < self::MIN_BELANJA) {
            return redirect()->route('kiosk.cart')->with('error', 'Belanja kurang dari Rp ' . number_format(self::MIN_BELANJA));
        }

        $daftarAlamat = DB::table('alamat_pengiriman')
            ->where('id_user', $user->id_user)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        $ongkirKurir = ($user->membership == 'Gold') ? 0 : self::ONGKIR_FLAT;

        return view('kiosk.checkout', compact('keranjang', 'subtotal', 'daftarAlamat', 'ongkirKurir', 'user'));
    }

    // ========================================================================
    // 3. LOGIKA KERANJANG (ADD/REMOVE)
    // ========================================================================

    public function addToCart(Request $request, $id)
    {
        if (!Auth::check()) {
            if ($request->ajax()) return response()->json(['status' => 'error', 'message' => 'Login dulu!', 'redirect' => route('login')]);
            return redirect()->route('login');
        }

        $produk = Produk::find($id);
        if (!$produk || $produk->stok < 1) {
            if ($request->ajax()) return response()->json(['status' => 'error', 'message' => 'Stok Habis!']);
            return back()->with('error', 'Stok Habis!');
        }

        $userId = Auth::id();
        $qty = $request->input('qty', 1);

        $currentCart = Keranjang::where('id_user', $userId)->where('id_produk', $id)->first();
        $currentQty = $currentCart ? $currentCart->jumlah : 0;

        if (($currentQty + $qty) > $produk->stok) {
            if ($request->ajax()) return response()->json(['status' => 'error', 'message' => 'Stok tidak cukup!']);
            return back()->with('error', 'Stok tidak cukup!');
        }

        $cart = Keranjang::firstOrNew(['id_user' => $userId, 'id_produk' => $id]);
        $cart->jumlah += $qty;
        $cart->save();

        if ($request->type == 'now') return redirect()->route('kiosk.cart');

        if ($request->ajax()) return response()->json([
            'status' => 'success',
            'message' => 'Berhasil masuk keranjang',
            'total_cart' => Keranjang::where('id_user', $userId)->sum('jumlah')
        ]);

        return back()->with('success', 'Masuk keranjang!');
    }

    public function emptyCart()
    {
        Keranjang::where('id_user', Auth::id())->delete();
        return back();
    }

    public function increaseItem($id)
    {
        $item = Keranjang::where('id_user', Auth::id())->where('id_produk', $id)->first();
        if ($item && $item->produk->stok > $item->jumlah) $item->increment('jumlah');
        return back();
    }

    public function decreaseItem($id)
    {
        $item = Keranjang::where('id_user', Auth::id())->where('id_produk', $id)->first();
        if ($item) $item->jumlah > 1 ? $item->decrement('jumlah') : $item->delete();
        return back();
    }

    public function removeItem($id)
    {
        Keranjang::where('id_user', Auth::id())->where('id_produk', $id)->delete();
        return back();
    }

    // ========================================================================
    // 4. PROSES PEMBAYARAN (PROCESS PAYMENT)
    // ========================================================================

    public function processPayment(Request $request)
    {
        if (!Auth::check()) return response()->json(['error' => 'Unauthorized'], 401);
        $user = Auth::user();

        $keranjang = Keranjang::with('produk')->where('id_user', $user->id_user)->get();
        if ($keranjang->isEmpty()) return response()->json(['error' => 'Keranjang kosong'], 400);

        // Hitung Manual & Validasi Stok
        $subtotal = 0;
        foreach ($keranjang as $item) {
            if ($item->produk->stok < $item->jumlah) return response()->json(['error' => "Stok {$item->produk->nama_produk} kurang."], 400);
            $harga = $item->produk->harga_produk;
            $diskon = $item->produk->persen_diskon ?? 0;
            $subtotal += ($harga - ($harga * ($diskon / 100))) * $item->jumlah;
        }

        if ($subtotal < self::MIN_BELANJA) return response()->json(['error' => 'Min belanja kurang'], 400);

        // --- LOGIKA UTAMA PERBAIKAN ---
        $tipePengiriman = $request->input('tipe_pengiriman', 'delivery');
        $idAlamat = $request->id_alamat;
        $ongkir = 0;

        if ($tipePengiriman == 'delivery') {
            if (empty($idAlamat)) return response()->json(['error' => 'Pilih alamat pengiriman.'], 400);
            $ongkir = ($user->membership == 'Gold') ? 0 : self::ONGKIR_FLAT;
        } else {
            // JIKA PICKUP, PAKSA ALAMAT JADI NULL
            $ongkir = 0;
            $idAlamat = null;
        }

        $grandTotal = $subtotal + $ongkir;

        if ($request->metode_pembayaran == 'Tunai') {
            try {
                $idTrx = $this->createTransaction($user->id_user, $idAlamat, $grandTotal, $ongkir, 'Tunai', 'diproses', $keranjang);
                return response()->json(['status' => 'success', 'redirect_url' => route('kiosk.success', $idTrx)]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        try {
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;
            $params = ['transaction_details' => ['order_id' => 'MID-' . time() . rand(100, 999), 'gross_amount' => (int)$grandTotal], 'customer_details' => ['first_name' => $user->nama, 'email' => $user->email, 'phone' => $user->no_hp]];
            return response()->json(['snap_token' => Snap::getSnapToken($params)]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function midtransSuccess(Request $request)
    {
        if (!Auth::check()) return response()->json(['error' => 'Unauthorized'], 401);
        $user = Auth::user();
        $keranjang = Keranjang::with('produk')->where('id_user', $user->id_user)->get();
        if ($keranjang->isEmpty()) return response()->json(['status' => 'success']);

        $subtotal = $this->hitungSubtotal($keranjang);

        // Cek ID Alamat dari request JS. Jika null, berarti Pickup.
        $idAlamat = $request->id_alamat;

        // Logika Ongkir: Jika alamat ada, cek member. Jika null, gratis.
        $ongkir = 0;
        if ($idAlamat) {
            $ongkir = ($user->membership == 'Gold') ? 0 : self::ONGKIR_FLAT;
        }

        $grandTotal = $subtotal + $ongkir;

        try {
            $idTrx = $this->createTransaction($user->id_user, $idAlamat, $grandTotal, $ongkir, 'Midtrans', 'Dikemas', $keranjang);
            return response()->json(['status' => 'success', 'id_transaksi' => $idTrx]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function completeTransaction($id)
    {
        // Cari transaksi milik user
        $trx = Transaksi::where('id_transaksi', $id)
            ->where('id_user_pembeli', Auth::id())
            ->firstOrFail();

        // Update status
        $trx->update([
            'status' => 'selesai',
            'updated_at' => now()
        ]);

        return back()->with('success', 'Terima kasih! Pesanan telah selesai.');
    }

    // public function midtransSuccess(Request $request)
    // {
    //     if (!Auth::check()) return response()->json(['error' => 'Unauthorized'], 401);
    //     $user = Auth::user();
    //     $keranjang = Keranjang::with('produk')->where('id_user', $user->id_user)->get();
    //     if ($keranjang->isEmpty()) return response()->json(['status' => 'success']);

    //     // Recalculate manual
    //     $subtotal = $this->hitungSubtotal($keranjang);

    //     $ongkir = ($user->membership == 'Gold') ? 0 : self::ONGKIR_FLAT;
    //     $idAlamat = $request->id_alamat ?? DB::table('alamat_pengiriman')->where('id_user', $user->id_user)->value('id_alamat');
    //     $grandTotal = $subtotal + $ongkir;

    //     try {
    //         $idTrx = $this->createTransaction($user->id_user, $idAlamat, $grandTotal, $ongkir, 'Midtrans', 'Dikemas', $keranjang);
    //         return response()->json(['status' => 'success', 'id_transaksi' => $idTrx]);
    //     } catch (\Exception $e) {
    //         return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    //     }
    // }

    private function createTransaction($userId, $idAlamat, $total, $ongkir, $metode, $status, $keranjang)
    {
        DB::beginTransaction();
        try {
            $idTrx = DB::table('transaksi')->insertGetId([
                'id_user_pembeli' => $userId,
                'id_alamat' => $idAlamat,
                'kode_transaksi' => 'TRX-' . time(),
                'total_bayar' => $total,
                'ongkos_kirim' => $ongkir,
                'metode_pembayaran' => $metode,
                'status' => $status,
                'tanggal_transaksi' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            foreach ($keranjang as $item) {
                // Hitung ulang harga final per item untuk disimpan di history
                $harga = $item->produk->harga_produk;
                $diskon = $item->produk->persen_diskon ?? 0;
                $hargaFinal = $harga - ($harga * ($diskon / 100));

                DB::table('detail_transaksi')->insert([
                    'id_transaksi' => $idTrx,
                    'id_produk' => $item->id_produk,
                    'jumlah' => $item->jumlah,
                    'harga_produk_saat_beli' => $hargaFinal,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                Produk::where('id_produk', $item->id_produk)->decrement('stok', $item->jumlah);
            }
            Keranjang::where('id_user', $userId)->delete();
            DB::commit();
            return $idTrx;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ========================================================================
    // 5. HALAMAN PENDUKUNG LAINNYA
    // ========================================================================

    public function riwayatTransaksi(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');
        $query = Transaksi::with(['detailTransaksi.produk'])->where('id_user_pembeli', Auth::id());
        if ($request->filled('status')) $query->where('status', $request->status);
        $riwayat = $query->orderBy('created_at', 'desc')->get();
        return view('kiosk.riwayat', compact('riwayat'));
    }

    public function trackingPage($id)
    {
        $trx = Transaksi::with('detailTransaksi.produk')->findOrFail($id);
        return view('kiosk.tracking', compact('trx'));
    }

    public function successPage($id)
    {
        $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->first();
        if (!$transaksi || (Auth::check() && $transaksi->id_user_pembeli != Auth::id())) return redirect()->route('kiosk.index');
        $details = DB::table('detail_transaksi')->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id_produk')->where('id_transaksi', $id)->get();
        return view('kiosk.success', compact('transaksi', 'details'));
    }

    public function profile()
    {
        $user = Auth::user();
        $alamat = DB::table('alamat_pengiriman')->where('id_user', $user->id_user)->orderBy('is_primary', 'desc')->get();
        return view('kiosk.profile', compact('user', 'alamat'));
    }

    public function updateProfile(Request $request)
    {
        $user = User::find(Auth::id());
        $request->validate(['nama' => 'required', 'email' => 'nullable|email', 'no_hp' => 'nullable|numeric']);
        $user->update($request->only(['nama', 'email', 'no_hp']));
        return back()->with('success', 'Data profil diperbarui!');
    }

    public function updatePhoto(Request $request)
    {
        $request->validate(['foto_profil' => 'required|image|max:2048']);
        $user = User::find(Auth::id());
        if ($request->hasFile('foto_profil')) {
            if ($user->foto_profil) Storage::disk('public')->delete($user->foto_profil);
            $user->update(['foto_profil' => $request->file('foto_profil')->store('profiles', 'public')]);
        }
        return back()->with('success', 'Foto profil diperbarui!');
    }

    // ALAMAT HELPERS
    private function validateDistance($plusCodeInput)
    {
        // Simple bypass logic for now to avoid complexity errors
        return ['valid' => true, 'bypass' => true, 'clean_code' => $plusCodeInput];
    }

    public function addAddress(Request $request)
    {
        $request->validate(['label' => 'required', 'penerima' => 'required', 'no_hp_penerima' => 'required', 'detail_alamat' => 'required']);
        DB::table('alamat_pengiriman')->insert(array_merge($request->only(['label', 'penerima', 'no_hp_penerima', 'detail_alamat', 'plus_code']), ['id_user' => Auth::id(), 'created_at' => now()]));
        return back()->with('success', 'Alamat disimpan!');
    }

    public function updateAddress(Request $request, $id)
    {
        DB::table('alamat_pengiriman')->where('id_alamat', $id)->update($request->only(['label', 'penerima', 'no_hp_penerima', 'detail_alamat', 'plus_code']));
        return back()->with('success', 'Alamat diperbarui!');
    }

    public function deleteAddress($id)
    {
        DB::table('alamat_pengiriman')->where('id_alamat', $id)->delete();
        return back();
    }

    public function setPrimaryAddress($id)
    {
        DB::table('alamat_pengiriman')->where('id_user', Auth::id())->update(['is_primary' => 0]);
        DB::table('alamat_pengiriman')->where('id_alamat', $id)->update(['is_primary' => 1]);
        return back();
    }
}
