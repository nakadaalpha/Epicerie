'use client';

import React from 'react';
import Link from 'next/link';
import { Produk } from '@/types';
import { formatRupiah } from '@/lib/utils';
import { Star, Image as ImageIcon } from 'lucide-react';
import { useCartStore } from '@/store/useCartStore';

interface ProductCardProps {
  product: Produk;
  variant?: 'default' | 'new' | 'top';
  rank?: number;
}

export const ProductCard: React.FC<ProductCardProps> = ({
  product,
  variant = 'default',
  rank,
}) => {
  const { addItem } = useCartStore();

  const isOutOfStock = product.stok <= 0;
  const hasDiskon = ((product as any).persen_diskon || 0) > 0;
  const persenDiskon = (product as any).persen_diskon || 0;
  const avgRating = Number((product as any).avg_rating || 0).toFixed(1);
  const totalTerjual = product.total_terjual || 0;

  const handleAddToCart = (e: React.MouseEvent) => {
    e.preventDefault();
    e.stopPropagation();
    if (isOutOfStock) return;
    addItem(product, 1);
  };

  const isTop = variant === 'top';
  const isNew = variant === 'new';

  return (
    <div className="bg-white p-2 rounded-xl border border-gray-100 shadow-xs hover:shadow-md transition-all group flex flex-col justify-between h-full relative overflow-hidden select-none">
      {/* 1. Image & Badge Wrapper */}
      <div className="relative mb-2">
        {hasDiskon ? (
          <div className="absolute top-0 left-0 z-20 bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-br-lg rounded-tl-lg shadow-xs">
            -{persenDiskon}%
          </div>
        ) : isNew ? (
          <div className="absolute top-0 left-0 z-20 bg-blue-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-br-lg rounded-tl-lg shadow-xs">
            NEW
          </div>
        ) : null}

        <Link
          href={`/produk/${product.id_produk}`}
          className="block aspect-square rounded-lg overflow-hidden bg-gray-50/50"
        >
          {product.gambar ? (
            <img
              src={product.gambar}
              alt={product.nama_produk}
              className="w-full h-full object-contain p-3 group-hover:scale-105 transition duration-300"
              loading="lazy"
            />
          ) : (
            <div className="w-full h-full flex items-center justify-center text-3xl text-gray-300">
              <ImageIcon className="w-8 h-8 text-gray-300" />
            </div>
          )}
        </Link>
      </div>

      {/* 2. Info Produk */}
      <div className="flex flex-col flex-1">
        <Link href={`/produk/${product.id_produk}`} className="block mb-1">
          <h3
            className="font-bold text-gray-800 text-xs leading-snug line-clamp-2 h-8 hover:text-blue-600 transition-colors"
            title={product.nama_produk}
          >
            {product.nama_produk}
          </h3>
        </Link>

        {/* Rating & Terjual */}
        <div className="flex items-center gap-1 mb-1">
          <Star className="w-2.5 h-2.5 fill-yellow-400 text-yellow-400" />
          <span className="text-[10px] text-gray-500">
            {avgRating} <span className="text-gray-300">•</span> {totalTerjual} terjual
          </span>
        </div>

        {/* Harga */}
        <div className="mt-auto">
          <div className="flex flex-wrap items-baseline gap-x-1.5">
            <span
              className={`text-sm font-extrabold ${
                isTop ? 'text-orange-600' : 'text-blue-600'
              }`}
            >
              {formatRupiah(product.harga || product.harga_produk)}
            </span>
            {hasDiskon && (
              <span className="text-[10px] text-gray-400 line-through">
                {formatRupiah(product.harga_produk)}
              </span>
            )}
          </div>
        </div>

        {/* Watermark Peringkat (#1 s/d #6) Khusus Paling Laris */}
        {isTop && rank !== undefined && (
          <span className="absolute right-1 bottom-5 text-[5rem] leading-none font-black text-gray-100 italic select-none pointer-events-none z-10">
            #{rank}
          </span>
        )}
      </div>

      {/* 3. Tombol Keranjang */}
      <div className="mt-2 z-10">
        {!isOutOfStock ? (
          <button
            type="button"
            onClick={handleAddToCart}
            className={`block w-full text-white font-bold text-xs py-2 md:py-1.5 rounded-lg text-center transition shadow-xs active:scale-95 cursor-pointer ${
              isTop
                ? 'bg-orange-500 hover:bg-orange-600'
                : 'bg-blue-600 hover:bg-blue-700'
            }`}
          >
            + Keranjang
          </button>
        ) : (
          <button
            disabled
            className="block w-full bg-gray-100 text-gray-400 font-bold text-xs py-2 md:py-1.5 rounded-lg text-center cursor-not-allowed"
          >
            Habis
          </button>
        )}
      </div>
    </div>
  );
};
