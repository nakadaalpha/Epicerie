'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import {
  Star,
  ShoppingBag,
  Plus,
  Minus,
  ChevronRight,
  ShieldCheck,
  Truck,
  CheckCircle2,
  AlertCircle,
  Clock,
  Send,
  Sparkles,
  ArrowLeft,
  Share2,
} from 'lucide-react';
import { Produk } from '@/types';
import { useCartStore } from '@/store/useCartStore';
import { formatRupiah } from '@/lib/utils';
import { submitReviewAction, ReviewItem } from '@/app/actions/review';

interface ProductDetailClientProps {
  product: Produk & {
    avg_rating?: number;
    total_ulasan?: number;
    reviews?: ReviewItem[];
  };
  currentUser?: any;
}

export const ProductDetailClient: React.FC<ProductDetailClientProps> = ({
  product,
  currentUser,
}) => {
  const router = useRouter();
  const addItem = useCartStore((state) => state.addItem);

  const [quantity, setQuantity] = useState(1);
  const [activeTab, setActiveTab] = useState<'detail' | 'ulasan'>('detail');
  const [ratingFilter, setRatingFilter] = useState<number | 'all'>('all');
  const [toastMessage, setToastMessage] = useState<string | null>(null);

  // Review Form state
  const [reviewRating, setReviewRating] = useState(5);
  const [reviewComment, setReviewComment] = useState('');
  const [submittingReview, setSubmittingReview] = useState(false);
  const [reviewError, setReviewError] = useState<string | null>(null);
  const [reviewSuccess, setReviewSuccess] = useState<string | null>(null);

  const price = Number(product.harga_produk || product.harga || 0);
  const stock = Number(product.stok || 0);
  const avgRating = Number(product.avg_rating || 0);
  const totalUlasan = Number(product.total_ulasan || 0);
  const reviews = product.reviews || [];

  // Membership discount calculation
  const discountPercent = currentUser?.discountPercent || 0;
  const discountedPrice = discountPercent > 0 ? price * (1 - discountPercent / 100) : price;
  const subtotal = discountedPrice * quantity;

  // Rating distribution calculation
  const distribution: Record<number, number> = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 };
  reviews.forEach((r) => {
    const star = Number(r.rating || 0);
    if (star >= 1 && star <= 5) {
      distribution[star] = (distribution[star] || 0) + 1;
    }
  });

  const filteredReviews = ratingFilter === 'all'
    ? reviews
    : reviews.filter((r) => Number(r.rating) === ratingFilter);

  const showToast = (msg: string) => {
    setToastMessage(msg);
    setTimeout(() => setToastMessage(null), 3000);
  };

  const handleAddToCart = () => {
    if (stock <= 0) return;
    addItem(product, quantity);
    showToast(`Berhasil menambahkan ${quantity} item ke keranjang!`);
  };

  const handleBuyNow = () => {
    if (stock <= 0) return;
    addItem(product, quantity);
    router.push('/kiosk');
  };

  const handleSubmitReview = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!currentUser) {
      router.push('/login');
      return;
    }

    if (!reviewComment.trim()) {
      setReviewError('Silakan tulis ulasan Anda terlebih dahulu.');
      return;
    }

    setSubmittingReview(true);
    setReviewError(null);
    setReviewSuccess(null);

    const res = await submitReviewAction({
      id_produk: product.id_produk,
      rating: reviewRating,
      komentar: reviewComment.trim(),
    });

    setSubmittingReview(false);

    if (res.success) {
      setReviewSuccess(res.message || 'Ulasan berhasil diterbitkan!');
      setReviewComment('');
      setTimeout(() => {
        router.refresh();
      }, 1000);
    } else {
      setReviewError(res.error || 'Gagal mengirim ulasan.');
    }
  };

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      {/* Toast Notification */}
      {toastMessage && (
        <div className="fixed top-20 left-1/2 -translate-x-1/2 z-50 bg-blue-600 text-white px-5 py-3 rounded-2xl shadow-xl flex items-center gap-2.5 text-sm font-semibold animate-bounce">
          <CheckCircle2 className="w-5 h-5 text-blue-200" />
          <span>{toastMessage}</span>
        </div>
      )}

      {/* Main Container */}
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex-1 w-full">
        {/* Breadcrumb Navigation */}
        <nav className="flex items-center gap-2 text-xs sm:text-sm text-gray-500 mb-6 overflow-x-auto whitespace-nowrap pb-1">
          <Link href="/" className="hover:text-blue-600 transition flex items-center gap-1 font-medium">
            <ArrowLeft className="w-4 h-4 sm:hidden" />
            <span>Katalog</span>
          </Link>
          <ChevronRight className="w-3.5 h-3.5 text-gray-400 shrink-0" />
          {product.nama_kategori && (
            <>
              <span className="text-gray-600 font-medium">{product.nama_kategori}</span>
              <ChevronRight className="w-3.5 h-3.5 text-gray-400 shrink-0" />
            </>
          )}
          <span className="text-gray-900 font-bold truncate max-w-xs">{product.nama_produk}</span>
        </nav>

        {/* Product Grid: 3 Columns on Desktop */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
          {/* Column 1: Image Gallery (5 cols) */}
          <div className="lg:col-span-5">
            <div className="bg-white rounded-3xl p-6 border border-gray-200/80 shadow-xs relative overflow-hidden group">
              {discountPercent > 0 && (
                <div className="absolute top-4 left-4 z-10 bg-amber-500 text-white text-[11px] font-black px-3 py-1.5 rounded-full shadow-md flex items-center gap-1">
                  <Sparkles className="w-3.5 h-3.5" />
                  MEMBER {currentUser?.membership?.toUpperCase()} -{discountPercent}%
                </div>
              )}

              <div className="aspect-square rounded-2xl bg-gray-50 flex items-center justify-center overflow-hidden p-4">
                {product.gambar ? (
                  <img
                    src={product.gambar}
                    alt={product.nama_produk}
                    className="w-full h-full object-contain group-hover:scale-105 transition-transform duration-500"
                  />
                ) : (
                  <div className="text-gray-300 flex flex-col items-center">
                    <ShoppingBag className="w-20 h-20" />
                    <span className="text-xs font-semibold mt-2">Belum ada gambar</span>
                  </div>
                )}
              </div>

              {/* Guarantees Box */}
              <div className="grid grid-cols-2 gap-3 mt-4 pt-4 border-t border-gray-100 text-[11px] text-gray-600">
                <div className="flex items-center gap-2">
                  <ShieldCheck className="w-4 h-4 text-blue-600 shrink-0" />
                  <span>Kualitas Segar Terjamin</span>
                </div>
                <div className="flex items-center gap-2">
                  <Truck className="w-4 h-4 text-blue-600 shrink-0" />
                  <span>Pengiriman Instan Kurir</span>
                </div>
              </div>
            </div>
          </div>

          {/* Column 2: Details & Tabs (4 cols) */}
          <div className="lg:col-span-4 space-y-6">
            <div>
              <span className="text-[11px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2.5 py-1 rounded-md">
                {product.nama_kategori || 'Kategori Umum'}
              </span>
              <h1 className="text-2xl sm:text-3xl font-black text-gray-900 mt-2 leading-tight">
                {product.nama_produk}
              </h1>

              {/* Rating & Sold Meta */}
              <div className="flex items-center gap-3 mt-3 text-xs">
                <button
                  onClick={() => setActiveTab('ulasan')}
                  className="flex items-center gap-1 text-amber-500 hover:text-amber-600 font-bold cursor-pointer"
                >
                  <Star className="w-4 h-4 fill-amber-400 text-amber-400" />
                  <span>{avgRating.toFixed(1)}</span>
                  <span className="text-gray-500 font-medium">({totalUlasan} Ulasan)</span>
                </button>
                <span className="text-gray-300">•</span>
                <span className="text-gray-600">Terjual <strong className="text-gray-900">{product.total_terjual || 0}</strong> item</span>
                <span className="text-gray-300">•</span>
                <span className={`font-semibold ${stock > 10 ? 'text-blue-600' : stock > 0 ? 'text-amber-600' : 'text-red-600'}`}>
                  {stock > 0 ? `Stok: ${stock}` : 'Stok Habis'}
                </span>
              </div>
            </div>

            {/* Pricing Box */}
            <div className="bg-blue-50/60 border border-blue-100 p-5 rounded-2xl">
              <div className="flex items-baseline gap-2">
                <span className="text-3xl font-black text-blue-600">
                  {formatRupiah(discountedPrice)}
                </span>
                {discountPercent > 0 && (
                  <span className="text-sm text-gray-400 line-through font-semibold">
                    {formatRupiah(price)}
                  </span>
                )}
              </div>
              {discountPercent > 0 ? (
                <p className="text-[11px] text-blue-800 font-medium mt-1">
                  🎉 Harga spesial member {currentUser?.membership} (hemat {formatRupiah(price - discountedPrice)} per item)
                </p>
              ) : (
                <p className="text-[11px] text-gray-500 mt-1">
                  Gabung member untuk dapatkan diskon otomatis hingga 10% di setiap pesanan!
                </p>
              )}
            </div>

            {/* Tab Controls: Detail Produk vs Ulasan */}
            <div>
              <div className="flex border-b border-gray-200">
                <button
                  onClick={() => setActiveTab('detail')}
                  className={`pb-3 px-4 text-xs font-bold transition-all relative ${
                    activeTab === 'detail'
                      ? 'text-blue-600 border-b-2 border-blue-600'
                      : 'text-gray-500 hover:text-gray-800'
                  }`}
                >
                  Detail & Deskripsi
                </button>
                <button
                  onClick={() => setActiveTab('ulasan')}
                  className={`pb-3 px-4 text-xs font-bold transition-all relative flex items-center gap-1.5 ${
                    activeTab === 'ulasan'
                      ? 'text-blue-600 border-b-2 border-blue-600'
                      : 'text-gray-500 hover:text-gray-800'
                  }`}
                >
                  <span>Ulasan Pembeli</span>
                  <span className="px-1.5 py-0.5 rounded-full text-[10px] bg-blue-100 text-blue-800 font-extrabold">
                    {totalUlasan}
                  </span>
                </button>
              </div>

              {/* Tab Content: Detail */}
              {activeTab === 'detail' && (
                <div className="py-4 text-xs text-gray-700 leading-relaxed space-y-3">
                  <p>{product.deskripsi_produk || product.deskripsi || 'Tidak ada deskripsi detail untuk produk ini.'}</p>
                  <div className="bg-white rounded-xl p-3.5 border border-gray-200/80 space-y-2 text-[11px]">
                    <div className="flex justify-between py-1 border-b border-gray-100">
                      <span className="text-gray-500">Kategori</span>
                      <span className="font-semibold text-gray-900">{product.nama_kategori || '-'}</span>
                    </div>
                    <div className="flex justify-between py-1 border-b border-gray-100">
                      <span className="text-gray-500">Kondisi</span>
                      <span className="font-semibold text-emerald-700">Baru / Fresh</span>
                    </div>
                    <div className="flex justify-between py-1">
                      <span className="text-gray-500">Min. Pembelian</span>
                      <span className="font-semibold text-gray-900">1 unit</span>
                    </div>
                  </div>
                </div>
              )}

              {/* Tab Content: Ulasan */}
              {activeTab === 'ulasan' && (
                <div className="py-4 space-y-5">
                  {/* Rating Summary Card */}
                  <div className="bg-white p-4 rounded-2xl border border-gray-200/80 flex items-center gap-4">
                    <div className="text-center px-3">
                      <span className="text-3xl font-black text-gray-900 block leading-none">
                        {avgRating.toFixed(1)}
                      </span>
                      <div className="flex justify-center text-amber-400 my-1">
                        {[1, 2, 3, 4, 5].map((s) => (
                          <Star
                            key={s}
                            className={`w-3.5 h-3.5 ${s <= Math.round(avgRating) ? 'fill-amber-400' : 'text-gray-300'}`}
                          />
                        ))}
                      </div>
                      <span className="text-[10px] text-gray-500 block font-medium">
                        {totalUlasan} ulasan
                      </span>
                    </div>

                    {/* Progress Bars */}
                    <div className="flex-1 space-y-1 text-[10px]">
                      {[5, 4, 3, 2, 1].map((star) => {
                        const count = distribution[star] || 0;
                        const pct = totalUlasan > 0 ? (count / totalUlasan) * 100 : 0;
                        return (
                          <div key={star} className="flex items-center gap-2">
                            <span className="w-3 text-gray-600 font-semibold">{star}</span>
                            <div className="flex-1 h-2 bg-gray-100 rounded-full overflow-hidden">
                              <div
                                className="h-full bg-amber-400 rounded-full"
                                style={{ width: `${pct}%` }}
                              />
                            </div>
                            <span className="w-5 text-right text-gray-400 font-medium">{count}</span>
                          </div>
                        );
                      })}
                    </div>
                  </div>

                  {/* Filter Star Buttons */}
                  <div className="flex flex-wrap gap-1.5">
                    <button
                      onClick={() => setRatingFilter('all')}
                      className={`px-3 py-1 rounded-full text-[11px] font-bold transition ${
                        ratingFilter === 'all'
                          ? 'bg-blue-600 text-white shadow-xs'
                          : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-100'
                      }`}
                    >
                      Semua ({totalUlasan})
                    </button>
                    {[5, 4, 3, 2, 1].map((s) => (
                      <button
                        key={s}
                        onClick={() => setRatingFilter(s)}
                        className={`px-2.5 py-1 rounded-full text-[11px] font-semibold transition flex items-center gap-1 ${
                          ratingFilter === s
                            ? 'bg-blue-600 text-white shadow-xs'
                            : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-100'
                        }`}
                      >
                        <Star className="w-3 h-3 fill-amber-400 text-amber-400" />
                        <span>{s}</span>
                        <span className="text-[10px] opacity-75">({distribution[s] || 0})</span>
                      </button>
                    ))}
                  </div>

                  {/* Write Review Section */}
                  <div className="bg-blue-50/50 p-4 rounded-2xl border border-blue-100 space-y-3">
                    <h4 className="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                      <Send className="w-3.5 h-3.5 text-blue-600" />
                      Tulis Ulasan untuk Produk Ini
                    </h4>
                    {currentUser ? (
                      <form onSubmit={handleSubmitReview} className="space-y-3">
                        {reviewError && (
                          <div className="p-2.5 rounded-xl bg-red-50 border border-red-200 text-red-700 text-[11px] flex items-center gap-1.5">
                            <AlertCircle className="w-3.5 h-3.5 shrink-0" />
                            <span>{reviewError}</span>
                          </div>
                        )}
                        {reviewSuccess && (
                          <div className="p-2.5 rounded-xl bg-blue-100 border border-blue-300 text-blue-800 text-[11px] flex items-center gap-1.5">
                            <CheckCircle2 className="w-3.5 h-3.5 shrink-0" />
                            <span>{reviewSuccess}</span>
                          </div>
                        )}
                        <div>
                          <label className="block text-[11px] font-semibold text-gray-700 mb-1">
                            Beri Rating Bintang:
                          </label>
                          <div className="flex items-center gap-1">
                            {[1, 2, 3, 4, 5].map((star) => (
                              <button
                                type="button"
                                key={star}
                                onClick={() => setReviewRating(star)}
                                className="p-1 hover:scale-110 transition cursor-pointer"
                              >
                                <Star
                                  className={`w-5 h-5 ${
                                    star <= reviewRating ? 'fill-amber-400 text-amber-400' : 'text-gray-300'
                                  }`}
                                />
                              </button>
                            ))}
                            <span className="text-xs font-bold text-amber-600 ml-2">
                              {reviewRating} dari 5 Bintang
                            </span>
                          </div>
                        </div>

                        <div>
                          <textarea
                            rows={3}
                            value={reviewComment}
                            onChange={(e) => setReviewComment(e.target.value)}
                            placeholder="Bagaimana kualitas produk ini? Ceritakan pengalaman belanja Anda..."
                            className="w-full p-2.5 rounded-xl bg-white border border-gray-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
                          />
                        </div>

                        <button
                          type="submit"
                          disabled={submittingReview}
                          className="w-full py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-xs disabled:opacity-50"
                        >
                          {submittingReview ? 'Mengirim Ulasan...' : 'Kirim Ulasan Sekarang'}
                        </button>
                      </form>
                    ) : (
                      <div className="text-center py-3 bg-white rounded-xl border border-gray-200 text-xs text-gray-500">
                        <p>Silakan masuk ke akun Anda untuk memberikan ulasan produk ini.</p>
                        <Link
                          href="/login"
                          className="inline-block mt-2 px-4 py-1.5 bg-blue-600 text-white font-bold rounded-lg text-xs hover:bg-blue-700 transition"
                        >
                          Masuk Akun
                        </Link>
                      </div>
                    )}
                  </div>

                  {/* Reviews List */}
                  <div className="space-y-3">
                    {filteredReviews.length > 0 ? (
                      filteredReviews.map((rev) => (
                        <div
                          key={rev.id_ulasan}
                          className="bg-white p-3.5 rounded-2xl border border-gray-200/70 shadow-2xs space-y-2"
                        >
                          <div className="flex items-center justify-between">
                            <div className="flex items-center gap-2">
                              <div className="w-7 h-7 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center">
                                {rev.nama_user?.charAt(0) || 'U'}
                              </div>
                              <div>
                                <span className="font-bold text-gray-900 text-xs block">
                                  {rev.nama_user || 'Pelanggan'}
                                </span>
                                <div className="flex text-amber-400">
                                  {[1, 2, 3, 4, 5].map((s) => (
                                    <Star
                                      key={s}
                                      className={`w-3 h-3 ${
                                        s <= rev.rating ? 'fill-amber-400' : 'text-gray-200'
                                      }`}
                                    />
                                  ))}
                                </div>
                              </div>
                            </div>
                            <span className="text-[10px] text-gray-400">
                              {new Date(rev.created_at).toLocaleDateString('id-ID', {
                                day: 'numeric',
                                month: 'short',
                                year: 'numeric',
                              })}
                            </span>
                          </div>
                          <p className="text-xs text-gray-700 pl-9 italic leading-relaxed">
                            &ldquo;{rev.komentar}&rdquo;
                          </p>
                        </div>
                      ))
                    ) : (
                      <div className="text-center py-6 bg-white rounded-2xl border border-gray-200 text-xs text-gray-400">
                        Belum ada ulasan untuk kriteria filter ini.
                      </div>
                    )}
                  </div>
                </div>
              )}
            </div>
          </div>

          {/* Column 3: Sticky Buy Action Card (3 cols) */}
          <div className="lg:col-span-3">
            <div className="bg-white rounded-3xl p-5 border border-gray-200 shadow-sm sticky top-24 space-y-4">
              <h3 className="font-black text-gray-900 text-sm">Atur Jumlah & Beli</h3>

              {/* Quantity Selector */}
              <div>
                <span className="text-xs text-gray-500 block mb-1.5 font-medium">Jumlah Pembelian:</span>
                <div className="flex items-center justify-between border border-gray-200 rounded-2xl p-1 bg-gray-50">
                  <button
                    onClick={() => setQuantity((q) => Math.max(1, q - 1))}
                    disabled={quantity <= 1}
                    className="w-9 h-9 rounded-xl bg-white shadow-xs flex items-center justify-center text-gray-700 hover:bg-gray-100 disabled:opacity-40 transition"
                  >
                    <Minus className="w-4 h-4" />
                  </button>
                  <span className="font-extrabold text-sm text-gray-900">{quantity}</span>
                  <button
                    onClick={() => setQuantity((q) => Math.min(stock, q + 1))}
                    disabled={quantity >= stock}
                    className="w-9 h-9 rounded-xl bg-white shadow-xs flex items-center justify-center text-gray-700 hover:bg-gray-100 disabled:opacity-40 transition"
                  >
                    <Plus className="w-4 h-4" />
                  </button>
                </div>
              </div>

              {/* Subtotal Display */}
              <div className="pt-2 border-t border-gray-100 flex items-baseline justify-between">
                <span className="text-xs font-semibold text-gray-500">Subtotal:</span>
                <div className="text-right">
                  <span className="text-xl font-black text-gray-900 block">
                    {formatRupiah(subtotal)}
                  </span>
                  {discountPercent > 0 && (
                    <span className="text-[10px] text-blue-600 font-bold">
                      Hemat {formatRupiah((price - discountedPrice) * quantity)}
                    </span>
                  )}
                </div>
              </div>

              {/* Action Buttons */}
              <div className="space-y-2 pt-2">
                <button
                  onClick={handleAddToCart}
                  disabled={stock <= 0}
                  className="w-full py-3 px-4 rounded-2xl bg-blue-50 text-blue-700 border border-blue-200 font-extrabold text-xs hover:bg-blue-100 transition shadow-xs flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer"
                >
                  <ShoppingBag className="w-4 h-4 text-blue-600" />
                  <span>+ Keranjang</span>
                </button>

                <button
                  onClick={handleBuyNow}
                  disabled={stock <= 0}
                  className="w-full py-3 px-4 rounded-2xl bg-blue-600 text-white font-extrabold text-xs hover:bg-blue-700 transition shadow-md shadow-blue-500/20 disabled:opacity-50 cursor-pointer"
                >
                  {stock > 0 ? 'Beli Sekarang' : 'Stok Habis'}
                </button>
              </div>

              <div className="pt-3 border-t border-gray-100 text-[11px] text-gray-400 text-center">
                <p>Belanja aman & nyaman di Épicerie Smart Grocery</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};
