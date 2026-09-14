'use client';

import React, { useState } from 'react';
import { Produk, Kategori } from '@/types';
import { Navbar } from '@/components/Navbar';
import { CategoryPills } from '@/components/CategoryPills';
import { ProductCard } from '@/components/ProductCard';
import { KioskCart } from '@/components/KioskCart';
import { CheckoutModal } from '@/components/CheckoutModal';
import { ReceiptModal } from '@/components/ReceiptModal';
import { Search, Tablet } from 'lucide-react';

interface KioskClientProps {
  products: Produk[];
  categories: Kategori[];
  currentUser?: any;
}

export const KioskClient: React.FC<KioskClientProps> = ({
  products,
  categories,
  currentUser,
}) => {
  const [selectedCat, setSelectedCat] = useState<number>(0);
  const [searchQuery, setSearchQuery] = useState<string>('');
  const [isCheckoutOpen, setIsCheckoutOpen] = useState<boolean>(false);
  const [receiptData, setReceiptData] = useState<any>(null);

  const filteredProducts = products.filter((p) => {
    const matchCat = selectedCat === 0 || p.id_kategori === selectedCat;
    const matchSearch =
      searchQuery.trim() === '' ||
      p.nama_produk.toLowerCase().includes(searchQuery.toLowerCase()) ||
      (p.deskripsi && p.deskripsi.toLowerCase().includes(searchQuery.toLowerCase()));
    return matchCat && matchSearch;
  });

  return (
    <div className="h-screen bg-gray-100/70 flex flex-col overflow-hidden select-none">
      <Navbar currentUser={currentUser} />

      {/* Main Split Screen Area */}
      <div className="flex-1 flex overflow-hidden p-3 sm:p-4 gap-4 max-w-[1600px] w-full mx-auto">
        {/* Left Section: Catalog & Fast Touch Grid (65% width) */}
        <section className="flex-1 flex flex-col bg-white rounded-3xl border border-gray-200/80 shadow-sm overflow-hidden p-4">
          {/* Header Controls: Search + Categories */}
          <div className="space-y-3 pb-3 border-b border-gray-100 shrink-0">
            <div className="flex items-center justify-between gap-3">
              <div className="flex items-center gap-2 text-blue-600">
                <Tablet className="w-5 h-5" />
                <h1 className="font-black text-base tracking-tight text-gray-900">
                  Mode Kasir Cepat (POS)
                </h1>
              </div>

              {/* Tablet Search Input */}
              <div className="relative w-64 sm:w-80">
                <Search className="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
                <input
                  type="text"
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  placeholder="Ketik nama produk..."
                  className="w-full pl-10 pr-4 py-2 rounded-xl bg-gray-100 border-none text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-blue-600"
                />
              </div>
            </div>

            {/* Fast Category Filter */}
            <CategoryPills
              categories={categories}
              selectedCategoryId={selectedCat}
              onSelectCategory={setSelectedCat}
            />
          </div>

          {/* Product Grid Area (Scrollable) */}
          <div className="flex-1 overflow-y-auto pt-3">
            {filteredProducts.length === 0 ? (
              <div className="h-full flex items-center justify-center text-gray-400 font-bold text-sm">
                Produk tidak ditemukan.
              </div>
            ) : (
              <div className="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
                {filteredProducts.map((prod) => (
                  <ProductCard key={prod.id_produk} product={prod} />
                ))}
              </div>
            )}
          </div>
        </section>

        {/* Right Section: Sticky POS Cart (35% width) */}
        <aside className="w-80 sm:w-96 lg:w-[380px] shrink-0 h-full flex flex-col">
          <KioskCart onCheckout={() => setIsCheckoutOpen(true)} />
        </aside>
      </div>

      {/* Checkout Modal */}
      <CheckoutModal
        isOpen={isCheckoutOpen}
        onClose={() => setIsCheckoutOpen(false)}
        onSuccess={(result) => setReceiptData(result)}
      />

      {/* Thermal Receipt Print Modal */}
      <ReceiptModal
        isOpen={!!receiptData}
        onClose={() => setReceiptData(null)}
        trxData={receiptData}
      />
    </div>
  );
};
