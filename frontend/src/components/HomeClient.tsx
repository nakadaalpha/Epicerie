'use client';

import React, { useState, useEffect, useMemo, useRef } from 'react';
import { Produk, Kategori, Slider } from '@/types';
import { Navbar } from '@/components/Navbar';
import { ProductCard } from '@/components/ProductCard';
import { CheckoutModal } from '@/components/CheckoutModal';
import { ReceiptModal } from '@/components/ReceiptModal';
import { KioskCart } from '@/components/KioskCart';
import { useCartStore } from '@/store/useCartStore';
import { formatRupiah } from '@/lib/utils';
import {
  Clock,
  Flame,
  ChevronLeft,
  ChevronRight,
  ChevronDown,
  ShoppingBag,
  ArrowRight,
  X,
  PackageOpen,
} from 'lucide-react';

interface HomeClientProps {
  initialProducts: Produk[];
  categories: Kategori[];
  sliders: Slider[];
  currentUser?: any;
}

export const HomeClient: React.FC<HomeClientProps> = ({
  initialProducts,
  categories,
  sliders,
  currentUser,
}) => {
  const [searchQuery, setSearchQuery] = useState<string>('');
  const [selectedCat, setSelectedCat] = useState<number>(0);
  const [isMobileCartOpen, setIsMobileCartOpen] = useState<boolean>(false);
  const [isCheckoutOpen, setIsCheckoutOpen] = useState<boolean>(false);
  const [receiptData, setReceiptData] = useState<any>(null);

  // Pagination for "Semua Produk"
  const [visibleCount, setVisibleCount] = useState<number>(12);

  // Slider state
  const [activeSlide, setActiveSlide] = useState<number>(0);
  const [isPaused, setIsPaused] = useState<boolean>(false);

  const { getTotalItems, getTotalPrice } = useCartStore();
  const totalItems = getTotalItems();
  const totalPrice = getTotalPrice();

  // 1. Slider Carousel Auto-advance
  useEffect(() => {
    if (!sliders || sliders.length <= 1 || isPaused) return;
    const interval = setInterval(() => {
      setActiveSlide((prev) => (prev + 1) % sliders.length);
    }, 4000);
    return () => clearInterval(interval);
  }, [sliders, isPaused]);

  const handlePrevSlide = () => {
    if (!sliders || sliders.length === 0) return;
    setActiveSlide((prev) => (prev - 1 + sliders.length) % sliders.length);
  };

  const handleNextSlide = () => {
    if (!sliders || sliders.length === 0) return;
    setActiveSlide((prev) => (prev + 1) % sliders.length);
  };

  // 2. Data Slices matching Legacy KioskController
  // Produk Terbaru: 6 produk terbaru (terurut id_produk / created_at desc)
  const produkTerbaru = useMemo(() => {
    return [...initialProducts]
      .sort((a, b) => Number(b.id_produk) - Number(a.id_produk))
      .slice(0, 6);
  }, [initialProducts]);

  // Produk Terlaris: 6 produk dengan total_terjual tertinggi
  const produkTerlaris = useMemo(() => {
    return [...initialProducts]
      .sort((a, b) => (b.total_terjual || 0) - (a.total_terjual || 0))
      .slice(0, 6);
  }, [initialProducts]);

  // Search or Category filter
  const isFiltering = searchQuery.trim().length > 0 || selectedCat > 0;
  const filteredProducts = useMemo(() => {
    return initialProducts.filter((p) => {
      const matchCat = selectedCat === 0 || Number(p.id_kategori) === selectedCat;
      const matchSearch =
        searchQuery.trim() === '' ||
        p.nama_produk.toLowerCase().includes(searchQuery.toLowerCase()) ||
        (p.deskripsi && p.deskripsi.toLowerCase().includes(searchQuery.toLowerCase()));
      return matchCat && matchSearch;
    });
  }, [initialProducts, searchQuery, selectedCat]);

  // Semua Produk: list catalog dengan load more
  const displayedSemuaProduk = useMemo(() => {
    return initialProducts.slice(0, visibleCount);
  }, [initialProducts, visibleCount]);

  const hasMore = visibleCount < initialProducts.length;

  const handleLoadMore = () => {
    setVisibleCount((prev) => prev + 12);
  };

  const selectedCategoryName = useMemo(() => {
    if (!selectedCat) return '';
    const cat = categories.find((c) => Number(c.id_kategori) === selectedCat);
    return cat ? cat.nama_kategori : '';
  }, [categories, selectedCat]);

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col font-sans">
      {/* NAVBAR DENGAN LOGO ÉPICERIE, MODAL KATEGORI, MODAL ALAMAT, MODAL MEMBER */}
      <Navbar
        currentUser={currentUser}
        categories={categories}
        searchQuery={searchQuery}
        onSearchChange={setSearchQuery}
        onSelectCategory={(id) => setSelectedCat(id)}
        onOpenMobileCart={() => setIsMobileCartOpen(true)}
      />

      <main className="flex-1 w-full pb-20">
        {/* JIKA SEDANG MELAKUKAN PENCARIAN ATAU FILTER KATEGORI */}
        {isFiltering ? (
          <div className="max-w-7xl mx-auto px-4 mt-6 pb-24">
            <div className="flex items-center justify-between mb-4 bg-white p-4 rounded-xl border border-gray-100 shadow-xs">
              <div>
                <h2 className="font-extrabold text-gray-800 text-lg">
                  {selectedCat > 0
                    ? `Kategori: ${selectedCategoryName}`
                    : `Hasil Pencarian: "${searchQuery}"`}
                </h2>
                <p className="text-xs text-gray-500 mt-0.5">
                  Ditemukan {filteredProducts.length} produk
                </p>
              </div>
              <button
                onClick={() => {
                  setSearchQuery('');
                  setSelectedCat(0);
                }}
                className="text-xs text-blue-600 font-bold hover:underline cursor-pointer"
              >
                Reset Filter
              </button>
            </div>

            {filteredProducts.length === 0 ? (
              <div className="col-span-full text-center py-20 bg-white rounded-xl border border-dashed border-gray-300">
                <div className="inline-block p-4 rounded-full mb-3 text-gray-300">
                  <PackageOpen className="w-12 h-12 mx-auto" />
                </div>
                <p className="text-gray-500 font-bold">Produk tidak ditemukan.</p>
                <p className="text-xs text-gray-400">
                  Coba kata kunci lain atau reset filter.
                </p>
              </div>
            ) : (
              <div className="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                {filteredProducts.map((p) => (
                  <ProductCard key={p.id_produk} product={p} variant="default" />
                ))}
              </div>
            )}
          </div>
        ) : (
          <>
            {/* 1. SLIDER BANNER PROMO CAROUSEL (ASLI SESUAI LEGACY KIOSK) */}
            {sliders && sliders.length > 0 && (
              <div className="max-w-7xl mx-auto px-4 mt-6">
                <div
                  className="relative w-full rounded-2xl overflow-hidden shadow-xs group aspect-[3/1] md:aspect-[3.5/1] bg-gray-100"
                  onMouseEnter={() => setIsPaused(true)}
                  onMouseLeave={() => setIsPaused(false)}
                >
                  <div
                    className="flex h-full w-full transition-transform duration-500 ease-in-out"
                    style={{ transform: `translateX(-${activeSlide * 100}%)` }}
                  >
                    {sliders.map((s, idx) => (
                      <div
                        key={s.id_slider || idx}
                        className="min-w-full h-full relative"
                      >
                        <img
                          src={s.gambar}
                          alt={s.judul || `Slider Promo ${idx + 1}`}
                          className="w-full h-full object-cover"
                        />
                      </div>
                    ))}
                  </div>

                  {/* Tombol Prev Slide (Muncul Saat Hover) */}
                  {sliders.length > 1 && (
                    <>
                      <button
                        type="button"
                        onClick={handlePrevSlide}
                        className="absolute top-1/2 -translate-y-1/2 z-20 bg-white text-slate-500 hover:text-slate-800 w-9 h-9 rounded-full shadow-md flex items-center justify-center transition-all duration-300 opacity-0 group-hover:opacity-100 translate-x-4 group-hover:translate-x-0 left-4 cursor-pointer"
                        aria-label="Previous Slide"
                      >
                        <ChevronLeft className="w-4 h-4" />
                      </button>
                      <button
                        type="button"
                        onClick={handleNextSlide}
                        className="absolute top-1/2 -translate-y-1/2 z-20 bg-white text-slate-500 hover:text-slate-800 w-9 h-9 rounded-full shadow-md flex items-center justify-center transition-all duration-300 opacity-0 group-hover:opacity-100 -translate-x-4 group-hover:translate-x-0 right-4 cursor-pointer"
                        aria-label="Next Slide"
                      >
                        <ChevronRight className="w-4 h-4" />
                      </button>
                    </>
                  )}

                  {/* Indikator Dot Putih di Tengah Bawah */}
                  {sliders.length > 1 && (
                    <div className="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1 z-10">
                      {sliders.map((_, idx) => (
                        <button
                          key={idx}
                          type="button"
                          onClick={() => setActiveSlide(idx)}
                          className={`h-1.5 rounded-full transition-all duration-300 ${
                            idx === activeSlide
                              ? 'bg-white w-4 shadow-xs'
                              : 'bg-white/40 w-1.5 hover:bg-white/70'
                          }`}
                          aria-label={`Slide ${idx + 1}`}
                        />
                      ))}
                    </div>
                  )}
                </div>
              </div>
            )}

            {/* CONTAINER 2 SEKSI ATAS: PRODUK TERBARU & PALING LARIS */}
            <div className="max-w-7xl mx-auto px-4 mt-6 space-y-10">
              {/* SEKSI 1: PRODUK TERBARU (IKON JAM BIRU, 6 KOLOM, BADGE NEW) */}
              {produkTerbaru.length > 0 && (
                <div>
                  <div className="flex items-center justify-between mb-4">
                    <div className="flex items-center gap-2">
                      <div className="bg-blue-600 text-white w-7 h-7 rounded-lg flex items-center justify-center shadow-blue-200 shadow-md">
                        <Clock className="w-3.5 h-3.5" />
                      </div>
                      <h2 className="font-extrabold text-gray-800 text-lg leading-tight">
                        Produk Terbaru
                      </h2>
                    </div>
                  </div>

                  <div className="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                    {produkTerbaru.map((p) => (
                      <ProductCard key={p.id_produk} product={p} variant="new" />
                    ))}
                  </div>
                </div>
              )}

              {/* SEKSI 2: PALING LARIS (IKON API ORANYE, 6 KOLOM, WATERMARK #1-#6, HARGA & TOMBOL ORANYE) */}
              {produkTerlaris.length > 0 && (
                <div>
                  <div className="flex items-center gap-2 mb-4">
                    <div className="bg-orange-500 text-white w-7 h-7 rounded-lg flex items-center justify-center shadow-orange-200 shadow-md">
                      <Flame className="w-3.5 h-3.5" />
                    </div>
                    <h2 className="font-extrabold text-gray-800 text-lg leading-tight">
                      Paling Laris
                    </h2>
                  </div>

                  <div className="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                    {produkTerlaris.map((p, idx) => (
                      <ProductCard
                        key={p.id_produk}
                        product={p}
                        variant="top"
                        rank={idx + 1}
                      />
                    ))}
                  </div>
                </div>
              )}
            </div>

            {/* SEKSI 3: SEMUA PRODUK (STICKY HEADER GARIS BIRU, 6 KOLOM, TOMBOL MUAT LEBIH BANYAK) */}
            <div className="max-w-7xl mx-auto px-4 pb-24 mt-8">
              <div className="flex justify-between items-center mb-6 sticky top-[70px] backdrop-blur-md py-2 z-30 bg-white/90 rounded-lg px-3 border border-gray-100 shadow-xs">
                <div className="flex items-center gap-2">
                  <div className="w-1 h-5 bg-blue-600 rounded-full"></div>
                  <h2 className="font-extrabold text-gray-700 text-base">
                    Semua Produk
                  </h2>
                </div>
              </div>

              {displayedSemuaProduk.length === 0 ? (
                <div className="col-span-full text-center py-20 bg-white rounded-xl border border-dashed border-gray-300">
                  <div className="inline-block p-4 rounded-full mb-3 text-gray-300">
                    <PackageOpen className="w-12 h-12 mx-auto" />
                  </div>
                  <p className="text-gray-500 font-bold">Produk tidak ditemukan.</p>
                </div>
              ) : (
                <div className="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-3">
                  {displayedSemuaProduk.map((p) => (
                    <ProductCard
                      key={p.id_produk}
                      product={p}
                      variant="default"
                    />
                  ))}
                </div>
              )}

              {/* Tombol Muat Lebih Banyak */}
              {hasMore && (
                <div className="mt-8 flex justify-center">
                  <button
                    type="button"
                    onClick={handleLoadMore}
                    className="bg-white border border-gray-300 text-gray-600 font-bold py-2.5 px-6 rounded-full shadow-xs hover:bg-blue-50 hover:border-blue-300 hover:text-blue-600 transition-all flex items-center gap-2 group text-sm cursor-pointer active:scale-95"
                  >
                    <span>Muat Lebih Banyak</span>
                    <ChevronDown className="w-4 h-4 group-hover:translate-y-0.5 transition-transform" />
                  </button>
                </div>
              )}
            </div>
          </>
        )}
      </main>

      {/* FLOATING BOTTOM CART BAR UNTUK MOBILE */}
      {totalItems > 0 && (
        <div className="md:hidden fixed bottom-18 inset-x-4 z-40">
          <button
            onClick={() => setIsMobileCartOpen(true)}
            className="w-full bg-blue-600 hover:bg-blue-700 text-white p-3.5 rounded-2xl shadow-xl shadow-blue-500/30 flex items-center justify-between font-bold text-sm transition active:scale-98 cursor-pointer"
          >
            <div className="flex items-center gap-3">
              <div className="w-8 h-8 rounded-xl bg-white/20 flex items-center justify-center">
                <ShoppingBag className="w-4 h-4" />
              </div>
              <div className="text-left">
                <span className="block text-xs font-medium text-blue-100">
                  {totalItems} item
                </span>
                <span className="text-base font-extrabold">
                  {formatRupiah(totalPrice)}
                </span>
              </div>
            </div>
            <div className="flex items-center gap-1.5 text-xs font-bold bg-white/20 px-3 py-1.5 rounded-xl">
              <span>Buka Keranjang</span>
              <ArrowRight className="w-3.5 h-3.5" />
            </div>
          </button>
        </div>
      )}

      {/* MOBILE SLIDE-UP CART DRAWER */}
      {isMobileCartOpen && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex flex-col justify-end">
          <div className="bg-white rounded-t-3xl h-[85vh] flex flex-col overflow-hidden animate-in slide-in-from-bottom duration-200">
            <div className="p-4 border-b border-gray-100 flex items-center justify-between">
              <h3 className="font-extrabold text-base text-gray-900">
                Keranjang Pesanan
              </h3>
              <button
                onClick={() => setIsMobileCartOpen(false)}
                className="p-2 rounded-xl text-gray-400 hover:bg-gray-100 cursor-pointer"
              >
                <X className="w-5 h-5" />
              </button>
            </div>
            <div className="flex-1 overflow-hidden p-4">
              <KioskCart
                onCheckout={() => {
                  setIsMobileCartOpen(false);
                  setIsCheckoutOpen(true);
                }}
              />
            </div>
          </div>
        </div>
      )}

      {/* CHECKOUT MODAL */}
      <CheckoutModal
        isOpen={isCheckoutOpen}
        onClose={() => setIsCheckoutOpen(false)}
        onSuccess={(result) => {
          setReceiptData(result);
        }}
      />

      {/* RECEIPT MODAL */}
      <ReceiptModal
        isOpen={!!receiptData}
        onClose={() => setReceiptData(null)}
        trxData={receiptData}
      />
    </div>
  );
};
