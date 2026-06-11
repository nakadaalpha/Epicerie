<?php

namespace App\Http\Controllers;

use App\Models\AlamatPengiriman;
use App\Models\Keranjang;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\Ulasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Midtrans\Config;
use Midtrans\Snap;

class CheckoutController extends Controller
{
    const ONGKIR_FLAT = 5000;

    const MIN_BELANJA = 30000;

    private function hitungSubtotal($keranjang)
    {
        return $keranjang->sum(function ($item) {
            $harga = $item->produk->harga_produk ?? 0;
            $diskon = $item->produk->persen_diskon ?? 0;
            $hargaFinal = $harga - ($harga * ($diskon / 100));

            return $hargaFinal * $item->jumlah;
        });
    }

    public function checkoutPage()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }
        $user = Auth::user();
        $keranjang = Keranjang::with('produk')->where('id_user', $user->id_user)->get();
        if ($keranjang->isEmpty()) {
            return redirect()->route('kiosk.cart');
        }

        $subtotal = $this->hitungSubtotal($keranjang);
        if ($subtotal < self::MIN_BELANJA) {
            return redirect()->route('kiosk.cart')->with('error', 'Belanja kurang dari Rp '.number_format(self::MIN_BELANJA));
        }

        $daftarAlamat = AlamatPengiriman::where('id_user', $user->id_user)->orderBy('is_primary', 'desc')->orderBy('created_at', 'desc')->get();
        $ongkirKurir = ($user->membership == 'Gold') ? 0 : self::ONGKIR_FLAT;

        return view('kiosk.checkout', compact('keranjang', 'subtotal', 'daftarAlamat', 'ongkirKurir', 'user'));
    }

    public function processPayment(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user = Auth::user();

        $keranjang = Keranjang::with('produk')->where('id_user', $user->id_user)->get();
        if ($keranjang->isEmpty()) {
            return response()->json(['error' => 'Keranjang kosong'], 400);
        }

        $subtotal = 0;
        foreach ($keranjang as $item) {
            if ($item->produk->stok < $item->jumlah) {
                return response()->json(['error' => "Stok {$item->produk->nama_produk} kurang."], 400);
            }
            $harga = $item->produk->harga_produk;
            $diskon = $item->produk->persen_diskon ?? 0;
            $subtotal += ($harga - ($harga * ($diskon / 100))) * $item->jumlah;
        }

        if ($subtotal < self::MIN_BELANJA) {
            return response()->json(['error' => 'Min belanja kurang'], 400);
        }

        $tipePengiriman = $request->input('tipe_pengiriman', 'delivery');
        $idAlamat = $request->id_alamat;
        $ongkir = 0;

        if ($tipePengiriman == 'delivery') {
            if (empty($idAlamat)) {
                return response()->json(['error' => 'Pilih alamat pengiriman.'], 400);
            }
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
            $params = ['transaction_details' => ['order_id' => 'MID-'.time().rand(100, 999), 'gross_amount' => (int) $grandTotal], 'customer_details' => ['first_name' => $user->nama, 'email' => $user->email, 'phone' => $user->no_hp]];

            return response()->json(['snap_token' => Snap::getSnapToken($params)]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function midtransSuccess(Request $request)
    {
        if (! Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $user = Auth::user();
        $keranjang = Keranjang::with('produk')->where('id_user', $user->id_user)->get();
        if ($keranjang->isEmpty()) {
            return response()->json(['status' => 'success']);
        }

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
                'kode_transaksi' => 'TRX-'.time(),
                'total_bayar' => $total,
                'ongkos_kirim' => $ongkir,
                'metode_pembayaran' => $metode,
                'status' => $status,
                'tanggal_transaksi' => now(),
                'created_at' => now(),
                'updated_at' => now(),
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
                    'updated_at' => now(),
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

    public function successPage($id)
    {
        $transaksi = DB::table('transaksi')->where('id_transaksi', $id)->first();

        if (! $transaksi || (Auth::check() && $transaksi->id_user_pembeli != Auth::id())) {
            return redirect()->route('kiosk.index');
        }

        $details = DB::table('detail_transaksi')
            ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id_produk')
            ->where('id_transaksi', $id)
            ->select('detail_transaksi.*', 'produk.nama_produk', 'produk.gambar', 'produk.id_produk')
            ->get();

        $reviewedProductIds = [];
        if (Auth::check()) {
            $reviewedProductIds = Ulasan::where('id_user', Auth::id())
                ->whereIn('id_produk', $details->pluck('id_produk'))
                ->pluck('id_produk')
                ->toArray();
        }

        return view('kiosk.success', compact('transaksi', 'details', 'reviewedProductIds'));
    }
}
