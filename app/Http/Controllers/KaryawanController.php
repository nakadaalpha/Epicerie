<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User; // Panggil Model User
use Illuminate\Support\Facades\Hash; // Buat enkripsi password

class KaryawanController extends Controller
{
    // 1. TAMPILKAN DAFTAR KARYAWAN
    public function index()
    {
        // Ambil user yang role-nya 'Karyawan' aja
        $karyawan = User::where('role', 'Karyawan')->get();
        return view('karyawan.index', compact('karyawan'));
    }

    // 2. FORM TAMBAH KARYAWAN
    public function create()
    {
        return view('karyawan.create');
    }

    // 3. SIMPAN KARYAWAN BARU
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama' => 'required',
            'username' => 'required|unique:user,username', // Username gak boleh kembar
            'password' => 'required|min:3',
        ]);

        User::create([
            'nama' => $request->nama,
            'username' => $request->username,
            'password' => Hash::make($request->password), // Password wajib di-enkripsi
            'role' => 'Karyawan' // Otomatis jadi Karyawan
        ]);

        return redirect()->route('karyawan.index')->with('success', 'Karyawan berhasil ditambahkan');
    }

    // 4. FORM EDIT KARYAWAN
    public function edit($id)
    {
        $karyawan = User::find($id);
        return view('karyawan.edit', compact('karyawan'));
    }

    // 5. UPDATE DATA KARYAWAN
    public function update(Request $request, $id)
    {
        $karyawan = User::find($id);

        $request->validate([
            'nama' => 'required',
            'username' => 'required|unique:user,username,'.$id.',id_user', // Boleh pake username lama dia sendiri
        ]);

        // Update data dasar
        $karyawan->nama = $request->nama;
        $karyawan->username = $request->username;

        // Kalau password diisi, update password baru. Kalau kosong, biarin password lama.
        if ($request->password) {
            $karyawan->password = Hash::make($request->password);
        }

        $karyawan->save();

        return redirect()->route('karyawan.index')->with('success', 'Data berhasil diperbarui');
    }

    // 6. HAPUS KARYAWAN
    public function destroy($id)
    {
        User::destroy($id);
        return redirect()->route('karyawan.index')->with('success', 'Karyawan dihapus');
    }
}
