'use client';

import React from 'react';
import { formatRupiah } from '@/lib/utils';
import { Printer, CheckCircle2, X } from 'lucide-react';
import { CartItem } from '@/types';

interface ReceiptModalProps {
  isOpen: boolean;
  onClose: () => void;
  trxData: {
    kodeTransaksi: string;
    tanggal: string;
    totalBayar: number;
    metodePembayaran: string;
    namaPelanggan: string;
    uangDiterima?: number;
    kembalian?: number;
    items: CartItem[];
  } | null;
}

export const ReceiptModal: React.FC<ReceiptModalProps> = ({
  isOpen,
  onClose,
  trxData,
}) => {
  if (!isOpen || !trxData) return null;

  const handlePrint = () => {
    window.print();
  };

  return (
    <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
      <div className="bg-white rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl animate-in zoom-in-95 duration-200">
        {/* Success Header */}
        <div className="bg-emerald-600 text-white p-5 text-center relative">
          <button
            onClick={onClose}
            className="absolute top-4 right-4 text-emerald-100 hover:text-white"
          >
            <X className="w-5 h-5" />
          </button>
          <div className="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-2">
            <CheckCircle2 className="w-7 h-7" />
          </div>
          <h2 className="text-lg font-bold">Transaksi Sukses!</h2>
          <p className="text-xs text-emerald-100">{trxData.kodeTransaksi}</p>
        </div>

        {/* Receipt Paper Style */}
        <div className="p-6 bg-white print:p-0" id="printable-receipt">
          <div className="text-center pb-4 border-b border-dashed border-gray-300">
            <h3 className="font-extrabold text-gray-900 text-base tracking-tight">
              ÉPICERIE KIOSK
            </h3>
            <p className="text-[11px] text-gray-500">
              Modern Smart Grocery & Point of Sale
            </p>
            <p className="text-[10px] text-gray-400 mt-1">
              {new Date(trxData.tanggal).toLocaleString('id-ID')}
            </p>
            <p className="text-[10px] font-semibold text-gray-600 mt-0.5">
              Pelanggan: {trxData.namaPelanggan || 'Walk-in'}
            </p>
          </div>

          {/* Item List */}
          <div className="py-4 space-y-2.5 border-b border-dashed border-gray-300 max-h-48 overflow-y-auto">
            {trxData.items.map((item, idx) => (
              <div key={idx} className="text-xs flex justify-between gap-2">
                <div className="flex-1">
                  <p className="font-semibold text-gray-800 line-clamp-1">
                    {item.produk.nama_produk}
                  </p>
                  <p className="text-[11px] text-gray-500">
                    {item.jumlah} x {formatRupiah(item.produk.harga)}
                  </p>
                </div>
                <span className="font-bold text-gray-800 shrink-0">
                  {formatRupiah(item.produk.harga * item.jumlah)}
                </span>
              </div>
            ))}
          </div>

          {/* Summary Breakdown */}
          <div className="py-3 space-y-1 text-xs border-b border-dashed border-gray-300">
            <div className="flex justify-between text-gray-600">
              <span>Metode Bayar</span>
              <span className="font-semibold">{trxData.metodePembayaran}</span>
            </div>
            <div className="flex justify-between font-bold text-gray-900 text-sm pt-1">
              <span>Total</span>
              <span>{formatRupiah(trxData.totalBayar)}</span>
            </div>
            {trxData.uangDiterima !== undefined && (
              <>
                <div className="flex justify-between text-gray-600 pt-1">
                  <span>Tunai Diterima</span>
                  <span>{formatRupiah(trxData.uangDiterima)}</span>
                </div>
                <div className="flex justify-between font-bold text-emerald-600 pt-1">
                  <span>Kembalian</span>
                  <span>{formatRupiah(trxData.kembalian || 0)}</span>
                </div>
              </>
            )}
          </div>

          <div className="text-center pt-4 text-[10px] text-gray-400">
            Terima kasih telah berbelanja di Épicerie!
          </div>
        </div>

        {/* Action Buttons */}
        <div className="p-4 bg-gray-50 border-t border-gray-100 flex gap-2">
          <button
            onClick={handlePrint}
            className="flex-1 py-3 px-4 rounded-xl bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold text-xs flex items-center justify-center gap-2 transition active:scale-95"
          >
            <Printer className="w-4 h-4" />
            <span>Cetak Struk</span>
          </button>
          <button
            onClick={onClose}
            className="flex-1 py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs flex items-center justify-center gap-2 transition active:scale-95 shadow-md shadow-blue-500/20"
          >
            <span>Transaksi Baru</span>
          </button>
        </div>
      </div>
    </div>
  );
};
