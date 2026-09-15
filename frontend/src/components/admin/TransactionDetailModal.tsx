'use client';

import React, { useState } from 'react';
import { formatRupiah } from '@/lib/utils';
import {
  Receipt,
  X,
  Calendar,
  MapPin,
  Package,
  Send,
  Loader2,
  CheckCircle2,
} from 'lucide-react';
import { updateOrderStatusAction } from '@/app/actions/shop';

interface TransactionDetailModalProps {
  isOpen: boolean;
  onClose: () => void;
  transaction: {
    id_transaksi: number;
    kode_transaksi: string;
    total_bayar: number;
    status: string;
    created_at: string;
    metode_pembayaran?: string;
    nama_pelanggan_hold?: string;
    ongkos_kirim: number;
    nama_pembeli?: string;
    no_hp_pembeli?: string;
    penerima?: string;
    no_hp_penerima?: string;
    detail_alamat?: string;
    items: {
      id_detail_transaksi: number;
      id_produk: number;
      nama_produk: string;
      gambar?: string;
      jumlah: number;
      harga_produk_saat_beli: number;
    }[];
  } | null;
  onStatusUpdated?: () => void;
}

export const TransactionDetailModal: React.FC<TransactionDetailModalProps> = ({
  isOpen,
  onClose,
  transaction,
  onStatusUpdated,
}) => {
  const [isSubmitting, setIsSubmitting] = useState(false);

  if (!isOpen || !transaction) return null;

  const statusNormalized = (transaction.status || '').toLowerCase();
  const canSendOrder = statusNormalized === 'diproses' || statusNormalized === 'dikemas';

  const getStatusBadge = () => {
    if (statusNormalized === 'selesai') {
      return 'bg-green-100 text-green-700 border-green-200';
    } else if (statusNormalized === 'dikirim') {
      return 'bg-blue-100 text-blue-700 border-blue-200';
    } else {
      return 'bg-yellow-100 text-yellow-700 border-yellow-200';
    }
  };

  const handleSendOrder = async () => {
    if (!transaction) return;
    setIsSubmitting(true);
    try {
      const res = await updateOrderStatusAction(transaction.id_transaksi, 'dikirim');
      if (res.success) {
        if (onStatusUpdated) onStatusUpdated();
        onClose();
      } else {
        alert(res.error || 'Gagal mengubah status pesanan');
      }
    } catch (err: any) {
      alert(err.message || 'Terjadi kesalahan sistem');
    } finally {
      setIsSubmitting(false);
    }
  };

  const recipientName = transaction.penerima || transaction.nama_pembeli || transaction.nama_pelanggan_hold || 'Walk-in Customer';
  const recipientPhone = transaction.no_hp_penerima || transaction.no_hp_pembeli || '-';
  const addressText = transaction.detail_alamat || 'Pesanan Langsung di Toko (Walk-in / Kiosk)';

  return (
    <div className="fixed inset-0 z-[100] overflow-y-auto animate-in fade-in duration-200">
      {/* Backdrop */}
      <div
        className="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
        onClick={onClose}
      />

      <div className="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div className="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg z-10">
          {/* HEADER MODAL */}
          <div className="bg-gradient-to-r from-blue-600 to-teal-500 px-6 py-4 flex justify-between items-center">
            <h3 className="text-lg font-bold leading-6 text-white flex items-center gap-2">
              <Receipt className="w-5 h-5" />
              <span>Detail Pesanan</span>
            </h3>
            <button
              type="button"
              onClick={onClose}
              className="text-white/80 hover:text-white transition focus:outline-none p-1 rounded-lg hover:bg-white/10 cursor-pointer"
            >
              <X className="w-5 h-5" />
            </button>
          </div>

          {/* BODY */}
          <div className="px-6 py-5 max-h-[70vh] overflow-y-auto scrollbar-thin">
            {/* Top Code & Status */}
            <div className="flex justify-between items-start mb-6 pb-4 border-b border-gray-100">
              <div>
                <p className="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">
                  Kode Transaksi
                </p>
                <p className="text-lg font-black text-gray-800 tracking-tight">
                  {transaction.kode_transaksi}
                </p>
                <p className="text-xs text-gray-500 mt-1 flex items-center gap-1.5 font-semibold">
                  <Calendar className="w-3.5 h-3.5 text-gray-400" />
                  <span>
                    {new Date(transaction.created_at).toLocaleDateString('id-ID', {
                      day: '2-digit',
                      month: 'long',
                      year: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit',
                    })}
                  </span>
                </p>
              </div>
              <div className="text-right">
                <span
                  className={`px-3 py-1 rounded-full text-xs font-bold border capitalize ${getStatusBadge()}`}
                >
                  {transaction.status}
                </span>
              </div>
            </div>

            {/* Info Pengiriman */}
            <div className="bg-gray-50 rounded-xl p-4 mb-6 border border-gray-100">
              <h4 className="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                <MapPin className="w-4 h-4 text-red-500" />
                <span>Info Pengiriman</span>
              </h4>
              <div className="space-y-2 text-sm">
                <div className="flex items-baseline">
                  <span className="text-gray-400 w-24 text-xs font-bold uppercase shrink-0">
                    Penerima
                  </span>
                  <span className="font-bold text-gray-800">{recipientName}</span>
                </div>
                <div className="flex items-baseline">
                  <span className="text-gray-400 w-24 text-xs font-bold uppercase shrink-0">
                    Telepon
                  </span>
                  <span className="text-gray-600 font-semibold">{recipientPhone}</span>
                </div>
                <div className="flex items-baseline">
                  <span className="text-gray-400 w-24 text-xs font-bold uppercase shrink-0">
                    Alamat
                  </span>
                  <span className="text-gray-600 leading-relaxed font-medium">{addressText}</span>
                </div>
              </div>
            </div>

            {/* Rincian Barang */}
            <h4 className="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
              <Package className="w-4 h-4 text-teal-500" />
              <span>Rincian Barang</span>
            </h4>
            <div className="space-y-2.5 mb-6">
              {transaction.items && transaction.items.length > 0 ? (
                transaction.items.map((item) => (
                  <div
                    key={item.id_detail_transaksi || item.id_produk}
                    className="flex items-center gap-3 p-2.5 hover:bg-gray-50 rounded-xl transition border border-transparent hover:border-gray-100"
                  >
                    <div className="w-12 h-12 bg-white rounded-lg border border-gray-200 flex items-center justify-center overflow-hidden shrink-0 p-1">
                      {item.gambar ? (
                        <img
                          src={item.gambar}
                          alt={item.nama_produk}
                          className="w-full h-full object-contain"
                        />
                      ) : (
                        <Package className="w-5 h-5 text-gray-300" />
                      )}
                    </div>
                    <div className="flex-1 min-w-0">
                      <p className="font-bold text-gray-800 text-sm line-clamp-1 leading-tight">
                        {item.nama_produk}
                      </p>
                      <p className="text-xs text-gray-500 mt-1 font-semibold">
                        {item.jumlah} x {formatRupiah(item.harga_produk_saat_beli)}
                      </p>
                    </div>
                    <div className="font-black text-gray-800 text-sm shrink-0">
                      {formatRupiah(item.jumlah * item.harga_produk_saat_beli)}
                    </div>
                  </div>
                ))
              ) : (
                <div className="text-center py-4 text-gray-400 text-xs">
                  Tidak ada item produk terlampir.
                </div>
              )}
            </div>
          </div>

          {/* FOOTER MODAL */}
          <div className="bg-gray-50 px-6 py-4 border-t border-gray-200 space-y-4">
            <div className="flex justify-between items-center">
              <div className="text-xs text-gray-500 font-bold">
                Ongkir:{' '}
                <span className="text-gray-800 font-extrabold">
                  {formatRupiah(transaction.ongkos_kirim || 0)}
                </span>
              </div>
              <div className="text-right">
                <p className="text-xs text-gray-400 font-bold uppercase tracking-wider mb-0.5">
                  Total Bayar
                </p>
                <p className="text-xl font-black text-blue-600">
                  {formatRupiah(transaction.total_bayar)}
                </p>
              </div>
            </div>

            {/* Tombol Kirim Pesanan (Jika Diproses / Dikemas) */}
            {canSendOrder && (
              <button
                type="button"
                onClick={handleSendOrder}
                disabled={isSubmitting}
                className="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-blue-200 transition flex justify-center items-center gap-2 cursor-pointer disabled:opacity-50"
              >
                {isSubmitting ? (
                  <>
                    <Loader2 className="w-5 h-5 animate-spin" />
                    <span>Memperbarui Status...</span>
                  </>
                ) : (
                  <>
                    <Send className="w-4 h-4" />
                    <span>Kirim Pesanan Sekarang</span>
                  </>
                )}
              </button>
            )}
          </div>
        </div>
      </div>
    </div>
  );
};
