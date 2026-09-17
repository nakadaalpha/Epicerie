'use client';

import React, { useState, useEffect, useRef } from 'react';
import {
  Menu,
  Palette,
  Upload,
  Check,
  CreditCard,
  QrCode,
  Sparkles,
  RotateCcw,
  Image as ImageIcon,
} from 'lucide-react';
import { AdminSidebar } from './AdminSidebar';

interface CardSettingsClientProps {
  currentUser: any;
  pendingCardCount: number;
}

const DEFAULT_FRONT = '/images/card_bg.png';
const DEFAULT_BACK = '/images/card_bg_back.png';

export function CardSettingsClient({
  currentUser,
  pendingCardCount,
}: CardSettingsClientProps) {
  const [isSidebarOpen, setIsSidebarOpen] = useState(false);
  const [savedSuccess, setSavedSuccess] = useState(false);

  const [frontBg, setFrontBg] = useState<string>(DEFAULT_FRONT);
  const [backBg, setBackBg] = useState<string>(DEFAULT_BACK);

  const frontFileInputRef = useRef<HTMLInputElement>(null);
  const backFileInputRef = useRef<HTMLInputElement>(null);

  // Load persisted custom card backgrounds from localStorage
  useEffect(() => {
    if (typeof window !== 'undefined') {
      const savedFront = localStorage.getItem('epicerie_card_front_bg');
      const savedBack = localStorage.getItem('epicerie_card_back_bg');
      if (savedFront) setFrontBg(savedFront);
      if (savedBack) setBackBg(savedBack);
    }
  }, []);

  const handleFileUpload = (
    e: React.ChangeEvent<HTMLInputElement>,
    setter: (val: string) => void
  ) => {
    const file = e.target.files?.[0];
    if (!file) return;

    if (!file.type.startsWith('image/')) {
      alert('Mohon pilih berkas gambar valid (PNG / JPG / WEBP).');
      return;
    }

    const reader = new FileReader();
    reader.onload = () => {
      if (typeof reader.result === 'string') {
        setter(reader.result);
      }
    };
    reader.readAsDataURL(file);
  };

  const handleResetToDefault = () => {
    setFrontBg(DEFAULT_FRONT);
    setBackBg(DEFAULT_BACK);
    if (typeof window !== 'undefined') {
      localStorage.removeItem('epicerie_card_front_bg');
      localStorage.removeItem('epicerie_card_back_bg');
    }
    setSavedSuccess(true);
    setTimeout(() => setSavedSuccess(false), 3000);
  };

  const handleSave = (e: React.FormEvent) => {
    e.preventDefault();
    if (typeof window !== 'undefined') {
      localStorage.setItem('epicerie_card_front_bg', frontBg);
      localStorage.setItem('epicerie_card_back_bg', backBg);
    }
    setSavedSuccess(true);
    setTimeout(() => setSavedSuccess(false), 4000);
  };

  return (
    <div className="bg-gradient-to-br from-blue-500 to-teal-400 h-screen flex overflow-hidden font-sans select-none">
      <AdminSidebar
        isOpen={isSidebarOpen}
        onClose={() => setIsSidebarOpen(false)}
        currentUser={currentUser}
        pendingCardCount={pendingCardCount}
      />

      <main className="flex-1 flex flex-col h-screen overflow-hidden relative">
        {/* Mobile Header */}
        <header className="md:hidden h-16 flex items-center justify-between px-6 shrink-0 z-30">
          <span className="font-extrabold text-white text-lg drop-shadow-md">
            Desain Kartu
          </span>
          <button
            type="button"
            onClick={() => setIsSidebarOpen(true)}
            className="text-white bg-white/20 p-2 rounded-lg backdrop-blur-xs shadow-xs hover:bg-white/30 transition cursor-pointer"
          >
            <Menu className="w-6 h-6" />
          </button>
        </header>

        {/* Content */}
        <div className="flex-1 overflow-y-auto p-4 md:p-8 relative scrollbar-thin">
          <div className="max-w-7xl mx-auto space-y-6">
            <div className="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
              <div>
                <h1 className="text-2xl md:text-3xl font-black text-white drop-shadow-sm">
                  Pengaturan Desain Kartu Member
                </h1>
                <p className="text-white/80 text-sm mt-0.5">
                  Kustomisasi latar belakang kartu digital dan cetak untuk member Épicerie (Standar ISO ID-1).
                </p>
              </div>
              <button
                type="button"
                onClick={handleResetToDefault}
                className="self-start md:self-auto px-4 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 cursor-pointer backdrop-blur-xs"
              >
                <RotateCcw className="w-3.5 h-3.5" /> Reset Default Épicerie
              </button>
            </div>

            {savedSuccess && (
              <div className="bg-green-100 border border-green-200 text-green-800 p-4 rounded-2xl flex items-center gap-3 shadow-md animate-in fade-in">
                <div className="w-8 h-8 rounded-full bg-green-200 text-green-800 flex items-center justify-center font-bold">
                  <Check className="w-5 h-5" />
                </div>
                <div>
                  <p className="font-bold text-sm">Pengaturan Berhasil Disimpan!</p>
                  <p className="text-xs text-green-700">
                    Template desain kartu fisik dan digital member kini telah diperbarui.
                  </p>
                </div>
              </div>
            )}

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
              {/* Form Settings */}
              <div className="lg:col-span-2 space-y-6">
                <form onSubmit={handleSave} className="space-y-6">
                  {/* Sisi Depan */}
                  <div className="bg-white rounded-[2rem] p-6 md:p-8 shadow-xl border border-white/40 relative overflow-hidden">
                    <div className="flex justify-between items-center mb-6">
                      <h3 className="font-bold text-gray-800 text-lg flex items-center gap-3">
                        <span className="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center shadow-xs">
                          <Palette className="w-5 h-5" />
                        </span>
                        Sisi Depan (Utama)
                      </h3>
                      <span className="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-xs font-bold border border-blue-100">
                        Wajib
                      </span>
                    </div>

                    <div className="space-y-4">
                      {/* Upload Box */}
                      <div>
                        <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                          Upload Berkas Gambar (PNG / JPG)
                        </label>
                        <input
                          ref={frontFileInputRef}
                          type="file"
                          accept="image/*"
                          className="hidden"
                          onChange={(e) => handleFileUpload(e, setFrontBg)}
                        />
                        <div
                          onClick={() => frontFileInputRef.current?.click()}
                          className="border-2 border-dashed border-gray-300 hover:border-blue-500 rounded-2xl p-6 text-center bg-gray-50 hover:bg-blue-50/50 transition cursor-pointer flex flex-col items-center justify-center gap-2 group"
                        >
                          <div className="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center group-hover:scale-110 transition">
                            <Upload className="w-6 h-6" />
                          </div>
                          <p className="text-xs font-bold text-gray-700">
                            Klik di sini untuk memilih gambar latar sisi depan
                          </p>
                          <p className="text-[10px] text-gray-400">
                            Ukuran Rekomendasi: 1026 × 648 piksel (Rasio 85.6mm × 54mm)
                          </p>
                        </div>
                      </div>

                      <div>
                        <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                          Atau Masukkan URL Background Depan
                        </label>
                        <input
                          type="text"
                          value={frontBg}
                          onChange={(e) => setFrontBg(e.target.value)}
                          placeholder="/images/card_bg.png atau https://..."
                          className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium"
                        />
                      </div>
                    </div>
                  </div>

                  {/* Sisi Belakang */}
                  <div className="bg-white rounded-[2rem] p-6 md:p-8 shadow-xl border border-white/40 relative overflow-hidden">
                    <div className="flex justify-between items-center mb-6">
                      <h3 className="font-bold text-gray-800 text-lg flex items-center gap-3">
                        <span className="w-10 h-10 rounded-xl bg-gray-100 text-gray-600 flex items-center justify-center shadow-xs">
                          <CreditCard className="w-5 h-5" />
                        </span>
                        Sisi Belakang
                      </h3>
                      <span className="bg-gray-100 text-gray-600 px-3 py-1 rounded-full text-xs font-bold border border-gray-200">
                        Opsional
                      </span>
                    </div>

                    <div className="space-y-4">
                      {/* Upload Box */}
                      <div>
                        <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                          Upload Berkas Gambar (PNG / JPG)
                        </label>
                        <input
                          ref={backFileInputRef}
                          type="file"
                          accept="image/*"
                          className="hidden"
                          onChange={(e) => handleFileUpload(e, setBackBg)}
                        />
                        <div
                          onClick={() => backFileInputRef.current?.click()}
                          className="border-2 border-dashed border-gray-300 hover:border-gray-500 rounded-2xl p-6 text-center bg-gray-50 hover:bg-gray-100 transition cursor-pointer flex flex-col items-center justify-center gap-2 group"
                        >
                          <div className="w-12 h-12 rounded-xl bg-gray-200 text-gray-700 flex items-center justify-center group-hover:scale-110 transition">
                            <Upload className="w-6 h-6" />
                          </div>
                          <p className="text-xs font-bold text-gray-700">
                            Klik di sini untuk memilih gambar latar sisi belakang
                          </p>
                          <p className="text-[10px] text-gray-400">
                            Ukuran Rekomendasi: 1026 × 648 piksel
                          </p>
                        </div>
                      </div>

                      <div>
                        <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                          Atau Masukkan URL Background Belakang
                        </label>
                        <input
                          type="text"
                          value={backBg}
                          onChange={(e) => setBackBg(e.target.value)}
                          placeholder="/images/card_bg_back.png atau https://..."
                          className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm font-medium"
                        />
                      </div>
                    </div>
                  </div>

                  <button
                    type="submit"
                    className="w-full bg-blue-600 hover:bg-blue-700 text-white py-3.5 rounded-2xl font-bold shadow-lg shadow-blue-500/30 transition flex items-center justify-center gap-2 cursor-pointer"
                  >
                    <Check className="w-5 h-5" /> Simpan Pengaturan Desain
                  </button>
                </form>
              </div>

              {/* Live Card Preview */}
              <div className="space-y-6">
                <div className="bg-white rounded-[2rem] p-6 shadow-xl border border-white/40">
                  <h3 className="font-bold text-gray-800 text-base mb-4 flex items-center gap-2">
                    <Sparkles className="w-5 h-5 text-amber-500" /> Live Preview Sisi Depan
                  </h3>

                  <div className="aspect-[85.6/53.98] rounded-2xl overflow-hidden shadow-xl border border-gray-200 relative bg-slate-900 group">
                    <img
                      src={frontBg}
                      alt="Front Preview"
                      className="w-full h-full object-cover opacity-80"
                      onError={(e: any) => {
                        e.target.src = 'https://placehold.co/1026x648/1e3a8a/FFF?text=ÉPICERIE+CARD';
                      }}
                    />

                    {/* Card Content Mockup */}
                    <div className="absolute inset-0 p-5 flex flex-col justify-between text-white drop-shadow-md">
                      <div className="flex justify-between items-start">
                        <span className="font-black text-lg tracking-widest">ÉPICERIE</span>
                        <span className="bg-amber-400 text-slate-900 text-[10px] font-black px-2.5 py-0.5 rounded-full uppercase">
                          Gold Member
                        </span>
                      </div>

                      <div className="flex justify-between items-end">
                        <div>
                          <p className="text-[10px] font-semibold text-white/80 uppercase">NAMA MEMBER</p>
                          <p className="font-extrabold text-sm tracking-wide">
                            {currentUser?.nama?.toUpperCase() || 'PELANGGAN SETIA'}
                          </p>
                          <p className="text-[10px] text-white/70 font-mono mt-0.5">
                            ID: {String(currentUser?.id_user || 1001).padStart(8, '0')}
                          </p>
                        </div>
                        <div className="w-10 h-10 bg-white rounded-lg flex items-center justify-center text-slate-900 shadow-sm">
                          <QrCode className="w-7 h-7" />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div className="bg-white rounded-[2rem] p-6 shadow-xl border border-white/40">
                  <h3 className="font-bold text-gray-800 text-base mb-4 flex items-center gap-2">
                    <CreditCard className="w-5 h-5 text-gray-500" /> Live Preview Sisi Belakang
                  </h3>

                  <div className="aspect-[85.6/53.98] rounded-2xl overflow-hidden shadow-xl border border-gray-200 relative bg-slate-900">
                    <img
                      src={backBg}
                      alt="Back Preview"
                      className="w-full h-full object-cover opacity-80"
                      onError={(e: any) => {
                        e.target.src = 'https://placehold.co/1026x648/334155/FFF?text=BACK+SIDE';
                      }}
                    />
                    <div className="absolute inset-0 p-5 flex flex-col justify-between text-white">
                      <div className="w-full h-8 bg-black/80 rounded-sm -mx-5 px-5 flex items-center">
                        <span className="text-[9px] font-mono text-gray-300">MAGNETIC STRIPE SIMULATION</span>
                      </div>
                      <p className="text-[9px] text-white/80 leading-relaxed">
                        Kartu ini adalah milik Épicerie Gourmet Store. Gunakan saat transaksi di kasir untuk menikmati cashback & promo eksklusif.
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}
