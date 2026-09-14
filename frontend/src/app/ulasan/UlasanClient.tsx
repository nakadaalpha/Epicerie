'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import {
  Star,
  Clock,
  CheckCircle2,
  AlertCircle,
  MessageSquare,
  Sparkles,
  ShoppingBag,
  ArrowRight,
  X,
  Send,
} from 'lucide-react';
import { formatRupiah } from '@/lib/utils';
import {
  PendingReviewItem,
  ReviewItem,
  submitReviewAction,
} from '@/app/actions/review';

interface UlasanClientProps {
  initialPending: PendingReviewItem[];
  initialHistory: ReviewItem[];
  currentUser?: any;
}

export const UlasanClient: React.FC<UlasanClientProps> = ({
  initialPending,
  initialHistory,
  currentUser,
}) => {
  const router = useRouter();
  const [tab, setTab] = useState<'menunggu' | 'selesai'>('menunggu');

  // Modal State
  const [selectedProduct, setSelectedProduct] = useState<PendingReviewItem | null>(null);
  const [rating, setRating] = useState(5);
  const [comment, setComment] = useState('');
  const [submitting, setSubmitting] = useState(false);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const [successMsg, setSuccessMsg] = useState<string | null>(null);

  const openReviewModal = (item: PendingReviewItem) => {
    setSelectedProduct(item);
    setRating(5);
    setComment('');
    setErrorMsg(null);
  };

  const closeReviewModal = () => {
    setSelectedProduct(null);
    setErrorMsg(null);
  };

  const handleSubmitReview = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!selectedProduct) return;

    if (!comment.trim()) {
      setErrorMsg('Silakan tulis ulasan komentar Anda terlebih dahulu.');
      return;
    }

    setSubmitting(true);
    setErrorMsg(null);

    const res = await submitReviewAction({
      id_produk: selectedProduct.id_produk,
      rating,
      komentar: comment.trim(),
    });

    setSubmitting(false);

    if (res.success) {
      setSuccessMsg(res.message || 'Ulasan berhasil diterbitkan!');
      setTimeout(() => {
        setSuccessMsg(null);
        closeReviewModal();
        router.refresh();
      }, 1200);
    } else {
      setErrorMsg(res.error || 'Gagal mengirim ulasan.');
    }
  };

  return (
    <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full">
      {/* Header */}
      <div className="mb-6">
        <h1 className="text-2xl sm:text-3xl font-black text-gray-900 flex items-center gap-2.5">
          <Star className="w-7 h-7 text-amber-500 fill-amber-400" />
          <span>Ulasan Pembelian Saya</span>
        </h1>
        <p className="text-xs sm:text-sm text-gray-500 mt-1">
          Bagikan pengalaman berbelanja Anda untuk membantu pelanggan Épicerie lainnya.
        </p>
      </div>

      {/* Tabs */}
      <div className="flex border-b border-gray-200 mb-6 gap-2">
        <button
          onClick={() => setTab('menunggu')}
          className={`pb-3 px-4 text-sm font-bold transition-all relative flex items-center gap-2 cursor-pointer ${
            tab === 'menunggu'
              ? 'text-blue-600 border-b-2 border-blue-600'
              : 'text-gray-500 hover:text-gray-900'
          }`}
        >
          <span>Menunggu Diulas</span>
          <span
            className={`px-2 py-0.5 rounded-full text-xs font-extrabold ${
              tab === 'menunggu'
                ? 'bg-blue-100 text-blue-800'
                : 'bg-gray-100 text-gray-600'
            }`}
          >
            {initialPending.length}
          </span>
        </button>

        <button
          onClick={() => setTab('selesai')}
          className={`pb-3 px-4 text-sm font-bold transition-all relative flex items-center gap-2 cursor-pointer ${
            tab === 'selesai'
              ? 'text-blue-600 border-b-2 border-blue-600'
              : 'text-gray-500 hover:text-gray-900'
          }`}
        >
          <span>Riwayat Ulasan</span>
          <span
            className={`px-2 py-0.5 rounded-full text-xs font-extrabold ${
              tab === 'selesai'
                ? 'bg-blue-100 text-blue-800'
                : 'bg-gray-100 text-gray-600'
            }`}
          >
            {initialHistory.length}
          </span>
        </button>
      </div>

      {/* TAB 1: MENUNGGU DIULAS */}
      {tab === 'menunggu' && (
        <div>
          {initialPending.length > 0 ? (
            <div className="space-y-4">
              {initialPending.map((item) => (
                <div
                  key={`${item.id_transaksi}-${item.id_produk}`}
                  className="bg-white rounded-3xl p-5 border border-gray-200 shadow-2xs hover:border-blue-300 transition flex flex-col sm:flex-row items-center gap-5"
                >
                  <div className="w-20 h-20 rounded-2xl bg-gray-50 border border-gray-100 p-2 shrink-0 flex items-center justify-center overflow-hidden">
                    {item.gambar ? (
                      <img
                        src={item.gambar}
                        alt={item.nama_produk}
                        className="w-full h-full object-contain"
                      />
                    ) : (
                      <ShoppingBag className="w-8 h-8 text-gray-300" />
                    )}
                  </div>

                  <div className="flex-1 text-center sm:text-left w-full">
                    <span className="text-[10px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2 py-0.5 rounded">
                      {item.nama_kategori || 'Kategori'}
                    </span>
                    <h3 className="font-extrabold text-gray-900 text-base mt-1">
                      {item.nama_produk}
                    </h3>
                    <div className="flex items-center justify-center sm:justify-start gap-2 text-xs text-gray-500 mt-1">
                      <Clock className="w-3.5 h-3.5 text-gray-400" />
                      <span>
                        Dibeli pada{' '}
                        {new Date(item.tgl_beli).toLocaleDateString('id-ID', {
                          day: 'numeric',
                          month: 'long',
                          year: 'numeric',
                        })}
                      </span>
                    </div>
                  </div>

                  <button
                    onClick={() => openReviewModal(item)}
                    className="w-full sm:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-2xl transition shadow-md shadow-blue-600/20 shrink-0 cursor-pointer"
                  >
                    Tulis Ulasan
                  </button>
                </div>
              ))}
            </div>
          ) : (
            <div className="bg-white rounded-3xl p-12 text-center border border-gray-200 shadow-2xs space-y-4">
              <div className="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto">
                <CheckCircle2 className="w-8 h-8" />
              </div>
              <h3 className="text-lg font-black text-gray-900">
                Semua produk sudah Anda ulas!
              </h3>
              <p className="text-xs sm:text-sm text-gray-500 max-w-sm mx-auto">
                Terima kasih banyak telah memberikan penilaian untuk produk yang Anda beli di Épicerie.
              </p>
              <Link
                href="/"
                className="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white font-extrabold text-xs rounded-2xl hover:bg-blue-700 transition shadow-md shadow-blue-500/20"
              >
                <span>Belanja Lagi</span>
                <ArrowRight className="w-4 h-4" />
              </Link>
            </div>
          )}
        </div>
      )}

      {/* TAB 2: RIWAYAT ULASAN */}
      {tab === 'selesai' && (
        <div>
          {initialHistory.length > 0 ? (
            <div className="space-y-4">
              {initialHistory.map((rev) => (
                <div
                  key={rev.id_ulasan}
                  className="bg-white rounded-3xl p-5 border border-gray-200 shadow-2xs space-y-3"
                >
                  <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 border-b border-gray-100 pb-3">
                    <div className="flex items-center gap-3">
                      <div className="w-12 h-12 rounded-xl bg-gray-50 border border-gray-100 p-1.5 shrink-0 flex items-center justify-center overflow-hidden">
                        {rev.gambar ? (
                          <img
                            src={rev.gambar}
                            alt={rev.nama_produk}
                            className="w-full h-full object-contain"
                          />
                        ) : (
                          <ShoppingBag className="w-6 h-6 text-gray-300" />
                        )}
                      </div>
                      <div>
                        <h4 className="font-extrabold text-gray-900 text-sm">
                          {rev.nama_produk || 'Produk'}
                        </h4>
                        <div className="flex text-amber-400 mt-0.5">
                          {[1, 2, 3, 4, 5].map((s) => (
                            <Star
                              key={s}
                              className={`w-3.5 h-3.5 ${
                                s <= rev.rating ? 'fill-amber-400' : 'text-gray-200'
                              }`}
                            />
                          ))}
                        </div>
                      </div>
                    </div>
                    <span className="text-[11px] text-gray-400">
                      {new Date(rev.created_at).toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'short',
                        year: 'numeric',
                      })}
                    </span>
                  </div>

                  {/* Comment Box */}
                  <div className="bg-gray-50/80 rounded-2xl p-3.5 border border-gray-100">
                    <p className="text-xs text-gray-700 italic leading-relaxed">
                      &ldquo;{rev.komentar}&rdquo;
                    </p>
                  </div>
                </div>
              ))}
            </div>
          ) : (
            <div className="bg-white rounded-3xl p-12 text-center border border-gray-200 shadow-2xs space-y-4">
              <div className="w-16 h-16 bg-gray-100 text-gray-400 rounded-2xl flex items-center justify-center mx-auto">
                <MessageSquare className="w-8 h-8" />
              </div>
              <h3 className="text-lg font-black text-gray-900">
                Belum ada riwayat ulasan
              </h3>
              <p className="text-xs sm:text-sm text-gray-500 max-w-sm mx-auto">
                Ulasan yang telah Anda kirimkan untuk produk pesanan akan tercatat di sini.
              </p>
            </div>
          )}
        </div>
      )}

      {/* REVIEW MODAL */}
      {selectedProduct && (
        <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-xs flex items-center justify-center p-4">
          <div className="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl relative space-y-4 animate-in fade-in zoom-in-95 duration-200">
            {/* Close Button */}
            <button
              onClick={closeReviewModal}
              className="absolute top-4 right-4 p-2 text-gray-400 hover:text-gray-700 rounded-full hover:bg-gray-100 transition"
            >
              <X className="w-5 h-5" />
            </button>

            <div className="text-center pb-2 border-b border-gray-100">
              <h3 className="text-lg font-black text-gray-900">Tulis Ulasan Produk</h3>
              <p className="text-xs text-gray-500 font-medium truncate mt-0.5">
                {selectedProduct.nama_produk}
              </p>
            </div>

            {errorMsg && (
              <div className="p-3 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-center gap-2">
                <AlertCircle className="w-4 h-4 shrink-0" />
                <span>{errorMsg}</span>
              </div>
            )}

            {successMsg && (
              <div className="p-3 rounded-2xl bg-blue-100 border border-blue-300 text-blue-800 text-xs flex items-center gap-2">
                <CheckCircle2 className="w-4 h-4 shrink-0" />
                <span>{successMsg}</span>
              </div>
            )}

            <form onSubmit={handleSubmitReview} className="space-y-4">
              {/* Star Selection */}
              <div className="text-center">
                <label className="block text-xs font-semibold text-gray-600 mb-2">
                  Beri Rating Kualitas Produk:
                </label>
                <div className="flex items-center justify-center gap-2">
                  {[1, 2, 3, 4, 5].map((star) => (
                    <button
                      type="button"
                      key={star}
                      onClick={() => setRating(star)}
                      className="p-1 hover:scale-125 transition cursor-pointer"
                    >
                      <Star
                        className={`w-7 h-7 ${
                          star <= rating
                            ? 'fill-amber-400 text-amber-400'
                            : 'text-gray-300'
                        }`}
                      />
                    </button>
                  ))}
                </div>
                <span className="text-xs font-bold text-amber-600 mt-1 block">
                  {rating === 5 && '⭐ Sangat Puas (5/5)'}
                  {rating === 4 && '⭐ Puas (4/5)'}
                  {rating === 3 && '⭐ Cukup (3/5)'}
                  {rating === 2 && '⭐ Kurang Puas (2/5)'}
                  {rating === 1 && '⭐ Kecewa (1/5)'}
                </span>
              </div>

              {/* Review Text */}
              <div>
                <label className="block text-xs font-bold text-gray-700 mb-1">
                  Ulasan Pengalaman:
                </label>
                <textarea
                  rows={4}
                  required
                  value={comment}
                  onChange={(e) => setComment(e.target.value)}
                  placeholder="Ceritakan kesegaran, kemasan, atau rasa produk ini..."
                  className="w-full p-3 rounded-2xl bg-gray-50 border border-gray-200 text-xs focus:ring-2 focus:ring-blue-500 focus:bg-white focus:outline-none transition"
                />
              </div>

              <div className="flex gap-2 pt-2">
                <button
                  type="button"
                  onClick={closeReviewModal}
                  className="flex-1 py-3 px-4 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-2xl text-xs font-extrabold transition cursor-pointer"
                >
                  Batal
                </button>
                <button
                  type="submit"
                  disabled={submitting}
                  className="flex-1 py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl text-xs font-extrabold transition shadow-md shadow-blue-600/20 disabled:opacity-50 flex items-center justify-center gap-1.5 cursor-pointer"
                >
                  <Send className="w-3.5 h-3.5" />
                  <span>{submitting ? 'Mengirim...' : 'Kirim Ulasan'}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
