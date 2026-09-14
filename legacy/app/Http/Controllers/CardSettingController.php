<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View; // Import View yang benar

class CardSettingController extends Controller
{
    // 1. HALAMAN DAFTAR REQUEST (ANTRIAN CETAK)
    public function index(): View
    {
        $requests = User::where('status_cetak_kartu', 'pending')
            ->orderBy('updated_at', 'asc')
            ->get();

        // Sesuai struktur folder Anda: resources/views/card/index.blade.php
        return view('card.index', compact('requests')); 
    }

    // 2. HALAMAN PENGATURAN DESAIN (UPLOAD GAMBAR)
    public function settings(): View
    {
        // Anda perlu membuat file baru: resources/views/card/settings.blade.php
        return view('card.settings'); 
    }

    // 3. PROSES UPLOAD GAMBAR BACKGROUND
    public function updateSettings(Request $request)
    {
        $request->validate([
            'bg_front' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
            'bg_back'  => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        $path = public_path('images');

        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }

        if ($request->hasFile('bg_front')) {
            $request->file('bg_front')->move($path, 'card_bg.png');
        }

        if ($request->hasFile('bg_back')) {
            $request->file('bg_back')->move($path, 'card_bg_back.png');
        }

        return back()->with('success', 'Desain kartu berhasil diperbarui!');
    }

    // 4. PROSES CETAK PDF
    public function printPdf($id)
    {
        $user = User::findOrFail($id);
        $customPaper = [0, 0, 242.64, 153.01];

        $pdf = Pdf::loadView('pdf.member-card', compact('user'));
        $pdf->setPaper($customPaper, 'landscape');

        return $pdf->stream('MemberCard-' . $user->username . '.pdf');
    }

    // 5. TANDAI SELESAI
    public function markAsComplete($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status_cetak_kartu' => 'completed']);

        return back()->with('success', 'Status kartu diperbarui: Selesai Dicetak.');
    }
}