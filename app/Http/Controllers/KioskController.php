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

class KioskController extends Controller
{
    // === KONFIGURASI CONSTANT (Mudah diubah) ===
    const SHOP_LAT = -7.73326;
    const SHOP_LNG = 110.33121;
    const MAX_DISTANCE_KM = 3.0;
    const ONGKIR_FLAT = 5000;
    const MIN_BELANJA = 30000;

    // === 1. HALAMAN DEPAN ===
    public function index(Request $request)
    {
        $query = Produk::query();

        if ($request->filled('search')) {
            $query->where('nama_produk', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->filled('kategori') && $request->kategori != 'semua') {
            $query->where('id_kategori', $request->kategori);
        }

        $produk = $query->orderBy('nama_produk', 'asc')->limit(20)->get();
        $kategoriList = Kategori::all();

        // Menggunakan Helper untuk data keranjang
        $cartData = $this->getCartSummary();

        return view('kiosk.index', array_merge(compact('produk', 'kategoriList'), $cartData));
    }

    // === 2. DETAIL PRODUK ===
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

    // === HELPER: AMBIL DATA KERANJANG ===
    private function getCartSummary()
    {
        if (!Auth::check()) {
            return ['totalItemKeranjang' => 0, 'keranjangItems' => []];
        }

        $keranjangItems = Keranjang::where('id_user', Auth::id())
            ->pluck('jumlah', 'id_produk')
            ->toArray();

        return [
            'totalItemKeranjang' => array_sum($keranjangItems),
            'keranjangItems' => $keranjangItems
        ];
    }

    // === 3. TAMBAH KERANJANG ===
    public function addToCart(Request $request, $id)
    {
        if (!Auth::check()) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Silakan login terlebih dahulu!', 'redirect' => route('login')]);
            }
            return redirect()->route('login')->with('error', 'Silakan login untuk berbelanja');
        }

        $produk = Produk::find($id);
        if (!$produk || $produk->stok < 1) {
            $msg = 'Stok Habis!';
            if ($request->ajax()) return response()->json(['status' => 'error', 'message' => $msg]);
            return back()->with('error', $msg);
        }

        $userId = Auth::id();
        $qty = $request->input('qty', 1);

        // Gunakan updateOrCreate agar lebih ringkas
        $cart = Keranjang::firstOrNew(['id_user' => $userId, 'id_produk' => $id]);
        $cart->jumlah += $qty;
        $cart->save();

        if ($request->type == 'now') return redirect()->route('kiosk.checkout');

        if ($request->ajax()) {
            $newTotal = Keranjang::where('id_user', $userId)->sum('jumlah');
            return response()->json(['status' => 'success', 'message' => 'Berhasil masuk keranjang!', 'total_cart' => $newTotal]);
        }

