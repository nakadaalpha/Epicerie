<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\LokasiKurirUpdated;
use App\Events\StatusTransaksiUpdated; // <--- PANGGIL FILE BARU TADI

class KurirController extends Controller
{
    // public function index()
    // {
    //     $tugas = Transaksi::with(['user', 'detailTransaksi'])
    //         ->whereIn('status', ['Dikemas', 'diproses', 'Dikirim'])
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return view('kurir.dashboard', compact('tugas'));
    // }
    public function index()
    {
        // Mengambil SEMUA transaksi, diurutkan dari yang terbaru
        $tugas = \App\Models\Transaksi::with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('kurir.dashboard', compact('tugas'));
    }
    public function getDetailTransaksi($id)
    {
        // Ambil Data Transaksi
        $t = \App\Models\Transaksi::findOrFail($id);

        // Ambil Detail Produk
        $details = \Illuminate\Support\Facades\DB::table('detail_transaksi')
            ->join('produk', 'detail_transaksi.id_produk', '=', 'produk.id_produk')
            ->where('id_transaksi', $id)
            ->select('detail_transaksi.*', 'produk.nama_produk', 'produk.gambar')
            ->get();

        // Render View Partial
        return view('kurir.modal_detail', compact('t', 'details'));
    }

    public function mulaiAntar($id)
    {
        $trx = Transaksi::findOrFail($id);

        $trx->update([
            'status' => 'Dikirim',
            'id_karyawan' => Auth::id()
        ]);

        return redirect()->back()->with('success', 'Status berubah jadi DIKIRIM. GPS Aktif!');
    }

    public function selesaiAntar($id)
    {
        $trx = Transaksi::findOrFail($id);

        $dataUpdate = ['status' => 'Selesai'];

        if (empty($trx->id_karyawan)) {
            $dataUpdate['id_karyawan'] = Auth::id();
        }

        $trx->update($dataUpdate);

        // === INI YANG DITUNGGU-TUNGGU PELANGGAN ===
        // Kirim sinyal 'status-update' ke tracking.blade.php
        event(new \App\Events\StatusTransaksiUpdated($id, 'Selesai'));

        return redirect()->back()->with('success', 'Pekerjaan selesai! Mantap.');
    }

    // API Update Lokasi (Kodingan GPS)
    public function updateLokasi(Request $request)
    {
        $request->validate([
            'id_transaksi' => 'required',
            'lat' => 'required',
            'long' => 'required',
        ]);

        DB::table('transaksi')
            ->where('id_transaksi', $request->id_transaksi)
            ->update([
                'kurir_lat' => $request->lat,
                'kurir_long' => $request->long,
            ]);

        event(new LokasiKurirUpdated($request->id_transaksi, $request->lat, $request->long));

        return response()->json(['status' => 'Sukses!', 'message' => 'Lokasi tersimpan.']);
    }
}
