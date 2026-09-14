'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { X, MapPin, CheckCircle2, Plus, MapPinned } from 'lucide-react';
import { AlamatPengiriman } from '@/types';

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
  addresses = [],
  selectedAddressId,
  onSelectAddress,
}) => {
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-[9999] flex items-center justify-center p-4 font-sans animate-in fade-in duration-200">
      {/* Backdrop */}
      <div
        className="absolute inset-0 bg-black/60 backdrop-blur-xs cursor-pointer"
        onClick={onClose}
      />

      {/* Modal Container */}
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-2xl relative z-10 overflow-hidden flex flex-col max-h-[90vh] animate-in zoom-in-95 duration-200">
        {/* Header */}
        <div className="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-20">
          <h3 className="font-bold text-lg text-gray-800">
            Mau kirim belanjaan kemana?
          </h3>
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
          <p className="text-sm text-gray-500 mb-2">
            Biar pengalaman belanjamu lebih baik, pilih alamat pengirimanmu dulu.
          </p>

          {!currentUser ? (
            <div className="text-center py-8 border-2 border-dashed border-gray-200 rounded-xl bg-white p-6">
              <MapPinned className="w-12 h-12 text-blue-500 mx-auto mb-3" />
              <p className="text-gray-700 font-bold text-sm">
                Silakan login untuk memilih alamat pengiriman Anda
              </p>
              <Link
                href="/login"
                onClick={onClose}
                className="mt-4 inline-block bg-blue-600 text-white font-bold py-2.5 px-6 rounded-xl hover:bg-blue-700 transition shadow-sm text-sm"
              >
                Masuk ke Akun
              </Link>
            </div>
          ) : addresses.length === 0 ? (
            <div className="text-center py-8 border-2 border-dashed border-gray-200 rounded-xl bg-white p-6">
              <MapPin className="w-12 h-12 text-gray-300 mx-auto mb-3" />
              <p className="text-gray-600 font-bold text-sm">
                Kamu belum punya alamat pengiriman
              </p>
              <p className="text-xs text-gray-400 mt-1">
                Tambahkan alamat rumah, kos, atau kantor untuk kemudahan belanja.
              </p>
              <button
                onClick={() => {
                  onClose();
                  alert('Fitur manajemen alamat akan segera dibuka.');
                }}
                className="mt-4 inline-flex items-center gap-1.5 bg-blue-600 text-white font-bold py-2 px-6 rounded-xl hover:bg-blue-700 transition text-sm cursor-pointer"
              >
                <Plus className="w-4 h-4" />
                <span>Tambah Alamat Baru</span>
              </button>
            </div>
          ) : (
            addresses.map((addr, idx) => (
              <div
                key={addr.id_alamat}
                onClick={() => {
                  if (onSelectAddress) onSelectAddress(addr);
                  onClose();
                }}
                className={`border-2 bg-white p-4 rounded-xl cursor-pointer transition group relative ${
                  selectedAddressId === addr.id_alamat
                    ? 'border-blue-600 shadow-sm'
                    : 'border-gray-200 hover:border-blue-400'
                }`}
              >
                <div className="flex justify-between items-start">
                  <div className="flex-1 pr-4">
                    <div className="flex items-center gap-2 mb-1">
                      <span className="font-bold text-gray-800 text-sm">
                        {addr.label}
                      </span>
                      {addr.is_primary === 1 && (
                        <span className="bg-gray-200 text-gray-700 text-[10px] font-bold px-1.5 py-0.5 rounded">
                          Utama
                        </span>
                      )}
                    </div>
                    <p className="font-bold text-xs text-gray-700 mb-1">
                      {addr.penerima} ({addr.no_hp_penerima})
                    </p>
                    <p className="text-xs text-gray-500 leading-relaxed">
                      {addr.detail_alamat}
                    </p>
                  </div>
                  <div className="shrink-0 flex items-center h-full pt-1">
                    <button className="bg-blue-600 text-white font-bold px-4 py-1.5 rounded-lg text-xs hover:bg-blue-700 transition">
                      Pilih
                    </button>
                  </div>
                </div>
              </div>
            ))
          )}
        </div>
      </div>
    </div>
  );
};
