<?php

namespace App\Http\Controllers;

use App\Models\Keranjang;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
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

    public function cart()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }
        $keranjang = Keranjang::with('produk')->where('id_user', Auth::id())->get();
        $subtotal = $this->hitungSubtotal($keranjang);
        $minBelanja = self::MIN_BELANJA;
        $rekomendasi = Produk::inRandomOrder()->limit(6)->get();

        return view('kiosk.cart', compact('keranjang', 'subtotal', 'minBelanja', 'rekomendasi'));
    }

    public function addToCart(Request $request, $id)
    {
        if (! Auth::check()) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Login dulu!', 'redirect' => route('login')]);
            }

            return redirect()->route('login');
        }

        $produk = Produk::find($id);
        if (! $produk || $produk->stok < 1) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Stok Habis!']);
            }

            return back()->with('error', 'Stok Habis!');
        }

        $userId = Auth::id();
        $qty = $request->input('qty', 1);
        $currentCart = Keranjang::where('id_user', $userId)->where('id_produk', $id)->first();
        $currentQty = $currentCart ? $currentCart->jumlah : 0;

        if (($currentQty + $qty) > $produk->stok) {
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Stok tidak cukup!']);
            }

            return back()->with('error', 'Stok tidak cukup!');
        }

        $cart = Keranjang::firstOrNew(['id_user' => $userId, 'id_produk' => $id]);
        $cart->jumlah += $qty;
        $cart->save();

        if ($request->type == 'now') {
            return redirect()->route('kiosk.cart');
        }
        if ($request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Berhasil masuk keranjang', 'total_cart' => Keranjang::where('id_user', $userId)->sum('jumlah')]);
        }

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
        if ($item && $item->produk->stok > $item->jumlah) {
            $item->increment('jumlah');
        }

        return back();
    }

    public function decreaseItem($id)
    {
        $item = Keranjang::where('id_user', Auth::id())->where('id_produk', $id)->first();
        if ($item) {
            $item->jumlah > 1 ? $item->decrement('jumlah') : $item->delete();
        }

        return back();
    }

    public function removeItem($id)
    {
        Keranjang::where('id_user', Auth::id())->where('id_produk', $id)->delete();

        return back();
    }
}
