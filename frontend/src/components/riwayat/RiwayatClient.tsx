'use client';

import React, { useState, useTransition } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import {
  ShoppingBag,
  Truck,
  Store,
  Clock,
  CheckCircle,
  Package,
  MapPin,
  Star,
  ChevronRight,
  ArrowLeft,
  Loader2,
} from 'lucide-react';
import { formatRupiah } from '@/lib/utils';
import { Transaksi } from '@/types';
import { completeMyOrderAction } from '@/app/actions/shop';

interface RiwayatClientProps {
  initialOrders: Transaksi[];
  currentStatus: string;
}

const STATUS_TABS = [
  { id: 'semua', label: 'Semua' },
  { id: 'diproses', label: 'Diproses' },
  { id: 'dikemas', label: 'Dikemas' },
  { id: 'dikirim', label: 'Dikirim' },
  { id: 'selesai', label: 'Selesai' },
];

export function RiwayatClient({ initialOrders, currentStatus }: RiwayatClientProps) {
  const router = useRouter();
  const [activeStatus, setActiveStatus] = useState<string>(currentStatus || 'semua');
  const [completingId, setCompletingId] = useState<number | null>(null);
  const [isPending, startTransition] = useTransition();

  const handleTabChange = (status: string) => {
    setActiveStatus(status);
    startTransition(() => {
      if (status === 'semua') {
        router.push('/riwayat');
      } else {
        router.push(`/riwayat?status=${status}`);
      }
    });
  };

  const handleConfirmReceived = async (id: number) => {
    if (!confirm('Apakah Anda yakin telah menerima paket pesanan ini dengan baik?')) return;
    setCompletingId(id);
    const res = await completeMyOrderAction(id);
    setCompletingId(null);
    if (res.success) {
      startTransition(() => {
        router.refresh();
      });
    } else {
      alert(res.error || 'Gagal mengonfirmasi pesanan.');
    }
  };

  const getStatusBadge = (status: string) => {
    const s = (status || 'pending').toLowerCase();
    switch (s) {
      case 'selesai':
        return (
          <span className="px-3 py-1 rounded-full text-xs font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-200">
            Selesai
          </span>
        );
      case 'dikirim':
        return (
          <span className="px-3 py-1 rounded-full text-xs font-black uppercase bg-blue-100 text-blue-800 border border-blue-200 flex items-center gap-1">
            <Truck className="w-3.5 h-3.5" /> Sedang Dikirim
          </span>
        );
      case 'dikemas':
        return (
          <span className="px-3 py-1 rounded-full text-xs font-black uppercase bg-amber-100 text-amber-800 border border-amber-200">
            Dikemas
          </span>
        );
      case 'diproses':
      case 'pending':
        return (
          <span className="px-3 py-1 rounded-full text-xs font-black uppercase bg-yellow-100 text-yellow-800 border border-yellow-200">
            Diproses
          </span>
        );
      case 'batal':
        return (
          <span className="px-3 py-1 rounded-full text-xs font-black uppercase bg-red-100 text-red-800 border border-red-200">
            Dibatalkan
          </span>
        );
      default:
        return (
          <span className="px-3 py-1 rounded-full text-xs font-black uppercase bg-gray-100 text-gray-700">
            {status}
          </span>
        );
    }
  };

  return (
    <div className="min-h-screen bg-slate-50 py-6 md:py-10 px-4">
      <div className="max-w-4xl mx-auto space-y-6">
        {/* Header Navigation */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <Link
              href="/"
              className="w-10 h-10 rounded-xl bg-white border border-gray-200 text-gray-600 flex items-center justify-center hover:bg-gray-50 transition shadow-2xs"
            >
              <ArrowLeft className="w-5 h-5" />
            </Link>
            <div>
              <h1 className="text-2xl md:text-3xl font-black text-gray-900 flex items-center gap-2.5">
                <ShoppingBag className="w-7 h-7 text-blue-600" /> Riwayat Pesanan Saya
              </h1>
              <p className="text-xs md:text-sm text-gray-500 mt-0.5">
                Pantau proses pengiriman dan rincian belanja Anda di Épicerie.
              </p>
            </div>
          </div>
        </div>

        {/* Status Filter Tabs */}
        <div className="flex gap-2 border-b border-gray-200 overflow-x-auto pb-2 scrollbar-none">
          {STATUS_TABS.map((tab) => {
            const isActive = activeStatus === tab.id;
            return (
              <button
                key={tab.id}
                type="button"
                onClick={() => handleTabChange(tab.id)}
                className={`px-5 py-2.5 rounded-xl text-xs md:text-sm font-bold whitespace-nowrap transition cursor-pointer ${
                  isActive
                    ? 'bg-blue-600 text-white shadow-md shadow-blue-500/20'
                    : 'bg-white text-gray-600 hover:bg-gray-100 border border-gray-200'
                }`}
              >
                {tab.label}
              </button>
            );
          })}
        </div>

        {/* Orders List */}
        {initialOrders.length === 0 ? (
          <div className="bg-white rounded-3xl p-12 text-center shadow-sm border border-gray-100 space-y-4">
            <div className="w-20 h-20 bg-blue-50 rounded-full flex items-center justify-center mx-auto text-blue-500">
              <ShoppingBag className="w-10 h-10" />
            </div>
            <div>
              <h3 className="text-lg font-bold text-gray-800">Belum ada pesanan</h3>
              <p className="text-xs text-gray-400 mt-1 max-w-sm mx-auto">
                Anda belum memiliki pesanan dengan filter status ini. Yuk mulai belanja kebutuhan
                harian Anda!
              </p>
            </div>
            <Link
              href="/"
              className="inline-block bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-sm py-3 px-8 rounded-2xl shadow-lg shadow-blue-500/25 transition cursor-pointer"
            >
              Mulai Belanja Sekarang
            </Link>
          </div>
        ) : (
          <div className="space-y-4">
            {initialOrders.map((trx) => {
              const isDelivery = !!trx.id_alamat || (trx.ongkos_kirim || 0) > 0;
              const isDelivering = (trx.status || '').toLowerCase() === 'dikirim';
              const isCompleted = (trx.status || '').toLowerCase() === 'selesai';

              return (
                <div
                  key={trx.id_transaksi}
                  className="bg-white rounded-3xl shadow-sm border border-gray-200/80 overflow-hidden hover:shadow-md transition"
                >
                  {/* Card Header */}
                  <div className="bg-slate-50/70 px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div className="flex items-center gap-3">
                      <div className="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-black text-xs">
                        {isDelivery ? <Truck className="w-5 h-5" /> : <Store className="w-5 h-5" />}
                      </div>
                      <div>
                        <span className="font-extrabold text-gray-900 text-sm block">
                          {trx.kode_transaksi}
                        </span>
                        <span className="text-xs text-gray-400 flex items-center gap-1">
                          <Clock className="w-3.5 h-3.5" />
                          {trx.created_at
                            ? new Date(trx.created_at).toLocaleDateString('id-ID', {
                                day: 'numeric',
                                month: 'short',
                                year: 'numeric',
                                hour: '2-digit',
                                minute: '2-digit',
                              })
                            : '-'}
                        </span>
                      </div>
                    </div>
                    <div>{getStatusBadge(trx.status)}</div>
                  </div>

                  {/* Item Details */}
                  <div className="p-6 space-y-3">
                    {trx.items && trx.items.length > 0 ? (
                      trx.items.map((item, idx) => (
                        <div key={idx} className="flex items-center gap-4 py-1">
                          <div className="w-14 h-14 bg-slate-50 rounded-xl border border-gray-100 p-1 shrink-0 flex items-center justify-center overflow-hidden">
                            {item.gambar ? (
                              <img
                                src={item.gambar}
                                alt={item.nama_produk}
                                className="w-full h-full object-contain"
                              />
                            ) : (
                              <Package className="w-6 h-6 text-gray-300" />
                            )}
                          </div>
                          <div className="flex-1 min-w-0">
                            <h4 className="font-bold text-sm text-gray-900 truncate">
                              {item.nama_produk || 'Produk Épicerie'}
                            </h4>
                            <p className="text-xs text-gray-500 mt-0.5">
                              {item.jumlah} × {formatRupiah(item.harga_produk_saat_beli || 0)}
                            </p>
                          </div>
                          <div className="text-right font-extrabold text-sm text-gray-900">
                            {formatRupiah((item.harga_produk_saat_beli || 0) * (item.jumlah || 1))}
                          </div>
                        </div>
                      ))
                    ) : (
                      <p className="text-xs text-gray-400 italic">Rincian produk tidak tersedia.</p>
                    )}
                  </div>

                  {/* Card Footer */}
                  <div className="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                      <span className="text-[10px] text-gray-400 uppercase font-black tracking-wider block">
                        Total Pembayaran
                      </span>
                      <p className="text-lg font-black text-blue-600">
                        {formatRupiah(trx.total_bayar)}
                      </p>
                    </div>

                    <div className="flex items-center gap-2">
                      {/* Tracking button if delivery */}
                      {isDelivery && (
                        <Link
                          href={`/tracking/${trx.id_transaksi}`}
                          className="px-4 py-2.5 rounded-xl border border-blue-600 bg-blue-50 text-blue-700 text-xs font-extrabold hover:bg-blue-100 transition flex items-center gap-1.5 shadow-2xs"
                        >
                          <MapPin className="w-4 h-4 text-blue-600" /> Lacak Pengiriman
                        </Link>
                      )}

                      {/* Confirm Received Button when in delivery */}
                      {isDelivering && (
                        <button
                          type="button"
                          onClick={() => handleConfirmReceived(trx.id_transaksi)}
                          disabled={completingId === trx.id_transaksi}
                          className="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold transition flex items-center gap-1.5 shadow-sm shadow-emerald-500/20 cursor-pointer disabled:opacity-50"
                        >
                          {completingId === trx.id_transaksi ? (
                            <Loader2 className="w-4 h-4 animate-spin" />
                          ) : (
                            <CheckCircle className="w-4 h-4" />
                          )}
                          <span>Pesanan Diterima</span>
                        </button>
                      )}

                      {/* Review Link when completed */}
                      {isCompleted && (
                        <Link
                          href="/ulasan"
                          className="px-4 py-2.5 rounded-xl border border-amber-300 bg-amber-50 text-amber-800 text-xs font-extrabold hover:bg-amber-100 transition flex items-center gap-1.5"
                        >
                          <Star className="w-4 h-4 text-amber-500" /> Beri Ulasan
                        </Link>
                      )}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>
    </div>
  );
}
