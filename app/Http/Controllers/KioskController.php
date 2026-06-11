<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Keranjang;
use App\Models\Produk;
use App\Models\Slider;
use App\Models\Ulasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KioskController extends Controller
{
    private function getCartSummary()
    {
        if (! Auth::check()) {
            return ['totalItemKeranjang' => 0, 'keranjangItems' => []];
        }
        $keranjangItems = Keranjang::where('id_user', Auth::id())->pluck('jumlah', 'id_produk')->toArray();

        return ['totalItemKeranjang' => array_sum($keranjangItems), 'keranjangItems' => $keranjangItems];
    }

    public function index()
    {
        try {
            $sliders = Slider::where('is_active', 1)->orderBy('urutan', 'asc')->get();
        } catch (\Exception $e) {
            $sliders = collect([]);
        }
        if ($sliders->isEmpty()) {
            $sliders = collect([
                (object) ['gambar' => 'https://placehold.co/1200x400/3b82f6/ffffff?text=Promo', 'judul' => 'Promo 1', 'is_dummy' => true],
            ]);
        }

        $produkTerbaru = Produk::select('produk.*', DB::raw('COALESCE(SUM(detail_transaksi.jumlah), 0) as total_terjual'))
            ->leftJoin('detail_transaksi', 'produk.id_produk', '=', 'detail_transaksi.id_produk')
            ->groupBy('produk.id_produk')
            ->orderBy('produk.created_at', 'desc')
            ->limit(6)
            ->get();

        $produkTerlaris = Produk::select('produk.*', DB::raw('COALESCE(SUM(detail_transaksi.jumlah), 0) as total_terjual'))
            ->leftJoin('detail_transaksi', 'produk.id_produk', '=', 'detail_transaksi.id_produk')
            ->groupBy('produk.id_produk')
            ->orderBy('total_terjual', 'desc')
            ->limit(6)
            ->get();

        $produk = Produk::select('produk.*', DB::raw('COALESCE(SUM(detail_transaksi.jumlah), 0) as total_terjual'))
            ->leftJoin('detail_transaksi', 'produk.id_produk', '=', 'detail_transaksi.id_produk')
            ->groupBy('produk.id_produk')
            ->orderBy('produk.created_at', 'desc')
            ->paginate(12);

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

        if ($keyword) {
            $query->where('nama_produk', 'like', '%'.$keyword.'%');
        }
        if (! empty($selectedKategori)) {
            $query->whereIn('id_kategori', $selectedKategori);
        }
        if ($minPrice) {
            $query->where('harga_produk', '>=', $minPrice);
        }
        if ($maxPrice) {
            $query->where('harga_produk', '<=', $maxPrice);
        }

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

    public function show(Request $request, $id)
    {
        $produk = Produk::with('kategori')->findOrFail($id);

        $allReviews = Ulasan::where('id_produk', $id)->get();
        $totalUlasan = $allReviews->count();
        $avgRating = $totalUlasan > 0 ? $allReviews->avg('rating') : 0;

        $starCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        foreach ($allReviews as $u) {
            $starCounts[$u->rating]++;
        }

        $query = Ulasan::with('user')->where('id_produk', $id);
        if ($request->has('rating') && in_array($request->rating, [1, 2, 3, 4, 5])) {
            $query->where('rating', $request->rating);
        }
        $ulasan = $query->latest()->get();

        $totalTerjual = DB::table('detail_transaksi')->where('id_produk', $id)->sum('jumlah');

        $produkLain = Produk::select('produk.*', DB::raw('COALESCE(SUM(detail_transaksi.jumlah), 0) as total_terjual'))
            ->leftJoin('detail_transaksi', 'produk.id_produk', '=', 'detail_transaksi.id_produk')
            ->where('produk.id_produk', '!=', $id)
            ->where('produk.id_kategori', $produk->id_kategori)
            ->groupBy('produk.id_produk')
            ->orderBy('total_terjual', 'desc')
            ->limit(6)
            ->get();

        return view('kiosk.show', compact(
            'produk',
            'produkLain',
            'totalTerjual',
            'ulasan',
            'avgRating',
            'totalUlasan',
            'starCounts'
        ));
    }
}
