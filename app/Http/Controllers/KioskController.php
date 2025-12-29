<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
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
    // === 1. HALAMAN DEPAN (Bisa Diakses Guest, Tapi Cart 0) ===
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
        
        // Logika Baru: Kalau Login ambil cart, kalau Guest cart 0
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

    // === 3. TAMBAH KERANJANG (WAJIB LOGIN) ===
    public function addToCart(Request $request, $id)
    {
        // CEK LOGIN DULU
        if (!Auth::check()) {
            if($request->ajax()) {
                // Kirim kode khusus biar JS tau harus redirect login
                return response()->json(['status' => 'error', 'message' => 'Silakan login terlebih dahulu!', 'redirect' => route('login')]);
            }
            return redirect()->route('login')->with('error', 'Silakan login untuk berbelanja');
        }

        $produk = Produk::find($id);
        if ($produk->stok < 1) {
            if($request->ajax()) return response()->json(['status'=>'error', 'message'=>'Stok Habis!']);
            return back()->with('error', 'Stok Habis!');
        }
        
        $userId = Auth::id(); // Pasti ada isinya karena udah dicek
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

    // === 4. HALAMAN CHECKOUT (WAJIB LOGIN) ===
    public function checkout()
    {
        if (!Auth::check()) return redirect()->route('login');

        $userId = Auth::id();
        $keranjang = Keranjang::with('produk')->where('id_user', $userId)->get();
        
        if ($keranjang->isEmpty()) return redirect()->route('kiosk.index');
        
        $totalBayar = 0;
        foreach ($keranjang as $item) { $totalBayar += $item->produk->harga_produk * $item->jumlah; }
        
        return view('kiosk.checkout', compact('keranjang', 'totalBayar'));
    }

    // === 5. PROSES BAYAR (WAJIB LOGIN) ===
    public function processPayment(Request $request)
    {
        if (!Auth::check()) return response()->json(['error' => 'Unauthorized'], 401);

        $userId = Auth::id();
        $keranjang = Keranjang::with('produk')->where('id_user', $userId)->get();
        if ($keranjang->isEmpty()) return response()->json(['error' => 'Keranjang kosong'], 400);

        $totalBayar = 0;
        $itemDetails = [];
        foreach ($keranjang as $item) {
            $totalBayar += $item->produk->harga_produk * $item->jumlah;
            $itemDetails[] = [
                'id' => $item->id_produk,
                'price' => (int)$item->produk->harga_produk,
                'quantity' => (int)$item->jumlah,
                'name' => substr($item->produk->nama_produk, 0, 50)
            ];
        }

        // TUNAI
        if ($request->metode_pembayaran == 'Tunai') {
            DB::beginTransaction();
            try {
                $idTransaksi = DB::table('transaksi')->insertGetId([
                    'id_user_pembeli' => $userId,
                    'id_user_kasir' => null, // Online order, gak ada kasir
                    'kode_transaksi' => 'TRX-' . time(),
                    'total_bayar' => $totalBayar,
                    'metode_pembayaran' => 'Tunai',
                    'status' => 'Dikemas', // Default Status
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
            $user = Auth::user(); // Ambil data user asli buat dikirim ke Midtrans

            $params = [
                'transaction_details' => ['order_id' => 'MID-' . time() . rand(100,999), 'gross_amount' => $totalBayar],
                'item_details' => $itemDetails,
                'customer_details' => ['first_name' => $user->nama, 'email' => $user->email ?? 'user@epicerie.com', 'phone' => $user->no_hp ?? '08123456789']
            ];
            return response()->json(['snap_token' => Snap::getSnapToken($params)]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // === 6. MIDTRANS CALLBACK (WAJIB LOGIN) ===
    public function midtransSuccess(Request $request)
    {
        if (!Auth::check()) return response()->json(['error' => 'Unauthorized'], 401);
        $userId = Auth::id();
        
        $keranjang = Keranjang::with('produk')->where('id_user', $userId)->get();
        if ($keranjang->isEmpty()) return response()->json(['status' => 'success']);

        DB::beginTransaction();
        try {
            $totalBayar = 0;
            foreach ($keranjang as $item) { $totalBayar += $item->produk->harga_produk * $item->jumlah; }

            $idTransaksi = DB::table('transaksi')->insertGetId([
                'id_user_pembeli' => $userId,
                'id_user_kasir' => null,
                'kode_transaksi' => 'TRX-MID-' . time(),
                'total_bayar' => $totalBayar,
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

    // === 7. RIWAYAT TRANSAKSI (WAJIB LOGIN) ===
    public function riwayatTransaksi(Request $request)
    {
        if (!Auth::check()) return redirect()->route('login');

        $user = Auth::user();
        
        // 1. Ambil status dari URL (misal: .../riwayat?status=Dikirim)
        $status = $request->query('status');

        // 2. Siapkan Query Dasar
        $query = Transaksi::with(['detailTransaksi.produk'])
            ->where('id_user_pembeli', $user->id_user); // Sesuaikan id_user atau id

        // 3. Kalau ada status dipilih, filter datanya
        if ($status) {
            $query->where('status', $status);
        }

        // 4. Ambil Data
        $riwayat = $query->orderBy('created_at', 'desc')->get();

        return view('kiosk.riwayat', compact('riwayat', 'user'));
    }

    // === 8. HALAMAN STRUK (Bisa diakses siapa aja asal punya link ID, atau mau dibatesin?) ===
    // Lebih aman kalau cuma yg punya transaksi yg bisa liat
    public function successPage($id)
    {
        $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->first();
        if (!$transaksi) return redirect()->route('kiosk.index');
        
        // Opsional: Cek apakah yang buka halaman ini adalah pemilik transaksi
        if (Auth::check() && $transaksi->id_user_pembeli != Auth::id()) {
             return redirect()->route('kiosk.index')->with('error', 'Akses ditolak');
        }

        $details = DB::table('detail_transaksi')
            ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id_produk')
            ->where('id_transaksi', $id)
            ->get();

        return view('kiosk.success', compact('transaksi', 'details'));
    }

    // === FUNGSI LAINNYA (WAJIB LOGIN) ===
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
    
    // Ini halaman profil yang kemarin lu kirim
    // --- 1. HALAMAN PROFILE ---
    public function profile()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Ambil alamat dari tabel baru
        $alamat = \Illuminate\Support\Facades\DB::table('alamat_pengiriman')
                    ->where('id_user', $user->id_user)
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
        return view('kiosk.profile', compact('user', 'alamat'));
    }

    // --- 2. UPDATE BIODATA (Nama, Email, HP) ---
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
        // Username sengaja gak diupdate biar gak error login
        $user->save();

        return back()->with('success', 'Biodata berhasil diperbarui!');
    }

    // --- 3. UPDATE FOTO PROFIL ---
    public function updatePhoto(Request $request)
    {
        $request->validate(['foto_profil' => 'required|image|max:2048']);
        $user = \App\Models\User::find(\Illuminate\Support\Facades\Auth::id());

        if ($request->hasFile('foto_profil')) {
            // Hapus foto lama kalau ada
            if ($user->foto_profil) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->foto_profil);
            }
            // Simpan foto baru
            $path = $request->file('foto_profil')->store('profiles', 'public');
            $user->foto_profil = $path;
            $user->save();
        }

        return back()->with('success', 'Foto profil diperbarui!');
    }

    // --- 4. TAMBAH ALAMAT BARU ---
    public function addAddress(Request $request)
    {
        $request->validate([
            'label' => 'required|string',
            'penerima' => 'required|string',
            'no_hp_penerima' => 'required',
            'detail_alamat' => 'required|string',
        ]);

        \Illuminate\Support\Facades\DB::table('alamat_pengiriman')->insert([
            'id_user' => \Illuminate\Support\Facades\Auth::id(),
            'label' => $request->label,
            'penerima' => $request->penerima,
            'no_hp_penerima' => $request->no_hp_penerima,
            'detail_alamat' => $request->detail_alamat,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Alamat baru ditambahkan!');
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

    // Placeholder
    public function addPacketToCart($key) { return back(); }
    public function holdOrder() { return back(); }
    public function listPending() { return view('kiosk.pending'); }
    public function recallOrder($id) { return back(); }
    public function setCartQuantity() { return back(); }

    public function trackingPage($id)
    {
        // Ambil data transaksi berdasarkan ID
        // Pastikan model Transaksi sudah punya relasi ke 'detailTransaksi' kalau mau nampilin barang
        $trx = \App\Models\Transaksi::with('detailTransaksi.produk')->findOrFail($id);

        // Cek status, kalau masih 'Menunggu Pembayaran' lempar balik aja
        if ($trx->status == 'Menunggu Pembayaran') {
            return redirect()->route('kiosk.riwayat')->with('error', 'Pesanan belum diproses.');
        }

        return view('kiosk.tracking', compact('trx'));
    }

}