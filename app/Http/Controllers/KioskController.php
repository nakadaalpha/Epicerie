<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http; 
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
    // === KONFIGURASI LOKASI TOKO (Sleman) ===
    private $shopLat = -7.73326; 
    private $shopLng = 110.33121;
    private $maxDistanceKm = 3.0;

    // === 1. HALAMAN DEPAN ===
    public function index(Request $request)
    {
        $query = Produk::query();

        if ($request->has('search') && $request->search != '') {
            $query->where('nama_produk', 'LIKE', '%' . $request->search . '%');
        }

        if ($request->has('kategori') && $request->kategori != 'semua') {
            $query->where('id_kategori', $request->kategori);
        }

        $produk = $query->orderBy('nama_produk', 'asc')->limit(20)->get();
        $kategoriList = Kategori::all();
        
        $totalItemKeranjang = 0;
        $keranjangItems = [];

        if (Auth::check()) {
            $userId = Auth::id();
            $keranjangItems = Keranjang::where('id_user', $userId)->pluck('jumlah', 'id_produk')->toArray();
            $totalItemKeranjang = array_sum($keranjangItems);
        }

        return view('kiosk.index', compact('produk', 'totalItemKeranjang', 'keranjangItems', 'kategoriList'));
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

        $totalItemKeranjang = 0;
        if (Auth::check()) {
            $userId = Auth::id();
            $keranjangItems = Keranjang::where('id_user', $userId)->pluck('jumlah', 'id_produk')->toArray();
            $totalItemKeranjang = array_sum($keranjangItems);
        }

        return view('kiosk.show', compact('produk', 'produkLain', 'totalItemKeranjang'));
    }

    // === 3. TAMBAH KERANJANG ===
    public function addToCart(Request $request, $id)
    {
        if (!Auth::check()) {
            if($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Silakan login terlebih dahulu!', 'redirect' => route('login')]);
            }
            return redirect()->route('login')->with('error', 'Silakan login untuk berbelanja');
        }

        $produk = Produk::find($id);
        if ($produk->stok < 1) {
            if($request->ajax()) return response()->json(['status'=>'error', 'message'=>'Stok Habis!']);
            return back()->with('error', 'Stok Habis!');
        }
        
        $userId = Auth::id();
        $qty = $request->qty ? $request->qty : 1; 

        $cek = Keranjang::where('id_user', $userId)->where('id_produk', $id)->first();
        if ($cek) { 
            $cek->jumlah += $qty; 
            $cek->save(); 
        } else { 
            Keranjang::create(['id_user' => $userId, 'id_produk' => $id, 'jumlah' => $qty]); 
        }
        
        if($request->type == 'now') return redirect()->route('kiosk.checkout');
        
        if($request->ajax()) {
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
        
        $subtotal = 0;
        foreach ($keranjang as $item) { 
            $subtotal += $item->produk->harga_produk * $item->jumlah; 
        }

        $ongkir = 5000;
        $minBelanja = 30000;
        $totalBayar = $subtotal + $ongkir;

        $daftarAlamat = DB::table('alamat_pengiriman')
                        ->where('id_user', $userId)
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

        if ($subtotal < 30000) {
            return response()->json(['error' => 'Minimal belanja Rp 30.000 untuk diproses.'], 400);
        }

        $ongkir = 5000;
        $grandTotal = $subtotal + $ongkir;

        $itemDetails[] = [
            'id' => 'ONGKIR',
            'price' => (int)$ongkir,
            'quantity' => 1,
            'name' => 'Ongkir Kurir (Max 3KM)'
        ];

        $idAlamat = $request->id_alamat;
        if (!$idAlamat) {
            $lastAddr = DB::table('alamat_pengiriman')->where('id_user', $userId)->orderBy('created_at', 'desc')->first();
            $idAlamat = $lastAddr ? $lastAddr->id_alamat : null;
        }

        if ($request->metode_pembayaran == 'Tunai') {
            DB::beginTransaction();
            try {
                $idTransaksi = DB::table('transaksi')->insertGetId([
                    'id_user_pembeli' => $userId,
                    'id_user_kasir' => null,
                    'id_alamat' => $idAlamat,
                    'kode_transaksi' => 'TRX-' . time(),
                    'total_bayar' => $grandTotal,
                    'ongkos_kirim' => $ongkir,
                    'metode_pembayaran' => 'Tunai',
                    'status' => 'diproses',
                    'tanggal_transaksi' => now(),
                    'created_at' => now(), 'updated_at' => now()
                ]);

                foreach ($keranjang as $item) {
                    DB::table('detail_transaksi')->insert([
                        'id_transaksi' => $idTransaksi,
                        'id_produk' => $item->id_produk,
                        'jumlah' => $item->jumlah,
                        'harga_produk_saat_beli' => $item->produk->harga_produk,
                        'created_at' => now(), 'updated_at' => now()
                    ]);
                    Produk::find($item->id_produk)->decrement('stok', $item->jumlah);
                }
                Keranjang::where('id_user', $userId)->delete();
                DB::commit();

                return response()->json(['status' => 'success', 'redirect_url' => route('kiosk.success', $idTransaksi)]);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        // MIDTRANS
        try {
            Config::$serverKey = config('midtrans.server_key');
            Config::$isProduction = config('midtrans.is_production');
            Config::$isSanitized = true; Config::$is3ds = true;
            $user = Auth::user(); 

            $params = [
                'transaction_details' => ['order_id' => 'MID-' . time() . rand(100,999), 'gross_amount' => $grandTotal],
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
        if ($keranjang->isEmpty()) return response()->json(['status' => 'success']);

        $idAlamat = $request->id_alamat;
        if (!$idAlamat) {
            $lastAddr = DB::table('alamat_pengiriman')->where('id_user', $userId)->orderBy('created_at', 'desc')->first();
            $idAlamat = $lastAddr ? $lastAddr->id_alamat : null;
        }

        DB::beginTransaction();
        try {
            $subtotal = 0;
            foreach ($keranjang as $item) { $subtotal += $item->produk->harga_produk * $item->jumlah; }

            $ongkir = 5000;
            $grandTotal = $subtotal + $ongkir;

            $idTransaksi = DB::table('transaksi')->insertGetId([
                'id_user_pembeli' => $userId,
                'id_user_kasir' => null,
                'id_alamat' => $idAlamat,
                'kode_transaksi' => 'TRX-MID-' . time(),
                'total_bayar' => $grandTotal,
                'ongkos_kirim' => $ongkir,
                'metode_pembayaran' => 'Midtrans',
                'status' => 'Dikemas',
                'tanggal_transaksi' => now(),
                'created_at' => now(), 'updated_at' => now()
            ]);

            foreach ($keranjang as $item) {
                DB::table('detail_transaksi')->insert([
                    'id_transaksi' => $idTransaksi,
                    'id_produk' => $item->id_produk,
                    'jumlah' => $item->jumlah,
                    'harga_produk_saat_beli' => $item->produk->harga_produk,
                    'created_at' => now(), 'updated_at' => now()
                ]);
                Produk::find($item->id_produk)->decrement('stok', $item->jumlah);
            }

            Keranjang::where('id_user', $userId)->delete();
            DB::commit();
            return response()->json(['status' => 'success', 'id_transaksi' => $idTransaksi]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // === 7. RIWAYAT TRANSAKSI ===
    public function riwayatTransaksi(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');

        $user = Auth::user();
        $status = $request->query('status');
        $query = Transaksi::with(['detailTransaksi.produk'])->where('id_user_pembeli', $user->id_user);

        if ($status) {
            $query->where('status', $status);
        }

        $riwayat = $query->orderBy('created_at', 'desc')->get();
        return view('kiosk.riwayat', compact('riwayat', 'user'));
    }

    // === 8. HALAMAN STRUK ===
    public function successPage($id)
    {
        $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->first();
        if (!$transaksi) return redirect()->route('kiosk.index');
        
        if (Auth::check() && $transaksi->id_user_pembeli != Auth::id()) {
             return redirect()->route('kiosk.index')->with('error', 'Akses ditolak');
        }

        $details = DB::table('detail_transaksi')
            ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id_produk')
            ->where('id_transaksi', $id)
            ->get();

        return view('kiosk.success', compact('transaksi', 'details'));
    }

    // === FUNGSI LAINNYA ===
    public function emptyCart()
    {
        if (!Auth::check()) return redirect()->route('login');
        Keranjang::where('id_user', Auth::id())->delete();
        return back()->with('success', 'Keranjang berhasil dikosongkan!');
    }

    public function increaseItem($id) { 
        if (!Auth::check()) return redirect()->route('login');
        $item = Keranjang::where('id_user', Auth::id())->where('id_produk', $id)->first();
        $p = Produk::find($id);
        if ($item && $p->stok > $item->jumlah) $item->increment('jumlah');
        return back();
    }
    public function decreaseItem($id) {
        if (!Auth::check()) return redirect()->route('login');
        $item = Keranjang::where('id_user', Auth::id())->where('id_produk', $id)->first();
        if ($item) { if ($item->jumlah > 1) $item->decrement('jumlah'); else $item->delete(); }
        return back();
    }
    public function removeItem($id) {
        if (!Auth::check()) return redirect()->route('login');
        Keranjang::where('id_user', Auth::id())->where('id_produk', $id)->delete();
        return back();
    }
    
    // --- 1. HALAMAN PROFILE ---
    public function profile()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $alamat = \Illuminate\Support\Facades\DB::table('alamat_pengiriman')
                    ->where('id_user', $user->id_user)
                    ->orderBy('created_at', 'desc')
                    ->get();
        return view('kiosk.profile', compact('user', 'alamat'));
    }

    // --- 2. UPDATE BIODATA ---
    public function updateProfile(Request $request)
    {
        $user = \App\Models\User::find(\Illuminate\Support\Facades\Auth::id());
        
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'nullable|email',
            'no_hp' => 'nullable|numeric',
        ]);

        $user->nama = $request->nama;
        $user->email = $request->email;
        $user->no_hp = $request->no_hp;
        $user->save();

        return back()->with('success', 'Biodata berhasil diperbarui!');
    }

    // --- 3. UPDATE FOTO PROFIL ---
    public function updatePhoto(Request $request)
    {
        $request->validate(['foto_profil' => 'required|image|max:2048']);
        $user = \App\Models\User::find(\Illuminate\Support\Facades\Auth::id());

        if ($request->hasFile('foto_profil')) {
            if ($user->foto_profil) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->foto_profil);
            }
            $path = $request->file('foto_profil')->store('profiles', 'public'); 
            $user->foto_profil = $path;
            $user->save();
        }

        return back()->with('success', 'Foto profil diperbarui!');
    }

    // === FUNGSI BANTUAN: HITUNG JARAK (FAIL-SAFE) ===
    private function validateDistance($plusCodeInput) 
    {
        // 1. Bersihkan Kode Plus (Ambil kode-nya saja, huruf besar)
        $cleanCode = strtoupper(trim($plusCodeInput));
        // Ambil hanya 4-8 karakter + 2 karakter (Contoh: 78F6+R2)
        preg_match('/[A-Z0-9]{4,8}\+[A-Z0-9]{2,}/', $cleanCode, $matches);
        $cleanCode = !empty($matches) ? $matches[0] : $cleanCode;

        try {
            // 2. HTTP Request ke Nominatim
            $response = Http::withoutVerifying() // Bypass SSL Localhost
                ->timeout(5) // Jangan lama-lama loading
                ->withHeaders([
                    'User-Agent' => 'EpicerieApp/1.0 (admin@epicerie.com)'
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $cleanCode . ' Sleman, Yogyakarta', 
                    'format' => 'json',
                    'limit' => 1
                ]);

            if ($response->failed() || empty($response->json())) {
                // FAIL-SAFE: Kalau API Error/Gagal, TERIMA SAJA (Anggap Valid)
                return ['valid' => true, 'bypass' => true, 'clean_code' => $cleanCode];
            }

            $data = $response->json();

            if (!empty($data) && isset($data[0]['lat']) && isset($data[0]['lon'])) {
                $latUser = $data[0]['lat'];
                $lngUser = $data[0]['lon'];

                // Hitung Jarak (Haversine Formula)
                $theta = $this->shopLng - $lngUser;
                $dist = sin(deg2rad($this->shopLat)) * sin(deg2rad($latUser)) +  cos(deg2rad($this->shopLat)) * cos(deg2rad($latUser)) * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $miles = $dist * 60 * 1.1515;
                $km = $miles * 1.609344;

                return [
                    'valid' => $km <= $this->maxDistanceKm,
                    'distance' => round($km, 2),
                    'lat' => $latUser,
                    'lng' => $lngUser,
                    'clean_code' => $cleanCode 
                ];
            }
        } catch (\Exception $e) {
            // FAIL-SAFE: Kalau ada error Exception, TERIMA SAJA
            return ['valid' => true, 'bypass' => true, 'clean_code' => $cleanCode];
        }

        // Kalau sampai sini berarti format salah total
        return ['valid' => false, 'error' => 'Format kode salah.'];
    }

    // --- 4. TAMBAH ALAMAT BARU ---
    public function addAddress(Request $request)
    {
        $request->validate([
            'label' => 'required|string',
            'penerima' => 'required|string',
            'no_hp_penerima' => 'required',
            'detail_alamat' => 'required|string',
            'plus_code' => 'required|string'
        ]);

        // CEK JARAK
        $check = $this->validateDistance($request->plus_code);

        if (isset($check['error'])) {
            return back()->with('error', $check['error'])->withInput();
        }

        if (!$check['valid']) {
            return back()->with('error', "Maaf, lokasi terlalu jauh ({$check['distance']} KM). Maksimal jarak pengiriman adalah {$this->maxDistanceKm} KM.")->withInput();
        }

        // Siapkan Data
        $lat = isset($check['lat']) ? $check['lat'] : null;
        $lng = isset($check['lng']) ? $check['lng'] : null;
        $msg = isset($check['bypass']) ? 'Alamat disimpan (Peta Offline)' : "Alamat disimpan! Jarak: {$check['distance']} KM";

        \Illuminate\Support\Facades\DB::table('alamat_pengiriman')->insert([
            'id_user' => \Illuminate\Support\Facades\Auth::id(),
            'label' => $request->label,
            'penerima' => $request->penerima,
            'no_hp_penerima' => $request->no_hp_penerima,
            'detail_alamat' => $request->detail_alamat,
            'latitude' => $lat,   
            'longitude' => $lng, 
            'plus_code' => $check['clean_code'], 
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', $msg);
    }

    // --- UPDATE ALAMAT ---
    public function updateAddress(Request $request, $id)
    {
        $request->validate([
            'label' => 'required|string',
            'penerima' => 'required|string',
            'no_hp_penerima' => 'required',
            'detail_alamat' => 'required|string',
            'plus_code' => 'required|string'
        ]);

        // CEK JARAK
        $check = $this->validateDistance($request->plus_code);

        if (isset($check['error'])) {
            return back()->with('error', $check['error'])->withInput();
        }

        if (!$check['valid']) {
            return back()->with('error', "Update Gagal! Lokasi baru terlalu jauh ({$check['distance']} KM). Batas maks: {$this->maxDistanceKm} KM.")->withInput();
        }

        $lat = isset($check['lat']) ? $check['lat'] : null;
        $lng = isset($check['lng']) ? $check['lng'] : null;
        $msg = isset($check['bypass']) ? 'Alamat diupdate (Peta Offline)' : "Alamat diupdate! Jarak: {$check['distance']} KM";

        DB::table('alamat_pengiriman')
            ->where('id_alamat', $id)
            ->where('id_user', Auth::id())
            ->update([
                'label' => $request->label,
                'penerima' => $request->penerima,
                'no_hp_penerima' => $request->no_hp_penerima,
                'detail_alamat' => $request->detail_alamat,
                'latitude' => $lat,
                'longitude' => $lng,
                'plus_code' => $check['clean_code'],
                'updated_at' => now(),
            ]);

        return back()->with('success', $msg);
    }

    // --- PILIH ALAMAT UTAMA ---
    public function setPrimaryAddress($id)
    {
        $userId = Auth::id();
        try {
            DB::beginTransaction();
            DB::table('alamat_pengiriman')->where('id_user', $userId)->update(['is_active' => 0]);
            DB::table('alamat_pengiriman')
                ->where('id_alamat', $id)
                ->where('id_user', $userId)
                ->update(['is_active' => 1]);
            DB::commit();
            return back()->with('success', 'Alamat utama berhasil diganti!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal set utama');
        }
    }
    
    // --- 5. HAPUS ALAMAT ---
    public function deleteAddress($id)
    {
        \Illuminate\Support\Facades\DB::table('alamat_pengiriman')
            ->where('id_alamat', $id)
            ->where('id_user', \Illuminate\Support\Facades\Auth::id())
            ->delete();
        return back()->with('success', 'Alamat dihapus.');
    } 

    public function recallOrder($id) { return back(); }
    public function setCartQuantity() { return back(); }

    public function trackingPage($id)
    {
        $trx = \App\Models\Transaksi::with('detailTransaksi.produk')->findOrFail($id);
        if ($trx->status == 'Menunggu Pembayaran') {
            return redirect()->route('kiosk.riwayat')->with('error', 'Pesanan belum diproses.');
        }
        return view('kiosk.tracking', compact('trx'));
    }
}