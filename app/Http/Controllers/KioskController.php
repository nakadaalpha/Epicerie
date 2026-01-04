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

    // === 1. HALAMAN DEPAN (HOME) ===
    public function index()
    {
        // 0. SLIDER (DENGAN FAIL-SAFE & DUMMY)
        try {
            // Coba ambil dari database
            $sliders = Slider::where('is_active', 1)->orderBy('urutan', 'asc')->get();
        } catch (\Exception $e) {
            // Jika tabel belum ada (biar gak error 500)
            $sliders = collect([]);
        }

        // JIKA KOSONG, ISI DENGAN DUMMY (Supaya tampilan tidak rusak)
        if ($sliders->isEmpty()) {
            $sliders = collect([
                (object)[
                    'gambar' => 'https://placehold.co/1200x400/3b82f6/ffffff?text=Promo+Spesial+Hari+Ini',
                    'judul' => 'Promo Dummy 1',
                    'is_dummy' => true // Penanda biar view tahu ini gambar online
                ],
                (object)[
                    'gambar' => 'https://placehold.co/1200x400/f97316/ffffff?text=Gratis+Ongkir+Se-Indonesia',
                    'judul' => 'Promo Dummy 2',
                    'is_dummy' => true
                ],
                (object)[
                    'gambar' => 'https://placehold.co/1200x400/10b981/ffffff?text=Diskon+50%25+Produk+Baru',
                    'judul' => 'Promo Dummy 3',
                    'is_dummy' => true
                ]
            ]);
        }

        // A. Produk Terbaru
        $produkTerbaru = Produk::orderBy('created_at', 'desc')->limit(5)->get();

        // B. Produk Terlaris
        $produkTerlaris = Produk::select('produk.*', DB::raw('COALESCE(SUM(detail_transaksi.jumlah), 0) as total_terjual'))
            ->leftJoin('detail_transaksi', 'produk.id_produk', '=', 'detail_transaksi.id_produk')
            ->groupBy('produk.id_produk')
            ->orderBy('total_terjual', 'desc')
            ->limit(5)
            ->get();

        // C. Grid Produk Utama
        $produk = Produk::inRandomOrder()->limit(12)->get();

        $kategoriList = Kategori::all();
        $cartData = $this->getCartSummary();

        return view('kiosk.index', array_merge(
            compact('produk', 'produkTerbaru', 'produkTerlaris', 'kategoriList', 'sliders'),
            $cartData
        ));
    }

    // === 2. HALAMAN PENCARIAN (SEARCH) ===
    public function search(Request $request)
    {
        $query = Produk::query();

        // Filter Keyword
        $keyword = $request->input('search');
        if ($keyword) {
            $query->where('nama_produk', 'LIKE', '%' . $keyword . '%');
        }

        // Filter Kategori
        $selectedKategori = $request->input('kategori', []);
        if (!is_array($selectedKategori) && $selectedKategori != '') {
            $selectedKategori = [$selectedKategori];
        }
        if (!empty($selectedKategori)) {
            $query->whereIn('id_kategori', $selectedKategori);
        }

        // Filter Harga
        $minPrice = $request->input('min_price');
        $maxPrice = $request->input('max_price');
        if ($minPrice) $query->where('harga_produk', '>=', $minPrice);
        if ($maxPrice) $query->where('harga_produk', '<=', $maxPrice);

        $produk = $query->orderBy('nama_produk', 'asc')->get();
        $allCategories = Kategori::all();
        $cartData = $this->getCartSummary();

        return view('kiosk.search', array_merge(
            compact('produk', 'allCategories', 'selectedKategori', 'keyword', 'minPrice', 'maxPrice'),
            $cartData
        ));
    }

    // === 3. DETAIL PRODUK ===
    public function show($id)
    {
        $produk = Produk::findOrFail($id);
        $produkLain = Produk::where('id_kategori', $produk->id_kategori)
            ->where('id_produk', '!=', $id)
            ->inRandomOrder()
            ->limit(6)
            ->get();

        $cartData = $this->getCartSummary();

        return view('kiosk.show', array_merge(compact('produk', 'produkLain'), $cartData));
    }

    // === HELPER: Data Keranjang ===
    private function getCartSummary()
    {
        if (!Auth::check()) {
            return ['totalItemKeranjang' => 0, 'keranjangItems' => []];
        }
        $keranjangItems = Keranjang::where('id_user', Auth::id())->pluck('jumlah', 'id_produk')->toArray();
        return ['totalItemKeranjang' => array_sum($keranjangItems), 'keranjangItems' => $keranjangItems];
    }

    // === 4. ADD TO CART ===
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

        $cart = Keranjang::firstOrNew(['id_user' => $userId, 'id_produk' => $id]);
        $cart->jumlah += $qty;
        $cart->save();

        if ($request->type == 'now') return redirect()->route('kiosk.checkout');
        if ($request->ajax()) return response()->json(['status' => 'success', 'total_cart' => Keranjang::where('id_user', $userId)->sum('jumlah')]);

        return back()->with('success', 'Masuk keranjang!');
    }

    // === 5. CHECKOUT ===
    public function checkout()
    {
        if (!Auth::check()) return redirect()->route('login');
        $userId = Auth::id();
        $keranjang = Keranjang::with('produk')->where('id_user', $userId)->get();

        if ($keranjang->isEmpty()) return redirect()->route('kiosk.index');

        $subtotal = $keranjang->sum(fn($item) => $item->produk->harga_produk * $item->jumlah);
        $ongkir = self::ONGKIR_FLAT;
        $totalBayar = $subtotal + $ongkir;

        $daftarAlamat = DB::table('alamat_pengiriman')
            ->where('id_user', $userId)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('kiosk.checkout', compact('keranjang', 'subtotal', 'ongkir', 'totalBayar', 'daftarAlamat'));
    }

    // === 6. PROSES BAYAR ===
    public function processPayment(Request $request)
    {
        if (!Auth::check()) return response()->json(['error' => 'Unauthorized'], 401);
        $userId = Auth::id();
        $keranjang = Keranjang::with('produk')->where('id_user', $userId)->get();

        if ($keranjang->isEmpty()) return response()->json(['error' => 'Keranjang kosong'], 400);

        $subtotal = $keranjang->sum(fn($i) => $i->produk->harga_produk * $i->jumlah);
        if ($subtotal < self::MIN_BELANJA) return response()->json(['error' => 'Minimal belanja Rp ' . number_format(self::MIN_BELANJA)], 400);

        $ongkir = self::ONGKIR_FLAT;
        $grandTotal = $subtotal + $ongkir;

        // Ambil ID Alamat (Prioritas dari Request, Fallback ke Utama)
        $idAlamat = $request->id_alamat ?? DB::table('alamat_pengiriman')->where('id_user', $userId)->orderBy('is_primary', 'desc')->value('id_alamat');

        // Bayar Tunai
        if ($request->metode_pembayaran == 'Tunai') {
            try {
                $idTrx = $this->createTransaction($userId, $idAlamat, $grandTotal, $ongkir, 'Tunai', 'diproses', $keranjang);
                return response()->json(['status' => 'success', 'redirect_url' => route('kiosk.success', $idTrx)]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        // Bayar Midtrans
        try {
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $params = [
                'transaction_details' => ['order_id' => 'MID-' . time() . rand(100, 999), 'gross_amount' => $grandTotal],
                'customer_details' => ['first_name' => Auth::user()->nama, 'email' => Auth::user()->email, 'phone' => Auth::user()->no_hp]
            ];
            return response()->json(['snap_token' => Snap::getSnapToken($params)]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function midtransSuccess(Request $request)
    {
        if (!Auth::check()) return response()->json(['error' => 'Unauthorized'], 401);
        $userId = Auth::id();
        $keranjang = Keranjang::with('produk')->where('id_user', $userId)->get();
        if ($keranjang->isEmpty()) return response()->json(['status' => 'success']);

        $subtotal = $keranjang->sum(fn($i) => $i->produk->harga_produk * $i->jumlah);
        $grandTotal = $subtotal + self::ONGKIR_FLAT;
        $idAlamat = $request->id_alamat ?? DB::table('alamat_pengiriman')->where('id_user', $userId)->orderBy('is_primary', 'desc')->value('id_alamat');

        try {
            $idTrx = $this->createTransaction($userId, $idAlamat, $grandTotal, self::ONGKIR_FLAT, 'Midtrans', 'Dikemas', $keranjang);
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
                DB::table('detail_transaksi')->insert([
                    'id_transaksi' => $idTrx,
                    'id_produk' => $item->id_produk,
                    'jumlah' => $item->jumlah,
                    'harga_produk_saat_beli' => $item->produk->harga_produk,
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

    // === 7. RIWAYAT & TRACKING ===
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

        $details = DB::table('detail_transaksi')
            ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id_produk')
            ->where('id_transaksi', $id)->get();
        return view('kiosk.success', compact('transaksi', 'details'));
    }

    // === 8. PROFILE MANAGEMENT (YANG DIPERBAIKI) ===
    public function profile()
    {
        $user = Auth::user();
        $alamat = DB::table('alamat_pengiriman')
            ->where('id_user', $user->id_user)
            ->orderBy('is_primary', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        return view('kiosk.profile', compact('user', 'alamat'));
    }

    // Update Biodata (Nama, Email, HP)
    public function updateProfile(Request $request)
    {
        $user = User::find(Auth::id());

        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email|max:100|unique:user,email,' . $user->id_user . ',id_user',
            'no_hp' => 'nullable|numeric|digits_between:10,15',
        ]);

        $user->nama = $request->nama;
        if ($request->has('email')) $user->email = $request->email;
        if ($request->has('no_hp')) $user->no_hp = $request->no_hp;

        $user->save();

        return back()->with('success', 'Data profil berhasil diperbarui!');
    }

    // Update Foto Profil
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'foto_profil' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = User::find(Auth::id());

        if ($request->hasFile('foto_profil')) {
            // Hapus foto lama
            if ($user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
                Storage::disk('public')->delete($user->foto_profil);
            }
            // Simpan baru
            $user->foto_profil = $request->file('foto_profil')->store('profiles', 'public');
            $user->save();
        }

        return back()->with('success', 'Foto profil diperbarui!');
    }

    // === 9. ALAMAT MANAGEMENT ===
    private function validateDistance($plusCodeInput)
    {
        $cleanCode = strtoupper(trim($plusCodeInput));
        preg_match('/[A-Z0-9]{4,8}\+[A-Z0-9]{2,}/', $cleanCode, $matches);
        $cleanCode = !empty($matches) ? $matches[0] : $cleanCode;

        try {
            $response = Http::withoutVerifying()->timeout(5)
                ->withHeaders(['User-Agent' => 'EpicerieApp/1.0'])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $cleanCode . ' Sleman, Yogyakarta',
                    'format' => 'json',
                    'limit' => 1
                ]);

            if ($response->failed() || empty($response->json())) {
                return ['valid' => true, 'bypass' => true, 'clean_code' => $cleanCode];
            }

            $data = $response->json();
            if (!empty($data) && isset($data[0]['lat'], $data[0]['lon'])) {
                $latUser = $data[0]['lat'];
                $lngUser = $data[0]['lon'];

                $theta = self::SHOP_LNG - $lngUser;
                $dist = sin(deg2rad(self::SHOP_LAT)) * sin(deg2rad($latUser)) + cos(deg2rad(self::SHOP_LAT)) * cos(deg2rad($latUser)) * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $miles = $dist * 60 * 1.1515;
                $km = $miles * 1.609344;

                return [
                    'valid' => $km <= self::MAX_DISTANCE_KM,
                    'distance' => round($km, 2),
                    'lat' => $latUser,
                    'lng' => $lngUser,
                    'clean_code' => $cleanCode
                ];
            }
        } catch (\Exception $e) {
            return ['valid' => true, 'bypass' => true, 'clean_code' => $cleanCode];
        }
        return ['valid' => false, 'error' => 'Format kode salah.'];
    }

    public function addAddress(Request $request)
    {
        $request->validate([
            'label' => 'required',
            'penerima' => 'required',
            'no_hp_penerima' => 'required',
            'detail_alamat' => 'required',
            'plus_code' => 'required'
        ]);

        $check = $this->validateDistance($request->plus_code);
        if (isset($check['error'])) return back()->with('error', $check['error'])->withInput();
        if (!$check['valid']) return back()->with('error', "Jarak terlalu jauh ({$check['distance']} KM).")->withInput();

        DB::table('alamat_pengiriman')->insert([
            'id_user' => Auth::id(),
            'label' => $request->label,
            'penerima' => $request->penerima,
            'no_hp_penerima' => $request->no_hp_penerima,
            'detail_alamat' => $request->detail_alamat,
            'latitude' => $check['lat'] ?? null,
            'longitude' => $check['lng'] ?? null,
            'plus_code' => $check['clean_code'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return back()->with('success', 'Alamat disimpan!');
    }

    public function updateAddress(Request $request, $id)
    {
        $request->validate([
            'label' => 'required',
            'penerima' => 'required',
            'no_hp_penerima' => 'required',
            'detail_alamat' => 'required',
            'plus_code' => 'required'
        ]);

        $check = $this->validateDistance($request->plus_code);
        if (isset($check['error'])) return back()->with('error', $check['error'])->withInput();
        if (!$check['valid']) return back()->with('error', "Update gagal! Jarak {$check['distance']} KM.")->withInput();

        DB::table('alamat_pengiriman')->where('id_alamat', $id)->where('id_user', Auth::id())->update([
            'label' => $request->label,
            'penerima' => $request->penerima,
            'no_hp_penerima' => $request->no_hp_penerima,
            'detail_alamat' => $request->detail_alamat,
            'latitude' => $check['lat'] ?? null,
            'longitude' => $check['lng'] ?? null,
            'plus_code' => $check['clean_code'],
            'updated_at' => now()
        ]);
        return back()->with('success', 'Alamat diperbarui!');
    }

    public function setPrimaryAddress($id)
    {
        DB::table('alamat_pengiriman')->where('id_user', Auth::id())->update(['is_primary' => 0]);
        DB::table('alamat_pengiriman')->where('id_alamat', $id)->where('id_user', Auth::id())->update(['is_primary' => 1]);
        return back()->with('success', 'Alamat utama diganti!');
    }

    public function deleteAddress($id)
    {
        DB::table('alamat_pengiriman')->where('id_alamat', $id)->where('id_user', Auth::id())->delete();
        return back()->with('success', 'Alamat dihapus.');
    }

    // === 10. KERANJANG UTILS ===
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

    // Placeholder (Jika diperlukan untuk error handling)
    public function setCartQuantity()
    {
        return back();
    }
    public function addPacketToCart()
    {
        return back();
    }
    public function recallOrder()
    {
        return back();
    }
    public function listPending()
    {
        return back();
    }
}
