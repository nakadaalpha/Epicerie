@extends('layouts.admin')

@section('title', 'Tambah Karyawan')
@section('header_title', 'Registrasi Karyawan')

@section('content')
<div class="flex justify-center">
    <div class="bg-white rounded-3xl p-8 shadow-lg border border-gray-100 w-full max-w-lg">

        <div class="flex items-center mb-6 text-gray-800">
            <a href="{{ route('karyawan.index') }}" class="mr-4 text-gray-400 hover:text-blue-600 transition transform hover:-translate-x-1">
                <i class="fa-solid fa-arrow-left text-xl"></i>
            </a>
            <h2 class="text-2xl font-bold">Karyawan Baru</h2>
        </div>

        <form action="{{ route('karyawan.store') }}" method="POST">
            @csrf
            <div class="mb-5">
                <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Nama Lengkap</label>
                <input type="text" name="nama" placeholder="Contoh: Budi Santoso" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" required>
            </div>

            <div class="mb-5">
                <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Username</label>
                <input type="text" name="username" placeholder="Username untuk login" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" required>
            </div>

            <div class="mb-8">
                <label class="block text-gray-700 font-bold mb-2 text-sm ml-1">Password</label>
                <input type="password" name="password" placeholder="********" class="w-full p-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-400 bg-gray-50" required>
            </div>

            <div class="flex justify-end gap-4">
                <a href="{{ route('karyawan.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700 font-bold">Batal</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700 shadow-md transition">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection