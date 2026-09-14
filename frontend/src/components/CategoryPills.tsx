'use client';

import React from 'react';
import { Kategori } from '@/types';
import { LayoutGrid } from 'lucide-react';

interface CategoryPillsProps {
  categories: Kategori[];
  selectedCategoryId: number;
  onSelectCategory: (id: number) => void;
}

export const CategoryPills: React.FC<CategoryPillsProps> = ({
  categories,
  selectedCategoryId,
  onSelectCategory,
}) => {
  return (
    <div className="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-none no-scrollbar py-1">
      <button
        onClick={() => onSelectCategory(0)}
        className={`flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs shrink-0 transition-all shadow-sm ${
          selectedCategoryId === 0
            ? 'bg-blue-600 text-white shadow-blue-500/25 scale-[1.02]'
            : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-100'
        }`}
      >
        <LayoutGrid className="w-4 h-4" />
        <span>Semua Produk</span>
      </button>

      {categories.map((cat) => {
        const isSelected = selectedCategoryId === cat.id_kategori;
        return (
          <button
            key={cat.id_kategori}
            onClick={() => onSelectCategory(cat.id_kategori)}
            className={`flex items-center gap-2 px-4 py-2.5 rounded-xl font-bold text-xs shrink-0 transition-all shadow-sm ${
              isSelected
                ? 'bg-blue-600 text-white shadow-blue-500/25 scale-[1.02]'
                : 'bg-white text-gray-700 hover:bg-gray-100 border border-gray-100'
            }`}
          >
            {cat.gambar ? (
              <img
                src={cat.gambar}
                alt={cat.nama_kategori}
                className="w-4 h-4 object-contain"
              />
            ) : null}
            <span>{cat.nama_kategori}</span>
          </button>
        );
      })}
    </div>
  );
};
