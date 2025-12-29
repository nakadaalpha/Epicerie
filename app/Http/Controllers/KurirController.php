<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth; // Tambahkan ini biar aman

class KurirController extends Controller
{
    // Halaman Dashboard Kurir (Daftar Tugas)
    public function index()
    {
        // Ambil pesanan yang statusnya 'Dikemas' atau 'Dikirim'
        $tugas = Transaksi::with(['user', 'detailTransaksi']) // Tambah array [] biar rapi
                    ->whereIn('status', ['Dikemas', 'Dikirim'])
                    ->orderBy('created_at', 'desc')
                    ->get();

        return view('kurir.dashboard', compact('tugas'));
    }

    // Update Status jadi 'Dikirim' (Pas tombol Mulai ditekan)
    public function mulaiAntar($id)
    {
        $trx = Transaksi::findOrFail($id);
        
        // === PERBAIKAN DISINI ===
        // Selain ubah status, kita simpan juga ID Kurir yang lagi login
        $trx->update([
            'status' => 'Dikirim',
            'id_karyawan' => Auth::id() // <--- INI KUNCINYA!
        ]);
        
        return redirect()->back()->with('success', 'Status berubah jadi DIKIRIM. GPS Aktif!');
    }

    // Update Status jadi 'Selesai' (Pas sampai)
    public function selesaiAntar($id)
    {
        $trx = Transaksi::findOrFail($id);
        
        // Jaga-jaga kalau pas mulaiAntar tadi belum kesimpen (buat data lama)
        // Kita simpan lagi ID-nya pas selesai
        $dataUpdate = ['status' => 'Selesai'];
        
        if (empty($trx->id_karyawan)) {
            $dataUpdate['id_karyawan'] = Auth::id();
        }

        $trx->update($dataUpdate);

        // KIRIM NOTIFIKASI REALTIME KE PELANGGAN (Opsional, kalau event belum ada bisa dikomen dulu)
        // event(new \App\Events\StatusTransaksiUpdated($id, 'Selesai'));
        
        return redirect()->back()->with('success', 'Pekerjaan selesai! Mantap.');
    }
}