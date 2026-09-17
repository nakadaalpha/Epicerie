'use client';

import React, { useState, useTransition } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import {
  User,
  Mail,
  Phone,
  CreditCard,
  MapPin,
  Sparkles,
  CheckCircle,
  Clock,
  ArrowLeft,
  Plus,
  Loader2,
  Printer,
  ShieldCheck,
  LogOut,
  QrCode,
  Tag,
} from 'lucide-react';
import { formatRupiah } from '@/lib/utils';
import { AlamatPengiriman, User as UserType } from '@/types';
import { updateProfileAction, requestCardPrintAction, logoutAction } from '@/app/actions/auth';
import { AddressModal } from '@/components/AddressModal';
import { deleteAddressAction, setPrimaryAddressAction } from '@/app/actions/shop';

interface ProfileClientProps {
  user: any;
  addresses: AlamatPengiriman[];
}

export function ProfileClient({ user: initialUser, addresses: initialAddresses }: ProfileClientProps) {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState<'profile' | 'addresses' | 'card'>('profile');
  const [user, setUser] = useState<any>(initialUser);
  const [addresses, setAddresses] = useState<AlamatPengiriman[]>(initialAddresses);
  const [isAddressModalOpen, setIsAddressModalOpen] = useState(false);

  // Profile Form State
  const [nama, setNama] = useState(initialUser?.nama || '');
  const [email, setEmail] = useState(initialUser?.email || '');
  const [noHp, setNoHp] = useState(initialUser?.no_hp || '');
  const [profileLoading, setProfileLoading] = useState(false);
  const [profileSuccess, setProfileSuccess] = useState('');
  const [profileError, setProfileError] = useState('');

  // Card Print Request State
  const [requestingCard, setRequestingCard] = useState(false);
  const [cardMsg, setCardMsg] = useState('');
  const [cardError, setCardError] = useState('');

  const [isPending, startTransition] = useTransition();

  const handleUpdateProfile = async (e: React.FormEvent) => {
    e.preventDefault();
    setProfileLoading(true);
    setProfileSuccess('');
    setProfileError('');

    const res = await updateProfileAction({ nama, email, no_hp: noHp });
    setProfileLoading(false);

    if (res.success && res.user) {
      setUser((prev: any) => ({ ...prev, ...res.user }));
      setProfileSuccess('Profil berhasil diperbarui.');
      setTimeout(() => setProfileSuccess(''), 4000);
      startTransition(() => router.refresh());
    } else {
      setProfileError(res.error || 'Gagal memperbarui profil.');
    }
  };

  const handleRequestCardPrint = async () => {
    if (!confirm('Ajukan pencetakan fisik kartu keanggotaan Épicerie Anda?')) return;
    setRequestingCard(true);
    setCardMsg('');
    setCardError('');

    const res = await requestCardPrintAction();
    setRequestingCard(false);

    if (res.success) {
      setUser((prev: any) => ({ ...prev, status_cetak_kartu: 'pending' }));
      setCardMsg('Permintaan cetak kartu fisik berhasil diajukan!');
      setTimeout(() => setCardMsg(''), 4000);
      startTransition(() => router.refresh());
    } else {
      setCardError(res.error || 'Gagal mengajukan permintaan cetak kartu.');
    }
  };

  const handleDeleteAddress = async (id: number) => {
    if (!confirm('Hapus alamat ini dari daftar pengiriman Anda?')) return;
    const res = await deleteAddressAction(id);
    if (res.success) {
      setAddresses((prev) => prev.filter((a) => a.id_alamat !== id));
    } else {
      alert(res.error || 'Gagal menghapus alamat.');
    }
  };

  const handleSetPrimary = async (id: number) => {
    const res = await setPrimaryAddressAction(id);
    if (res.success) {
      setAddresses((prev) =>
        prev.map((a) => ({
          ...a,
          is_primary: a.id_alamat === id ? 1 : 0,
        }))
      );
    } else {
      alert(res.error || 'Gagal mengatur alamat utama.');
    }
  };

  const tier = user?.membership || 'Classic';
  const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&margin=0&data=${encodeURIComponent(
    String(user?.id_user || 1001)
  )}`;

  return (
    <div className="min-h-screen bg-slate-50 py-6 md:py-10 px-4">
      <div className="max-w-4xl mx-auto space-y-6">
        {/* Header Navigation */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <Link
              href="/"
              className="w-10 h-10 rounded-xl bg-white border border-gray-200 text-gray-600 flex items-center justify-center hover:bg-gray-50 transition shadow-2xs"
            >
              <ArrowLeft className="w-5 h-5" />
            </Link>
            <div>
              <h1 className="text-2xl md:text-3xl font-black text-gray-900 flex items-center gap-2.5">
                <User className="w-7 h-7 text-blue-600" /> Profil Pengguna
              </h1>
              <p className="text-xs md:text-sm text-gray-500 mt-0.5">
                Kelola informasi akun, alamat pengiriman, dan kartu keanggotaan Épicerie Anda.
              </p>
            </div>
          </div>

          <form action={logoutAction}>
            <button
              type="submit"
              className="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-red-200 cursor-pointer"
            >
              <LogOut className="w-4 h-4" /> Keluar
            </button>
          </form>
        </div>

        {/* User Card Banner */}
        <div className="bg-gradient-to-r from-blue-600 via-blue-700 to-teal-600 rounded-3xl p-6 md:p-8 text-white shadow-xl shadow-blue-500/20 relative overflow-hidden">
          <div className="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div className="flex items-center gap-4">
              <div className="w-16 h-16 rounded-2xl bg-white/20 backdrop-blur-xs flex items-center justify-center text-2xl font-black border border-white/30 text-white shadow-sm">
                {user?.nama?.charAt(0).toUpperCase() || 'U'}
              </div>
              <div>
                <div className="flex items-center gap-2">
                  <h2 className="text-xl md:text-2xl font-black">{user?.nama}</h2>
                  <span className="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-amber-400 text-slate-900">
                    {tier} Member
                  </span>
                </div>
                <p className="text-xs text-blue-100 font-mono mt-0.5">
                  @{user?.username} • ID: #{String(user?.id_user).padStart(6, '0')}
                </p>
                <div className="flex items-center gap-4 mt-3 text-xs text-blue-100">
                  <span>Role: <strong className="text-white capitalize">{user?.role}</strong></span>
                  {user?.totalSpent !== undefined && (
                    <span>Total Belanja: <strong className="text-white">{formatRupiah(user.totalSpent)}</strong></span>
                  )}
                </div>
              </div>
            </div>

            <div className="flex items-center gap-3">
              <Link
                href="/riwayat"
                className="px-4 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-xs text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-white/20"
              >
                Pesanan Saya
              </Link>
              <Link
                href="/ulasan"
                className="px-4 py-2.5 bg-white text-blue-700 rounded-xl text-xs font-black shadow-md hover:bg-blue-50 transition flex items-center gap-1.5"
              >
                Ulasan Saya
              </Link>
            </div>
          </div>
        </div>

        {/* Tab Navigation */}
        <div className="flex gap-2 border-b border-gray-200 overflow-x-auto pb-2 scrollbar-none">
          <button
            type="button"
            onClick={() => setActiveTab('profile')}
            className={`px-5 py-2.5 rounded-xl text-xs md:text-sm font-bold transition cursor-pointer flex items-center gap-2 ${
              activeTab === 'profile'
                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'
            }`}
          >
            <User className="w-4 h-4" /> Informasi Akun
          </button>
          <button
            type="button"
            onClick={() => setActiveTab('addresses')}
            className={`px-5 py-2.5 rounded-xl text-xs md:text-sm font-bold transition cursor-pointer flex items-center gap-2 ${
              activeTab === 'addresses'
                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'
            }`}
          >
            <MapPin className="w-4 h-4" /> Alamat Pengiriman ({addresses.length})
          </button>
          <button
            type="button"
            onClick={() => setActiveTab('card')}
            className={`px-5 py-2.5 rounded-xl text-xs md:text-sm font-bold transition cursor-pointer flex items-center gap-2 ${
              activeTab === 'card'
                ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'
            }`}
          >
            <CreditCard className="w-4 h-4" /> Kartu Keanggotaan
          </button>
        </div>

        {/* TAB CONTENT 1: INFORMASI PROFIL */}
        {activeTab === 'profile' && (
          <div className="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-gray-200/80 space-y-6">
            <h3 className="text-base font-extrabold text-gray-900 flex items-center gap-2">
              <User className="w-5 h-5 text-blue-600" /> Edit Data Pribadi
            </h3>

            {profileSuccess && (
              <div className="p-3.5 bg-green-50 border border-green-200 rounded-xl text-xs font-bold text-green-800 flex items-center gap-2">
                <CheckCircle className="w-4 h-4 text-green-600" /> {profileSuccess}
              </div>
            )}
            {profileError && (
              <div className="p-3.5 bg-red-50 border border-red-200 rounded-xl text-xs font-bold text-red-700">
                {profileError}
              </div>
            )}

            <form onSubmit={handleUpdateProfile} className="space-y-4">
              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                  Nama Lengkap
                </label>
                <input
                  type="text"
                  value={nama}
                  onChange={(e) => setNama(e.target.value)}
                  required
                  className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm font-medium"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                  Alamat Email
                </label>
                <input
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="contoh@domain.com"
                  className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm font-medium"
                />
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1.5">
                  Nomor Handphone / WhatsApp
                </label>
                <input
                  type="text"
                  value={noHp}
                  onChange={(e) => setNoHp(e.target.value)}
                  placeholder="08123456789"
                  className="w-full px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600 text-sm font-medium"
                />
              </div>

              <div className="pt-2">
                <button
                  type="submit"
                  disabled={profileLoading}
                  className="px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-sm rounded-2xl shadow-lg shadow-blue-500/25 transition cursor-pointer flex items-center gap-2 disabled:opacity-50"
                >
                  {profileLoading ? <Loader2 className="w-4 h-4 animate-spin" /> : null}
                  <span>Simpan Perubahan</span>
                </button>
              </div>
            </form>
          </div>
        )}

        {/* TAB CONTENT 2: ALAMAT PENGIRIMAN */}
        {activeTab === 'addresses' && (
          <div className="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-gray-200/80 space-y-6">
            <div className="flex items-center justify-between">
              <div>
                <h3 className="text-base font-extrabold text-gray-900 flex items-center gap-2">
                  <MapPin className="w-5 h-5 text-blue-600" /> Daftar Alamat Pengiriman
                </h3>
                <p className="text-xs text-gray-500 mt-0.5">
                  Alamat ini digunakan untuk pengiriman belanja online Épicerie.
                </p>
              </div>
              <button
                type="button"
                onClick={() => setIsAddressModalOpen(true)}
                className="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm shadow-blue-500/20 cursor-pointer"
              >
                <Plus className="w-4 h-4" /> Tambah Alamat
              </button>
            </div>

            {addresses.length === 0 ? (
              <div className="text-center py-10 border-2 border-dashed border-gray-200 rounded-2xl p-6 space-y-2">
                <MapPin className="w-10 h-10 text-gray-300 mx-auto" />
                <p className="text-xs font-bold text-gray-600">Belum ada alamat tersimpan</p>
                <p className="text-[11px] text-gray-400">
                  Tambahkan alamat rumah atau kantor Anda untuk mempermudah checkout pengiriman.
                </p>
              </div>
            ) : (
              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                {addresses.map((addr) => {
                  const isPrimary = !!addr.is_primary || !!(addr as any).is_utama;
                  return (
                    <div
                      key={addr.id_alamat}
                      className={`p-4 rounded-2xl border transition relative space-y-2 ${
                        isPrimary
                          ? 'border-blue-500 bg-blue-50/40 shadow-xs'
                          : 'border-gray-200 bg-white hover:border-gray-300'
                      }`}
                    >
                      <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                          <span className="font-extrabold text-sm text-gray-900">
                            {addr.label}
                          </span>
                          {isPrimary && (
                            <span className="px-2 py-0.5 rounded-full text-[10px] font-black uppercase bg-blue-600 text-white">
                              Utama
                            </span>
                          )}
                        </div>
                      </div>

                      <div className="text-xs text-gray-600 space-y-0.5">
                        <p className="font-bold text-gray-800">
                          {addr.penerima} ({addr.no_hp_penerima})
                        </p>
                        <p className="line-clamp-2 leading-relaxed">{addr.detail_alamat}</p>
                      </div>

                      <div className="pt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                        {!isPrimary ? (
                          <button
                            type="button"
                            onClick={() => handleSetPrimary(addr.id_alamat)}
                            className="text-blue-600 font-bold hover:underline cursor-pointer"
                          >
                            Jadikan Utama
                          </button>
                        ) : (
                          <span className="text-emerald-600 font-bold flex items-center gap-1">
                            <CheckCircle className="w-3.5 h-3.5" /> Alamat Utama
                          </span>
                        )}

                        <button
                          type="button"
                          onClick={() => handleDeleteAddress(addr.id_alamat)}
                          className="text-red-500 hover:text-red-700 font-bold cursor-pointer"
                        >
                          Hapus
                        </button>
                      </div>
                    </div>
                  );
                })}
              </div>
            )}
          </div>
        )}

        {/* TAB CONTENT 3: KARTU MEMBER */}
        {activeTab === 'card' && (
          <div className="space-y-6">
            <div className="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-gray-200/80 space-y-6">
              <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                  <h3 className="text-base font-extrabold text-gray-900 flex items-center gap-2">
                    <CreditCard className="w-5 h-5 text-blue-600" /> Kartu Keanggotaan Digital
                  </h3>
                  <p className="text-xs text-gray-500 mt-0.5">
                    Tunjukkan kartu ini kepada kasir saat berbelanja untuk mendapatkan poin & promo khusus.
                  </p>
                </div>

                {/* Status Cetak Kartu Fisik */}
                <div>
                  {user?.status_cetak_kartu === 'pending' ? (
                    <span className="px-4 py-2 rounded-xl text-xs font-extrabold bg-amber-100 text-amber-800 border border-amber-200 flex items-center gap-1.5">
                      <Clock className="w-4 h-4 animate-spin" /> Sedang Diproses Admin
                    </span>
                  ) : user?.status_cetak_kartu === 'completed' ? (
                    <span className="px-4 py-2 rounded-xl text-xs font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200 flex items-center gap-1.5">
                      <CheckCircle className="w-4 h-4" /> Fisik Selesai Dicetak
                    </span>
                  ) : (
                    <button
                      type="button"
                      onClick={handleRequestCardPrint}
                      disabled={requestingCard}
                      className="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-blue-500/20 cursor-pointer disabled:opacity-50"
                    >
                      {requestingCard ? <Loader2 className="w-4 h-4 animate-spin" /> : <Printer className="w-4 h-4" />}
                      <span>Minta Cetak Kartu Fisik</span>
                    </button>
                  )}
                </div>
              </div>

              {cardMsg && (
                <div className="p-3.5 bg-green-50 border border-green-200 rounded-xl text-xs font-bold text-green-800 flex items-center gap-2">
                  <CheckCircle className="w-4 h-4 text-green-600" /> {cardMsg}
                </div>
              )}
              {cardError && (
                <div className="p-3.5 bg-red-50 border border-red-200 rounded-xl text-xs font-bold text-red-700">
                  {cardError}
                </div>
              )}

              {/* Digital Card Preview */}
              <div className="max-w-md mx-auto aspect-[85.6/53.98] rounded-3xl overflow-hidden shadow-2xl border border-slate-700 relative bg-slate-900 group">
                <img
                  src="/images/card_bg.png"
                  alt="Member Card"
                  className="w-full h-full object-cover opacity-85"
                  onError={(e: any) => {
                    e.target.src = 'https://placehold.co/1026x648/1e3a8a/FFF?text=ÉPICERIE+CARD';
                  }}
                />

                <div className="absolute inset-0 p-6 flex flex-col justify-between text-white drop-shadow-md">
                  <div className="flex justify-between items-start">
                    <span className="font-black text-xl tracking-widest">ÉPICERIE</span>
                    <span className="bg-amber-400 text-slate-900 text-[10px] font-black px-3 py-0.5 rounded-full uppercase">
                      {tier} Member
                    </span>
                  </div>

                  <div className="flex justify-between items-end">
                    <div>
                      <p className="text-[10px] font-semibold text-white/80 uppercase">NAMA KEANGGOTAAN</p>
                      <p className="font-extrabold text-base tracking-wide">
                        {user?.nama?.toUpperCase()}
                      </p>
                      <p className="text-xs text-white/80 font-mono mt-0.5">
                        NO: {String(user?.id_user).padStart(8, '0')}
                      </p>
                    </div>
                    <div className="w-12 h-12 bg-white rounded-xl p-1 flex items-center justify-center text-slate-900 shadow-md">
                      <img src={qrUrl} alt="QR" className="w-full h-full object-contain" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Address Creation Modal */}
      {isAddressModalOpen && (
        <AddressModal
          isOpen={isAddressModalOpen}
          onClose={() => setIsAddressModalOpen(false)}
          currentUser={user}
          addresses={addresses}
          onSelectAddress={(newAddr) => {
            setAddresses((prev) => [newAddr, ...prev]);
            setIsAddressModalOpen(false);
          }}
        />
      )}
    </div>
  );
}
