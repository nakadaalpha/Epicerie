'use client';

import React, { useState } from 'react';
import { useCartStore } from '@/store/useCartStore';
import { formatRupiah } from '@/lib/utils';
import { Banknote, QrCode, CreditCard, X, Loader2 } from 'lucide-react';
import { createTransaction } from '@/app/actions/shop';

interface CheckoutModalProps {
  isOpen: boolean;
  onClose: () => void;
  onSuccess: (trxResult: any) => void;
}

export const CheckoutModal: React.FC<CheckoutModalProps> = ({
  isOpen,
  onClose,
  onSuccess,
}) => {
  const { items, customerName, getTotalPrice, clearCart } = useCartStore();
  const total = getTotalPrice();

  const [paymentMethod, setPaymentMethod] = useState<'Tunai' | 'QRIS' | 'Transfer'>('Tunai');
  const [cashGiven, setCashGiven] = useState<number>(total);
  const [loading, setLoading] = useState<boolean>(false);
  const [errorMsg, setErrorMsg] = useState<string>('');

  if (!isOpen) return null;

  const kembalian = Math.max(0, cashGiven - total);
  const quickAmounts = [total, 10000, 20000, 50000, 100000].filter(
    (v, i, a) => a.indexOf(v) === i && v >= total
  );

  const handleCheckout = async () => {
    if (paymentMethod === 'Tunai' && cashGiven < total) {
      setErrorMsg('Uang yang diberikan kurang dari total belanja.');
      return;
    }

    setLoading(true);
    setErrorMsg('');

    try {
      const payload = {
        items: items.map((i) => ({
          id_produk: i.produk.id_produk,
          jumlah: i.jumlah,
          harga: i.produk.harga,
        })),
        total_bayar: total,
        metode_pembayaran: paymentMethod,
        nama_pelanggan_hold: customerName || 'Pelanggan Walk-in',
      };

      const res = await createTransaction(payload);

      if (!res.success) {
        setErrorMsg(res.error || 'Terjadi kesalahan saat memproses pesanan.');
        setLoading(false);
        return;
      }

      // Pass result to parent for receipt
      onSuccess({
        ...res,
        items: [...items],
        metodePembayaran: paymentMethod,
        namaPelanggan: customerName || 'Walk-in',
        uangDiterima: paymentMethod === 'Tunai' ? cashGiven : undefined,
        kembalian: paymentMethod === 'Tunai' ? kembalian : undefined,
      });

      clearCart();
      onClose();
    } catch (err: any) {
      setErrorMsg(err.message || 'Gagal terhubung ke server.');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
      <div className="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl animate-in zoom-in-95 duration-200">
        {/* Header */}
        <div className="p-5 border-b border-gray-100 flex items-center justify-between">
          <div>
            <h2 className="text-lg font-extrabold text-gray-900">Pembayaran Kasir</h2>
            <p className="text-xs text-gray-500">Pilih metode pembayaran yang digunakan</p>
          </div>
          <button
            onClick={onClose}
            className="p-2 rounded-xl text-gray-400 hover:bg-gray-100 transition"
          >
            <X className="w-5 h-5" />
          </button>
        </div>

        <div className="p-6 space-y-5">
          {/* Total Box */}
          <div className="bg-blue-50/70 border border-blue-100 rounded-2xl p-4 text-center">
            <span className="text-xs font-semibold text-blue-600 block">
              Total Tagihan
            </span>
            <span className="text-3xl font-black text-blue-900 tracking-tight block mt-1">
              {formatRupiah(total)}
            </span>
            <span className="text-xs text-blue-700/80 mt-1 block">
              {items.length} item barang
            </span>
          </div>

          {/* Payment Method Selector */}
          <div>
            <label className="text-xs font-bold text-gray-700 block mb-2">
              Metode Pembayaran
            </label>
            <div className="grid grid-cols-3 gap-2">
              {[
                { id: 'Tunai', label: 'Tunai', icon: Banknote },
                { id: 'QRIS', label: 'QRIS', icon: QrCode },
                { id: 'Transfer', label: 'Transfer', icon: CreditCard },
              ].map((m) => {
                const Icon = m.icon;
                const isSelected = paymentMethod === m.id;
                return (
                  <button
                    key={m.id}
                    type="button"
                    onClick={() => {
                      setPaymentMethod(m.id as any);
                      setErrorMsg('');
                    }}
                    className={`p-3 rounded-2xl border flex flex-col items-center gap-1.5 transition text-xs font-bold ${
                      isSelected
                        ? 'border-blue-600 bg-blue-600 text-white shadow-md shadow-blue-500/20'
                        : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50'
                    }`}
                  >
                    <Icon className="w-5 h-5" />
                    <span>{m.label}</span>
                  </button>
                );
              })}
            </div>
          </div>

          {/* Cash Input & Quick Amounts */}
          {paymentMethod === 'Tunai' && (
            <div className="space-y-3 pt-2">
              <div>
                <label className="text-xs font-bold text-gray-700 block mb-1">
                  Uang Diterima (Rp)
                </label>
                <input
                  type="number"
                  value={cashGiven || ''}
                  onChange={(e) => setCashGiven(Number(e.target.value) || 0)}
                  className="w-full text-xl font-black px-4 py-3 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-600 text-gray-900"
                  placeholder="0"
                />
              </div>

              {/* Quick Cash Buttons */}
              <div className="flex flex-wrap gap-1.5">
                {quickAmounts.map((amt) => (
                  <button
                    key={amt}
                    type="button"
                    onClick={() => setCashGiven(amt)}
                    className="px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-bold transition active:scale-95"
                  >
                    {amt === total ? 'Uang Pas' : formatRupiah(amt)}
                  </button>
                ))}
              </div>

              {/* Kembalian Box */}
              <div className="flex items-center justify-between p-3.5 bg-emerald-50 rounded-2xl border border-emerald-100">
                <span className="text-xs font-bold text-emerald-800">Kembalian:</span>
                <span className="text-lg font-black text-emerald-700">
                  {formatRupiah(kembalian)}
                </span>
              </div>
            </div>
          )}

          {/* QRIS Display */}
          {paymentMethod === 'QRIS' && (
            <div className="p-4 bg-gray-50 border border-gray-200 rounded-2xl text-center space-y-2">
              <div className="w-40 h-40 mx-auto bg-white p-2 rounded-xl border shadow-sm flex items-center justify-center">
                <QrCode className="w-32 h-32 text-gray-800" />
              </div>
              <p className="text-xs font-semibold text-gray-700">
                Scan kode QRIS di atas dengan m-Banking atau e-Wallet
              </p>
              <p className="text-[11px] text-gray-400">
                NMID: ID1029384756 (Épicerie Kiosk)
              </p>
            </div>
          )}

          {errorMsg && (
            <div className="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl font-medium">
              {errorMsg}
            </div>
          )}

          {/* Submit Button */}
          <button
            onClick={handleCheckout}
            disabled={loading || total <= 0}
            className="w-full py-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-base shadow-lg shadow-blue-500/25 flex items-center justify-center gap-2 transition active:scale-98 disabled:opacity-50 disabled:pointer-events-none"
          >
            {loading ? (
              <>
                <Loader2 className="w-5 h-5 animate-spin" />
                <span>Memproses Pembayaran...</span>
              </>
            ) : (
              <span>Konfirmasi & Selesaikan Transaksi</span>
            )}
          </button>
        </div>
      </div>
    </div>
  );
};
