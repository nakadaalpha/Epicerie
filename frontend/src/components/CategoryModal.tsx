'use client';

import React from 'react';
import Link from 'next/link';
import { X, Layers, ShoppingBag } from 'lucide-react';
import { Kategori } from '@/types';

interface CategoryModalProps {
  isOpen: boolean;
  onClose: () => void;
  categories: Kategori[];
  onSelectCategory?: (id: number) => void;
}

export const CategoryModal: React.FC<CategoryModalProps> = ({
  isOpen,
  onClose,
  categories,
  onSelectCategory,
}) => {
  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 z-[9999] flex items-center justify-center p-4 font-sans animate-in fade-in duration-200">
      {/* Backdrop */}
      <div
        className="absolute inset-0 bg-gray-900/60 backdrop-blur-xs cursor-pointer"
        onClick={onClose}
      />

      {/* Modal Container */}
      <div className="bg-white rounded-2xl shadow-2xl w-full max-w-3xl relative z-10 overflow-hidden flex flex-col max-h-[85vh] animate-in zoom-in-95 duration-200">
        {/* Header */}
        <div className="px-6 py-5 border-b border-gray-100 flex justify-between items-center bg-white sticky top-0 z-20">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
              <Layers className="w-5 h-5" />
            </div>
            <div>
              <h3 className="font-bold text-xl text-gray-800 tracking-tight">
                Kategori Produk
              </h3>
              <p className="text-xs text-gray-500">
                Temukan barang incaranmu dengan cepat.
              </p>
            </div>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="w-10 h-10 flex items-center justify-center rounded-full bg-gray-50 hover:bg-red-50 text-gray-400 hover:text-red-500 transition cursor-pointer"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        {/* Content: Categories Grid */}
        <div className="p-6 overflow-y-auto max-h-[70vh] bg-gray-50/50">
          <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
            {/* All Products Option */}
            <button
              onClick={() => {
                if (onSelectCategory) onSelectCategory(0);
                onClose();
              }}
              className="bg-white p-4 rounded-xl border border-gray-200 hover:border-blue-500 hover:shadow-md transition text-left flex flex-col items-center justify-center gap-2 group cursor-pointer"
            >
              <div className="w-14 h-14 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                <ShoppingBag className="w-6 h-6" />
              </div>
              <span className="font-bold text-xs text-gray-800 text-center group-hover:text-blue-600 transition">
                Semua Produk
              </span>
            </button>

            {categories.map((cat) => (
              <button
                key={cat.id_kategori}
                onClick={() => {
                  if (onSelectCategory) onSelectCategory(cat.id_kategori);
                  onClose();
                }}
                className="bg-white p-4 rounded-xl border border-gray-200 hover:border-blue-500 hover:shadow-md transition text-left flex flex-col items-center justify-center gap-2 group cursor-pointer"
              >
                <div className="w-14 h-14 rounded-xl bg-gray-50 flex items-center justify-center overflow-hidden p-1.5 group-hover:scale-110 transition-transform">
                  {cat.gambar ? (
                    <img
                      src={cat.gambar}
                      alt={cat.nama_kategori}
                      className="w-full h-full object-contain"
                    />
                  ) : (
                    <Layers className="w-6 h-6 text-gray-400" />
                  )}
                </div>
                <span className="font-bold text-xs text-gray-800 text-center group-hover:text-blue-600 transition line-clamp-2">
                  {cat.nama_kategori}
                </span>
              </button>
            ))}
          </div>
        </div>
      </div>
    </div>
  );
};
