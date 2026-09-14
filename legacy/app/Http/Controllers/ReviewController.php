<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Ulasan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReviewController extends Controller
{
    public function storeReview(StoreReviewRequest $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();
        $produkId = $request->id_produk;

        $hasPurchased = DB::table('detail_transaksi')
            ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
            ->where('transaksi.id_user_pembeli', $userId)
            ->where('detail_transaksi.id_produk', $produkId)
            ->where('transaksi.status', 'selesai')
            ->exists();

        if (! $hasPurchased) {
            return back()->with('error', 'Anda harus membeli produk ini dan menyelesaikan pesanan sebelum memberi ulasan.');
        }

        $existingReview = Ulasan::where('id_user', $userId)->where('id_produk', $produkId)->exists();
        if ($existingReview) {
            return back()->with('error', 'Anda sudah memberikan ulasan untuk produk ini.');
        }

        Ulasan::create([
            'id_user' => $userId,
            'id_produk' => $produkId,
            'rating' => $request->rating,
            'komentar' => $request->komentar,
        ]);

        return back()->with('success', 'Terima kasih atas ulasan Anda!');
    }
}
