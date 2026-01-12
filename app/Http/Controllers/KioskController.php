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
use Barryvdh\DomPDF\Facade\Pdf; // Import PDF
use SimpleSoftwareIO\QrCode\Facades\QrCode; // Import QR Code

class KioskController extends Controller
{
    // === KONFIGURASI CONSTANT ===
    const SHOP_LAT = -7.73326;
    const SHOP_LNG = 110.33121;
    const MAX_DISTANCE_KM = 3.0;
    const ONGKIR_FLAT = 5000;
    const MIN_BELANJA = 30000;

    // === HELPER PENTING: HITUNG SUBTOTAL ===
    private function hitungSubtotal($keranjang)
    {
        return $keranjang->sum(function ($item) {
            $harga = $item->produk->harga_produk ?? 0;
            $diskon = $item->produk->persen_diskon ?? 0;
            $hargaFinal = $harga - ($harga * ($diskon / 100));
            return $hargaFinal * $item->jumlah;
        });
    }

    // === HELPER: Data Ringkas Keranjang ===
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

        $produk = Produk::latest()->paginate(12);
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
        $sort = $request->sort;

        $query = Produk::query();

        if ($keyword) $query->where('nama_produk', 'like', '%' . $keyword . '%');
        if (!empty($selectedKategori)) $query->whereIn('id_kategori', $selectedKategori);
        if ($minPrice) $query->where('harga_produk', '>=', $minPrice);
        if ($maxPrice) $query->where('harga_produk', '<=', $maxPrice);

        switch ($sort) {
            case 'termurah':
                $query->orderBy('harga_produk', 'asc');
                break;
            case 'termahal':
                $query->orderBy('harga_produk', 'desc');
                break;
            case 'terbaru':
                $query->orderBy('created_at', 'desc');
                break;
            case 'terlaris':
                $query->select('produk.*', DB::raw('COALESCE(SUM(detail_transaksi.jumlah), 0) as total_terjual'))
                    ->leftJoin('detail_transaksi', 'produk.id_produk', '=', 'detail_transaksi.id_produk')
                    ->groupBy('produk.id_produk')
                    ->orderBy('total_terjual', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        $produk = $query->get();
        $allCategories = Kategori::withCount('produk')->get();
        $rekomendasi = collect();

        return view('kiosk.search', compact('produk', 'allCategories', 'keyword', 'selectedKategori', 'minPrice', 'maxPrice', 'rekomendasi', 'sort'));
    }

    public function show($id)
    {
        $produk = Produk::with('kategori')->findOrFail($id);
        $keywords = array_filter(explode(' ', $produk->nama_produk), fn($word) => strlen($word) > 2);

        $totalTerjual = DB::table('detail_transaksi')->where('id_produk', $id)->sum('jumlah');

        $produkLain = Produk::where('id_produk', '!=', $id)
            ->where(function ($query) use ($produk, $keywords) {
                $query->where('id_kategori', $produk->id_kategori)
                    ->orWhere(function ($subQuery) use ($keywords) {
                        foreach ($keywords as $word) $subQuery->orWhere('nama_produk', 'like', '%' . $word . '%');
                    });
            })
            ->orderByRaw("CASE WHEN id_kategori = ? THEN 1 ELSE 2 END", [$produk->id_kategori])
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        if ($produkLain->count() < 6) {
            $excludeIds = $produkLain->pluck('id_produk')->push($id);
            $tambahan = Produk::select('produk.*', DB::raw('COALESCE(SUM(detail_transaksi.jumlah), 0) as total_terjual'))
                ->leftJoin('detail_transaksi', 'produk.id_produk', '=', 'detail_transaksi.id_produk')
                ->whereNotIn('produk.id_produk', $excludeIds)
                ->groupBy('produk.id_produk')
                ->orderBy('total_terjual', 'desc')
                ->limit(6 - $produkLain->count())
                ->get();
            $produkLain = $produkLain->merge($tambahan);
        }

        return view('kiosk.show', compact('produk', 'produkLain', 'totalTerjual'));
    }

    // ========================================================================
    // 2. KERANJANG & CHECKOUT
    // ========================================================================

    public function cart()
    {
        if (!Auth::check()) return redirect()->route('login');
        $keranjang = Keranjang::with('produk')->where('id_user', Auth::id())->get();
        $subtotal = $this->hitungSubtotal($keranjang);
        $minBelanja = self::MIN_BELANJA;
        $rekomendasi = Produk::inRandomOrder()->limit(6)->get();

        return view('kiosk.cart', compact('keranjang', 'subtotal', 'minBelanja', 'rekomendasi'));
    }

    public function checkoutPage()
    {
        if (!Auth::check()) return redirect()->route('login');
        $user = Auth::user();
        $keranjang = Keranjang::with('produk')->where('id_user', $user->id_user)->get();
        if ($keranjang->isEmpty()) return redirect()->route('kiosk.cart');

        $subtotal = $this->hitungSubtotal($keranjang);
        if ($subtotal < self::MIN_BELANJA) return redirect()->route('kiosk.cart')->with('error', 'Belanja kurang dari Rp ' . number_format(self::MIN_BELANJA));

        $daftarAlamat = DB::table('alamat_pengiriman')->where('id_user', $user->id_user)->orderBy('is_primary', 'desc')->orderBy('created_at', 'desc')->get();
        $ongkirKurir = ($user->membership == 'Gold') ? 0 : self::ONGKIR_FLAT;

        return view('kiosk.checkout', compact('keranjang', 'subtotal', 'daftarAlamat', 'ongkirKurir', 'user'));
    }

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
        if ($request->ajax()) return response()->json(['status' => 'success', 'message' => 'Berhasil masuk keranjang', 'total_cart' => Keranjang::where('id_user', $userId)->sum('jumlah')]);

        return back()->with('success', 'Masuk keranjang!');
    }

