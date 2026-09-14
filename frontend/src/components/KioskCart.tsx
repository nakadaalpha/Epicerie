'use client';

import React from 'react';
import { useCartStore } from '@/store/useCartStore';
import { formatRupiah } from '@/lib/utils';
import { Trash2, Plus, Minus, ShoppingBag, User, ArrowRight } from 'lucide-react';

interface KioskCartProps {
  onCheckout: () => void;
}

export const KioskCart: React.FC<KioskCartProps> = ({ onCheckout }) => {
  const {
    items,
    customerName,
    setCustomerName,
    updateQuantity,
    removeItem,
    clearCart,
    getTotalPrice,
    getTotalItems,
  } = useCartStore();

  const total = getTotalPrice();
  const count = getTotalItems();

  return (
    <div className="flex flex-col h-full bg-white rounded-3xl border border-gray-200/80 shadow-sm overflow-hidden">
      {/* Cart Header */}
      <div className="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/70">
        <div className="flex items-center gap-2">
          <div className="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center">
            <ShoppingBag className="w-4 h-4" />
          </div>
          <div>
            <h2 className="font-extrabold text-sm text-gray-900 leading-tight">
              Keranjang Kasir
            </h2>
            <span className="text-[11px] text-gray-500">{count} item terpilih</span>
          </div>
        </div>

        {items.length > 0 && (
          <button
            onClick={clearCart}
            className="text-xs text-red-500 hover:text-red-700 font-semibold flex items-center gap-1 transition p-1"
          >
            <Trash2 className="w-3.5 h-3.5" />
            <span>Reset</span>
          </button>
        )}
      </div>

      {/* Customer Name / Hold Tag */}
      <div className="p-3.5 bg-blue-50/40 border-b border-gray-100">
        <div className="relative flex items-center">
          <User className="w-4 h-4 text-blue-500 absolute left-3" />
          <input
            type="text"
            value={customerName}
            onChange={(e) => setCustomerName(e.target.value)}
            placeholder="Nama / Meja / No. Antrian..."
            className="w-full pl-9 pr-3 py-2 bg-white border border-gray-200 rounded-xl text-xs font-semibold text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-600 transition"
          />
        </div>
      </div>

      {/* Item List */}
      <div className="flex-1 overflow-y-auto p-3.5 space-y-2.5 divide-y divide-gray-50">
        {items.length === 0 ? (
          <div className="h-full flex flex-col items-center justify-center text-center py-12 text-gray-400">
            <div className="w-16 h-16 rounded-full bg-gray-50 flex items-center justify-center mb-3">
              <ShoppingBag className="w-8 h-8 text-gray-300" />
            </div>
            <p className="text-xs font-bold text-gray-600">Keranjang masih kosong</p>
            <p className="text-[11px] text-gray-400 max-w-[180px] mt-0.5">
              Sentuh produk di sebelah kiri untuk menambah pesanan.
            </p>
          </div>
        ) : (
          items.map((item) => (
            <div
              key={item.produk.id_produk}
              className="pt-2.5 first:pt-0 flex items-center justify-between gap-2"
            >
              <div className="flex-1 min-w-0">
                <h4 className="font-bold text-xs text-gray-800 truncate">
                  {item.produk.nama_produk}
                </h4>
                <p className="text-[11px] font-extrabold text-blue-600">
                  {formatRupiah(item.produk.harga * item.jumlah)}
                </p>
              </div>

              {/* Quantity Stepper */}
              <div className="flex items-center gap-1.5 bg-gray-100/80 p-1 rounded-xl shrink-0">
                <button
                  onClick={() => updateQuantity(item.produk.id_produk, item.jumlah - 1)}
                  className="w-7 h-7 rounded-lg bg-white text-gray-700 flex items-center justify-center shadow-sm hover:bg-red-50 hover:text-red-600 transition active:scale-90"
                >
                  <Minus className="w-3.5 h-3.5" />
                </button>
                <span className="w-6 text-center text-xs font-bold text-gray-800">
                  {item.jumlah}
                </span>
                <button
                  onClick={() => updateQuantity(item.produk.id_produk, item.jumlah + 1)}
                  disabled={item.jumlah >= item.produk.stok}
                  className="w-7 h-7 rounded-lg bg-white text-gray-700 flex items-center justify-center shadow-sm hover:bg-blue-50 hover:text-blue-600 transition active:scale-90 disabled:opacity-30"
                >
                  <Plus className="w-3.5 h-3.5" />
                </button>
              </div>
            </div>
          ))
        )}
      </div>

      {/* Cart Footer */}
      <div className="p-4 border-t border-gray-100 bg-gray-50/80 space-y-3">
        <div className="space-y-1.5 text-xs">
          <div className="flex justify-between text-gray-500">
            <span>Subtotal ({count} item)</span>
            <span>{formatRupiah(total)}</span>
          </div>
          <div className="flex justify-between text-gray-900 font-extrabold text-base pt-1 border-t border-gray-200/60">
            <span>Total Bayar</span>
            <span className="text-blue-600 text-lg font-black">{formatRupiah(total)}</span>
          </div>
        </div>

        {/* Big Touch Checkout Button */}
        <button
          onClick={onCheckout}
          disabled={items.length === 0}
          className="w-full py-3.5 px-4 rounded-2xl bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-sm shadow-lg shadow-blue-500/25 flex items-center justify-center gap-2 transition active:scale-98 disabled:opacity-40 disabled:pointer-events-none"
        >
          <span>Proses Pembayaran</span>
          <ArrowRight className="w-4 h-4" />
        </button>
      </div>
    </div>
  );
};
