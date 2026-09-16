'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import {
  X,
  MapPin,
  CheckCircle2,
  Plus,
  MapPinned,
  ArrowLeft,
  Loader2,
  Phone,
  Tag,
  User,
} from 'lucide-react';
import { AlamatPengiriman } from '@/types';
import { createAddressAction, getUserAddressesAction } from '@/app/actions/shop';

interface AddressModalProps {
  isOpen: boolean;
  onClose: () => void;
  currentUser?: any;
  addresses?: AlamatPengiriman[];
  selectedAddressId?: number;
  onSelectAddress?: (addr: AlamatPengiriman) => void;
}

export const AddressModal: React.FC<AddressModalProps> = ({
  isOpen,
  onClose,
  currentUser,
  addresses: propAddresses = [],
  selectedAddressId,
  onSelectAddress,
}) => {
  const [addressList, setAddressList] = useState<AlamatPengiriman[]>(propAddresses);
  const [isAddingNew, setIsAddingNew] = useState(false);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);

  // Form State
  const [formData, setFormData] = useState({
    label: 'Rumah',
    penerima: currentUser?.nama || '',
    no_hp_penerima: currentUser?.no_hp || '',
    detail_alamat: '',
    is_primary: false,
  });

  useEffect(() => {
    if (propAddresses && propAddresses.length > 0) {
      setAddressList(propAddresses);
    } else if (currentUser && isOpen) {
      // Fetch fresh addresses
      getUserAddressesAction().then((addrs) => {
        if (addrs) setAddressList(addrs);
      });
    }
  }, [propAddresses, currentUser, isOpen]);

  if (!isOpen) return null;

  const handleCreateAddress = async (e: React.FormEvent) => {
    e.preventDefault();
    setErrorMsg(null);

    if (!formData.detail_alamat.trim()) {
      setErrorMsg('Detail alamat lengkap wajib diisi.');
      return;
    }

    setIsSubmitting(true);
    try {
      const res = await createAddressAction(formData);
      if (res.success && res.data) {
        const newAddr = res.data;
        setAddressList((prev) => [newAddr, ...prev]);
        if (onSelectAddress) {
          onSelectAddress(newAddr);
        }
        setIsAddingNew(false);
        setFormData({
          label: 'Rumah',
          penerima: currentUser?.nama || '',
          no_hp_penerima: currentUser?.no_hp || '',
          detail_alamat: '',
          is_primary: false,
        });
      } else {
        setErrorMsg(res.error || 'Gagal menyimpan alamat.');
      }
    } catch (err: any) {
      setErrorMsg(err.message || 'Terjadi kesalahan sistem.');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="fixed inset-0 z-[9999] flex items-center justify-center p-4 font-sans animate-in fade-in duration-200">
      {/* Backdrop */}
      <div
        className="absolute inset-0 bg-black/60 backdrop-blur-xs cursor-pointer"
        onClick={onClose}
      />

      {/* Modal Container */}
      <div className="bg-white rounded-3xl shadow-2xl w-full max-w-xl relative z-10 overflow-hidden flex flex-col max-h-[90vh] animate-in zoom-in-95 duration-200">
        {/* Header */}
        <div className="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-20">
          <div className="flex items-center gap-2">
            {isAddingNew && (
              <button
                type="button"
                onClick={() => setIsAddingNew(false)}
                className="p-1 text-gray-400 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition mr-1"
              >
                <ArrowLeft className="w-5 h-5" />
              </button>
            )}
            <h3 className="font-extrabold text-base sm:text-lg text-gray-900">
              {isAddingNew ? 'Tambah Alamat Baru' : 'Mau kirim belanjaan kemana?'}
            </h3>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Body */}
        <div className="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50/40">
          {isAddingNew ? (
            /* Form Tambah Alamat */
            <form onSubmit={handleCreateAddress} className="space-y-4">
              {errorMsg && (
                <div className="bg-rose-50 border border-rose-200 text-rose-600 text-xs p-3 rounded-xl font-bold">
                  {errorMsg}
                </div>
              )}

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">
                  Label Alamat
                </label>
                <div className="relative">
                  <Tag className="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                  <input
                    type="text"
                    required
                    value={formData.label}
                    onChange={(e) => setFormData({ ...formData, label: e.target.value })}
                    placeholder="Contoh: Rumah, Kantor, Kos"
                    className="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600"
                  />
                </div>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    Nama Penerima
                  </label>
                  <div className="relative">
                    <User className="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                      type="text"
                      required
                      value={formData.penerima}
                      onChange={(e) => setFormData({ ...formData, penerima: e.target.value })}
                      placeholder="Nama Lengkap"
                      className="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600"
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-xs font-bold text-gray-700 mb-1">
                    No. Handphone
                  </label>
                  <div className="relative">
                    <Phone className="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                    <input
                      type="text"
                      required
                      value={formData.no_hp_penerima}
                      onChange={(e) =>
                        setFormData({ ...formData, no_hp_penerima: e.target.value })
                      }
                      placeholder="0812..."
                      className="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600"
                    />
                  </div>
                </div>
              </div>

              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">
                  Detail Alamat Lengkap
                </label>
                <textarea
                  rows={3}
                  required
                  value={formData.detail_alamat}
                  onChange={(e) => setFormData({ ...formData, detail_alamat: e.target.value })}
                  placeholder="Nama jalan, nomor rumah, RT/RW, kelurahan, patokan lokasi..."
                  className="w-full p-3 bg-white border border-gray-200 rounded-xl text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600"
                />
              </div>

              <div className="flex items-center gap-2">
                <input
                  type="checkbox"
                  id="is_primary"
                  checked={formData.is_primary}
                  onChange={(e) => setFormData({ ...formData, is_primary: e.target.checked })}
                  className="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                />
                <label htmlFor="is_primary" className="text-xs font-bold text-gray-700 cursor-pointer">
                  Jadikan sebagai alamat pengiriman utama
                </label>
              </div>

              <div className="pt-2 flex justify-end gap-3">
                <button
                  type="button"
                  onClick={() => setIsAddingNew(false)}
                  className="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-xs font-bold hover:bg-gray-50 transition"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={isSubmitting}
                  className="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow-sm disabled:opacity-50 flex items-center gap-2 cursor-pointer"
                >
                  {isSubmitting && <Loader2 className="w-4 h-4 animate-spin" />}
                  <span>Simpan Alamat</span>
                </button>
              </div>
            </form>
          ) : (
            /* Daftar Alamat */
            <>
              <div className="flex items-center justify-between mb-2">
                <p className="text-xs text-gray-500">
                  Pilih salah satu alamat untuk pengalaman berbelanja yang cepat.
                </p>
                {currentUser && (
                  <button
                    onClick={() => setIsAddingNew(true)}
                    className="inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-700 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-xl transition cursor-pointer shrink-0"
                  >
                    <Plus className="w-3.5 h-3.5" />
                    <span>Tambah Alamat</span>
                  </button>
                )}
              </div>

              {!currentUser ? (
                <div className="text-center py-8 border-2 border-dashed border-gray-200 rounded-2xl bg-white p-6">
                  <MapPinned className="w-12 h-12 text-blue-500 mx-auto mb-3" />
                  <p className="text-gray-700 font-bold text-sm">
                    Silakan login untuk memilih alamat pengiriman Anda
                  </p>
                  <Link
                    href="/login"
                    onClick={onClose}
                    className="mt-4 inline-block bg-blue-600 text-white font-bold py-2.5 px-6 rounded-xl hover:bg-blue-700 transition shadow-sm text-xs"
                  >
                    Masuk ke Akun
                  </Link>
                </div>
              ) : addressList.length === 0 ? (
                <div className="text-center py-8 border-2 border-dashed border-gray-200 rounded-2xl bg-white p-6">
                  <MapPin className="w-12 h-12 text-gray-300 mx-auto mb-3" />
                  <p className="text-gray-600 font-bold text-sm">
                    Kamu belum punya alamat pengiriman
                  </p>
                  <p className="text-xs text-gray-400 mt-1">
                    Tambahkan alamat rumah, kos, atau kantor untuk kemudahan belanja.
                  </p>
                  <button
                    onClick={() => setIsAddingNew(true)}
                    className="mt-4 inline-flex items-center gap-1.5 bg-blue-600 text-white font-bold py-2 px-6 rounded-xl hover:bg-blue-700 transition text-xs cursor-pointer"
                  >
                    <Plus className="w-4 h-4" />
                    <span>Tambah Alamat Baru</span>
                  </button>
                </div>
              ) : (
                <div className="space-y-3">
                  {addressList.map((addr) => {
                    const isSelected = selectedAddressId === addr.id_alamat;
                    return (
                      <div
                        key={addr.id_alamat}
                        onClick={() => {
                          if (onSelectAddress) onSelectAddress(addr);
                          onClose();
                        }}
                        className={`border-2 bg-white p-4 rounded-2xl cursor-pointer transition group relative ${
                          isSelected
                            ? 'border-blue-600 shadow-sm bg-blue-50/20'
                            : 'border-gray-200 hover:border-blue-400'
                        }`}
                      >
                        <div className="flex justify-between items-start">
                          <div className="flex-1 pr-4">
                            <div className="flex items-center gap-2 mb-1">
                              <span className="font-extrabold text-gray-900 text-xs sm:text-sm">
                                {addr.label}
                              </span>
                              {(Number(addr.is_primary) === 1 || Number(addr.is_utama) === 1) && (
                                <span className="bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-black px-2 py-0.5 rounded-md">
                                  Utama
                                </span>
                              )}
                            </div>
                            <p className="font-bold text-xs text-gray-700 mb-1">
                              {addr.penerima} ({addr.no_hp_penerima})
                            </p>
                            <p className="text-xs text-gray-500 leading-relaxed line-clamp-2">
                              {addr.detail_alamat}
                            </p>
                          </div>
                          <div className="shrink-0 flex items-center h-full pt-1">
                            <button
                              type="button"
                              className={`font-bold px-4 py-1.5 rounded-xl text-xs transition ${
                                isSelected
                                  ? 'bg-blue-600 text-white shadow-xs'
                                  : 'bg-gray-100 text-gray-700 group-hover:bg-blue-600 group-hover:text-white'
                              }`}
                            >
                              {isSelected ? 'Terpilih' : 'Pilih'}
                            </button>
                          </div>
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}
            </>
          )}
        </div>
      </div>
    </div>
  );
};