    // ... (Fungsi emptyCart, increaseItem, decreaseItem, removeItem SAMA SEPERTI SEBELUMNYA) ...
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
    // 3. PEMBAYARAN & TRANSAKSI
    // ========================================================================

    public function processPayment(Request $request)
    {
        if (!Auth::check()) return response()->json(['error' => 'Unauthorized'], 401);
        $user = Auth::user();

        $keranjang = Keranjang::with('produk')->where('id_user', $user->id_user)->get();
        if ($keranjang->isEmpty()) return response()->json(['error' => 'Keranjang kosong'], 400);

        $subtotal = 0;
        foreach ($keranjang as $item) {
            if ($item->produk->stok < $item->jumlah) return response()->json(['error' => "Stok {$item->produk->nama_produk} kurang."], 400);
            $harga = $item->produk->harga_produk;
            $diskon = $item->produk->persen_diskon ?? 0;
            $subtotal += ($harga - ($harga * ($diskon / 100))) * $item->jumlah;
        }

        if ($subtotal < self::MIN_BELANJA) return response()->json(['error' => 'Min belanja kurang'], 400);

        $tipePengiriman = $request->input('tipe_pengiriman', 'delivery');
        $idAlamat = $request->id_alamat;
        $ongkir = 0;

        if ($tipePengiriman == 'delivery') {
            if (empty($idAlamat)) return response()->json(['error' => 'Pilih alamat pengiriman.'], 400);
            $ongkir = ($user->membership == 'Gold') ? 0 : self::ONGKIR_FLAT;
        } else {
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
        $idAlamat = $request->id_alamat;
        $ongkir = ($idAlamat && $user->membership != 'Gold') ? self::ONGKIR_FLAT : 0;
        $grandTotal = $subtotal + $ongkir;

        try {
            $idTrx = $this->createTransaction($user->id_user, $idAlamat, $grandTotal, $ongkir, 'Midtrans', 'Dikemas', $keranjang);
            return response()->json(['status' => 'success', 'id_transaksi' => $idTrx]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

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

    public function completeTransaction($id)
    {
        $trx = Transaksi::where('id_transaksi', $id)->where('id_user_pembeli', Auth::id())->firstOrFail();
        $trx->update(['status' => 'selesai', 'updated_at' => now()]);
        return back()->with('success', 'Terima kasih! Pesanan telah selesai.');
    }

    // ========================================================================
    // 4. PROFIL & FITUR TAMBAHAN (REQUEST KARTU & DOWNLOAD)
    // ========================================================================

    public function profile()
    {
        $user = Auth::user();
        $alamat = DB::table('alamat_pengiriman')->where('id_user', $user->id_user)->orderBy('is_primary', 'desc')->get();
        return view('kiosk.profile', compact('user', 'alamat'));
    }

    public function viewMemberCard()
    {
        $user = Auth::user();
        return view('kiosk.member-card', compact('user'));
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

    // --- FITUR BARU: Request Cetak Kartu ---
    // 1. Download PDF (Dipanggil saat tombol "Download PDF" di Modal ditekan)
    public function downloadQr()
    {
        $user = auth()->user();

        // Ukuran Kertas ID-1 (Standar Kartu ATM/KTP) dalam point
        // 85.60mm x 53.98mm => 242.64pt x 153.01pt
        $customPaper = [0, 0, 242.64, 153.01];

        $pdf = Pdf::loadView('pdf.member-card', compact('user'));
        $pdf->setPaper($customPaper, 'landscape');

        return $pdf->download('MemberCard-' . $user->username . '.pdf');
    }

    // 2. Request Cetak Fisik (Dipanggil dari Form di Modal)
    public function requestCetakKartu()
    {
        $user = auth()->user();

        // Cek status
        if ($user->status_cetak_kartu == 'pending') {
            return back()->with('error', 'Permintaan Anda sedang diproses oleh admin.');
        }
        if ($user->status_cetak_kartu == 'completed') {
            // Jika sudah completed tapi request lagi (misal hilang), reset ke pending
            $user->update(['status_cetak_kartu' => 'pending']);
            return back()->with('success', 'Permintaan cetak ulang berhasil dikirim.');
        }

        $user->update(['status_cetak_kartu' => 'pending']);

        return back()->with('success', 'Permintaan cetak kartu fisik berhasil dikirim!');
    }

    // ========================================================================
    // 5. MANAJEMEN ALAMAT
    // ========================================================================

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

    // ========================================================================
    // 6. HALAMAN LAIN (Riwayat, Tracking, Success)
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
}
