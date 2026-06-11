<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressRequest;
use App\Http\Requests\UpdatePhotoRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\AlamatPengiriman;
use App\Models\Transaksi;
use App\Models\Ulasan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function profile()
    {
        $user = Auth::user();
        $userId = $user->id_user;

        $alamat = AlamatPengiriman::where('id_user', $userId)->orderBy('is_primary', 'desc')->get();

        $riwayatUlasan = Ulasan::with('produk')
            ->where('id_user', $userId)
            ->latest()
            ->get();

        $menungguUlasan = DB::table('detail_transaksi')
            ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
            ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id_produk')
            ->where('transaksi.id_user_pembeli', $userId)
            ->where('transaksi.status', 'selesai')
            ->whereNotExists(function ($query) use ($userId) {
                $query->select(DB::raw(1))
                    ->from('ulasan')
                    ->whereRaw('ulasan.id_produk = detail_transaksi.id_produk')
                    ->where('ulasan.id_user', $userId);
            })
            ->select('produk.*', 'transaksi.created_at as tgl_beli')
            ->distinct()
            ->get();

        return view('kiosk.profile', compact('user', 'alamat', 'riwayatUlasan', 'menungguUlasan'));
    }

    public function viewMemberCard()
    {
        $user = Auth::user();

        return view('kiosk.member-card', compact('user'));
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = User::find(Auth::id());
        $user->update($request->validated());

        return back()->with('success', 'Data profil diperbarui!');
    }

    public function updatePhoto(UpdatePhotoRequest $request)
    {
        $user = User::find(Auth::id());
        if ($request->hasFile('foto_profil')) {
            if ($user->foto_profil) {
                Storage::disk('public')->delete($user->foto_profil);
            }
            $user->update(['foto_profil' => Cloudinary::upload($request->file('foto_profil')->getRealPath(), ['folder' => 'profiles'])->getSecurePath()]);
        }

        return back()->with('success', 'Foto profil diperbarui!');
    }

    public function downloadQr()
    {
        $user = auth()->user();
        $customPaper = [0, 0, 242.64, 153.01];
        $pdf = Pdf::loadView('pdf.member-card', compact('user'));
        $pdf->setPaper($customPaper, 'landscape');

        return $pdf->download('MemberCard-'.$user->username.'.pdf');
    }

    public function requestCetakKartu()
    {
        $user = auth()->user();
        if ($user->status_cetak_kartu == 'pending') {
            return back()->with('error', 'Permintaan Anda sedang diproses oleh admin.');
        }
        if ($user->status_cetak_kartu == 'completed') {
            $user->update(['status_cetak_kartu' => 'pending']);

            return back()->with('success', 'Permintaan cetak ulang berhasil dikirim.');
        }
        $user->update(['status_cetak_kartu' => 'pending']);

        return back()->with('success', 'Permintaan cetak kartu fisik berhasil dikirim!');
    }

    public function ulasanPage(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'menunggu');

        $menungguUlasan = [];
        $riwayatUlasan = [];

        if ($tab == 'menunggu') {
            $menungguUlasan = DB::table('detail_transaksi')
                ->join('transaksi', 'detail_transaksi.id_transaksi', '=', 'transaksi.id_transaksi')
                ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id_produk')
                ->where('transaksi.id_user_pembeli', $user->id_user)
                ->where('transaksi.status', 'selesai')
                ->whereNotExists(function ($query) use ($user) {
                    $query->select(DB::raw(1))
                        ->from('ulasan')
                        ->whereRaw('ulasan.id_produk = detail_transaksi.id_produk')
                        ->where('ulasan.id_user', $user->id_user);
                })
                ->select(
                    'produk.id_produk',
                    'produk.nama_produk',
                    'produk.gambar',
                    DB::raw('MAX(transaksi.created_at) as tgl_beli')
                )
                ->groupBy('produk.id_produk', 'produk.nama_produk', 'produk.gambar')
                ->get();
        } else {
            $riwayatUlasan = Ulasan::with('produk')
                ->where('id_user', $user->id_user)
                ->latest()
                ->get();
        }

        return view('kiosk.ulasan', compact('menungguUlasan', 'riwayatUlasan', 'tab'));
    }

    public function addAddress(AddressRequest $request)
    {
        AlamatPengiriman::create(array_merge($request->validated(), ['id_user' => Auth::id()]));

        return back()->with('success', 'Alamat disimpan!');
    }

    public function updateAddress(AddressRequest $request, $id)
    {
        $alamat = AlamatPengiriman::where('id_alamat', $id)->where('id_user', Auth::id())->firstOrFail();
        $alamat->update($request->validated());

        return back()->with('success', 'Alamat diperbarui!');
    }

    public function deleteAddress($id)
    {
        AlamatPengiriman::where('id_alamat', $id)->where('id_user', Auth::id())->delete();

        return back();
    }

    public function setPrimaryAddress($id)
    {
        AlamatPengiriman::where('id_user', Auth::id())->update(['is_primary' => 0]);
        AlamatPengiriman::where('id_alamat', $id)->where('id_user', Auth::id())->update(['is_primary' => 1]);

        return back();
    }

    public function riwayatTransaksi(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }
        $query = Transaksi::with(['detailTransaksi.produk'])->where('id_user_pembeli', Auth::id());
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $riwayat = $query->orderBy('created_at', 'desc')->get();

        return view('kiosk.riwayat', compact('riwayat'));
    }

    public function trackingPage($id)
    {
        $trx = Transaksi::with('detailTransaksi.produk')->where('id_transaksi', $id)->where('id_user_pembeli', Auth::id())->firstOrFail();

        return view('kiosk.tracking', compact('trx'));
    }
}