        return back()->with('success', 'Berhasil masuk keranjang!');
    }

    // === 4. HALAMAN CHECKOUT ===
    public function checkout()
    {
        if (!Auth::check()) return redirect()->route('login');

        $userId = Auth::id();
        $keranjang = Keranjang::with('produk')->where('id_user', $userId)->get();

        if ($keranjang->isEmpty()) return redirect()->route('kiosk.index');

        $subtotal = $keranjang->sum(fn($item) => $item->produk->harga_produk * $item->jumlah);
        $ongkir = self::ONGKIR_FLAT;
        $minBelanja = self::MIN_BELANJA;
        $totalBayar = $subtotal + $ongkir;

        $daftarAlamat = DB::table('alamat_pengiriman')
            ->where('id_user', $userId)
            ->orderBy('is_primary', 'desc') // Prioritaskan alamat utama
            ->orderBy('created_at', 'desc')
            ->get();

        return view('kiosk.checkout', compact('keranjang', 'subtotal', 'ongkir', 'totalBayar', 'minBelanja', 'daftarAlamat'));
    }

    // === 5. PROSES BAYAR ===
    public function processPayment(Request $request)
    {
        if (!Auth::check()) return response()->json(['error' => 'Unauthorized'], 401);

        $userId = Auth::id();
        $keranjang = Keranjang::with('produk')->where('id_user', $userId)->get();

        if ($keranjang->isEmpty()) return response()->json(['error' => 'Keranjang kosong'], 400);

        // Hitung Subtotal & Siapkan Item Details Midtrans
        $subtotal = 0;
        $itemDetails = [];

        foreach ($keranjang as $item) {
            $harga = $item->produk->harga_produk;
            $subtotal += $harga * $item->jumlah;

            $itemDetails[] = [
                'id' => $item->id_produk,
                'price' => (int)$harga,
                'quantity' => (int)$item->jumlah,
                'name' => substr($item->produk->nama_produk, 0, 50)
            ];
        }

        if ($subtotal < self::MIN_BELANJA) {
            return response()->json(['error' => 'Minimal belanja Rp ' . number_format(self::MIN_BELANJA)], 400);
        }

        $ongkir = self::ONGKIR_FLAT;
        $grandTotal = $subtotal + $ongkir;

        $itemDetails[] = [
            'id' => 'ONGKIR',
            'price' => (int)$ongkir,
            'quantity' => 1,
            'name' => 'Ongkir Kurir (Max 3KM)'
        ];

        // Ambil Alamat
        $idAlamat = $request->id_alamat;
        if (!$idAlamat) {
            $lastAddr = DB::table('alamat_pengiriman')->where('id_user', $userId)->orderBy('is_primary', 'desc')->orderBy('created_at', 'desc')->first();
            $idAlamat = $lastAddr ? $lastAddr->id_alamat : null;
        }

        // --- BAYAR TUNAI ---
        if ($request->metode_pembayaran == 'Tunai') {
            try {
                $idTransaksi = $this->createTransactionRecord($userId, $idAlamat, $grandTotal, $ongkir, 'Tunai', 'diproses', $keranjang);
                return response()->json(['status' => 'success', 'redirect_url' => route('kiosk.success', $idTransaksi)]);
            } catch (\Exception $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        // --- BAYAR MIDTRANS ---
        try {
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = true;
            Config::$is3ds = true;
            $user = Auth::user();

            $params = [
                'transaction_details' => ['order_id' => 'MID-' . time() . rand(100, 999), 'gross_amount' => $grandTotal],
                'item_details' => $itemDetails,
                'customer_details' => ['first_name' => $user->nama, 'email' => $user->email ?? 'user@epicerie.com', 'phone' => $user->no_hp ?? '08123456789']
            ];
            return response()->json(['snap_token' => Snap::getSnapToken($params)]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // === 6. MIDTRANS CALLBACK ===
    public function midtransSuccess(Request $request)
    {
        if (!Auth::check()) return response()->json(['error' => 'Unauthorized'], 401);
        $userId = Auth::id();

        $keranjang = Keranjang::with('produk')->where('id_user', $userId)->get();
        if ($keranjang->isEmpty()) return response()->json(['status' => 'success']); // Already processed or empty

        // Logic Alamat (sama seperti processPayment)
        $idAlamat = $request->id_alamat;
        if (!$idAlamat) {
            $lastAddr = DB::table('alamat_pengiriman')->where('id_user', $userId)->orderBy('is_primary', 'desc')->orderBy('created_at', 'desc')->first();
            $idAlamat = $lastAddr ? $lastAddr->id_alamat : null;
        }

        // Hitung Total Ulang untuk validasi
        $subtotal = $keranjang->sum(fn($item) => $item->produk->harga_produk * $item->jumlah);
        $ongkir = self::ONGKIR_FLAT;
        $grandTotal = $subtotal + $ongkir;

        try {
            // Panggil Helper Create Transaction
            $idTransaksi = $this->createTransactionRecord($userId, $idAlamat, $grandTotal, $ongkir, 'Midtrans', 'Dikemas', $keranjang);
            return response()->json(['status' => 'success', 'id_transaksi' => $idTransaksi]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // === HELPER: CREATE TRANSACTION (DRY Code) ===
    private function createTransactionRecord($userId, $idAlamat, $totalBayar, $ongkir, $metode, $status, $keranjang)
    {
        DB::beginTransaction();
        try {
            $idTransaksi = DB::table('transaksi')->insertGetId([
                'id_user_pembeli' => $userId,
                'id_user_kasir' => null,
                'id_alamat' => $idAlamat,
                'kode_transaksi' => 'TRX-' . ($metode == 'Midtrans' ? 'MID-' : '') . time(),
                'total_bayar' => $totalBayar,
                'ongkos_kirim' => $ongkir,
                'metode_pembayaran' => $metode,
                'status' => $status,
                'tanggal_transaksi' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $detailsData = [];
            foreach ($keranjang as $item) {
                $detailsData[] = [
                    'id_transaksi' => $idTransaksi,
                    'id_produk' => $item->id_produk,
                    'jumlah' => $item->jumlah,
                    'harga_produk_saat_beli' => $item->produk->harga_produk,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
                // Kurangi Stok
                Produk::where('id_produk', $item->id_produk)->decrement('stok', $item->jumlah);
            }

            // Bulk Insert Detail Transaksi (Lebih Cepat)
            DB::table('detail_transaksi')->insert($detailsData);

            // Hapus Keranjang
            Keranjang::where('id_user', $userId)->delete();

            DB::commit();
            return $idTransaksi;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // === 7. RIWAYAT TRANSAKSI ===
    public function riwayatTransaksi(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');

        $query = Transaksi::with(['detailTransaksi.produk'])
            ->where('id_user_pembeli', Auth::id());

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $riwayat = $query->orderBy('created_at', 'desc')->get();
        return view('kiosk.riwayat', compact('riwayat'));
    }

    // === 8. HALAMAN STRUK ===
    public function successPage($id)
    {
        $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->first();

        if (!$transaksi) return redirect()->route('kiosk.index');

        // Security check
        if (Auth::check() && $transaksi->id_user_pembeli != Auth::id()) {
            return redirect()->route('kiosk.index')->with('error', 'Akses ditolak');
        }

        $details = DB::table('detail_transaksi')
            ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id_produk')
            ->where('id_transaksi', $id)
            ->get();

        return view('kiosk.success', compact('transaksi', 'details'));
    }

    // === PROFILE & ADDRESS MANAGEMENT ===
    public function profile()
    {
        $user = Auth::user();
        $alamat = DB::table('alamat_pengiriman')
            ->where('id_user', $user->id_user)
            ->orderBy('is_primary', 'desc') // Utama dulu
            ->orderBy('created_at', 'desc')
            ->get();
        return view('kiosk.profile', compact('user', 'alamat'));
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email',
            'no_hp' => 'nullable|numeric',
        ]);

        $user = User::find(Auth::id());
        $user->update($request->only(['nama', 'email', 'no_hp']));

        return back()->with('success', 'Biodata berhasil diperbarui!');
    }

    public function updatePhoto(Request $request)
    {
        $request->validate(['foto_profil' => 'required|image|max:2048']);
        $user = User::find(Auth::id());

        if ($request->hasFile('foto_profil')) {
            if ($user->foto_profil) {
                Storage::disk('public')->delete($user->foto_profil);
            }
            $user->foto_profil = $request->file('foto_profil')->store('profiles', 'public');
            $user->save();
        }

        return back()->with('success', 'Foto profil diperbarui!');
    }

    // === ALAMAT & JARAK ===
    private function validateDistance($plusCodeInput)
    {
        $cleanCode = strtoupper(trim($plusCodeInput));
        // Simple regex fix
        preg_match('/[A-Z0-9]{4,8}\+[A-Z0-9]{2,}/', $cleanCode, $matches);
        $cleanCode = !empty($matches) ? $matches[0] : $cleanCode;

        try {
            $response = Http::withoutVerifying()
                ->timeout(5)
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

                // Haversine
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
            'label' => 'required|string',
            'penerima' => 'required|string',
            'no_hp_penerima' => 'required',
            'detail_alamat' => 'required|string',
            'plus_code' => 'required|string'
        ]);

        $check = $this->validateDistance($request->plus_code);

        if (isset($check['error'])) return back()->with('error', $check['error'])->withInput();
        if (!$check['valid']) return back()->with('error', "Jarak terlalu jauh ({$check['distance']} KM). Maks: " . self::MAX_DISTANCE_KM . " KM.")->withInput();

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
            'label' => 'required|string',
            'penerima' => 'required|string',
            'no_hp_penerima' => 'required',
            'detail_alamat' => 'required|string',
            'plus_code' => 'required|string'
        ]);

        $check = $this->validateDistance($request->plus_code);

        if (isset($check['error'])) return back()->with('error', $check['error'])->withInput();
        if (!$check['valid']) return back()->with('error', "Update gagal! Jarak {$check['distance']} KM terlalu jauh.")->withInput();

        DB::table('alamat_pengiriman')
            ->where('id_alamat', $id)
            ->where('id_user', Auth::id())
            ->update([
                'label' => $request->label,
                'penerima' => $request->penerima,
                'no_hp_penerima' => $request->no_hp_penerima,
                'detail_alamat' => $request->detail_alamat,
                'latitude' => $check['lat'] ?? null,
                'longitude' => $check['lng'] ?? null,
                'plus_code' => $check['clean_code'],
                'updated_at' => now(),
            ]);

        return back()->with('success', 'Alamat diperbarui!');
    }

    public function setPrimaryAddress($id)
    {
        // 1. Reset semua jadi 0
        DB::table('alamat_pengiriman')->where('id_user', Auth::id())->update(['is_primary' => 0]);
        // 2. Set yang dipilih jadi 1
        DB::table('alamat_pengiriman')->where('id_alamat', $id)->where('id_user', Auth::id())->update(['is_primary' => 1]);

        return back()->with('success', 'Alamat utama berhasil diganti!');
    }

    public function deleteAddress($id)
    {
        DB::table('alamat_pengiriman')->where('id_alamat', $id)->where('id_user', Auth::id())->delete();
        return back()->with('success', 'Alamat dihapus.');
    }

    // === KERANJANG UTILS ===
    public function emptyCart()
    {
        Keranjang::where('id_user', Auth::id())->delete();
        return back()->with('success', 'Keranjang dikosongkan!');
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
        if ($item) {
            if ($item->jumlah > 1) $item->decrement('jumlah');
            else $item->delete();
        }
        return back();
    }

    public function removeItem($id)
    {
        Keranjang::where('id_user', Auth::id())->where('id_produk', $id)->delete();
        return back();
    }

    public function trackingPage($id)
    {
        $trx = Transaksi::with('detailTransaksi.produk')->findOrFail($id);
        if ($trx->status == 'Menunggu Pembayaran') {
            return redirect()->route('kiosk.riwayat')->with('error', 'Pesanan belum diproses.');
        }
        return view('kiosk.tracking', compact('trx'));
    }
}
