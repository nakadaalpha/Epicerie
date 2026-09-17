'use client';

import React, { useState, useEffect } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import {
  ArrowLeft,
  Truck,
  MapPin,
  Clock,
  Package,
  CheckCircle,
  Navigation,
  ExternalLink,
  Loader2,
  Phone,
  ShieldCheck,
} from 'lucide-react';
import { formatRupiah } from '@/lib/utils';
import { Transaksi } from '@/types';
import { completeMyOrderAction, getOrderByIdAction } from '@/app/actions/shop';

interface TrackingClientProps {
  initialOrder: Transaksi;
}

export function TrackingClient({ initialOrder }: TrackingClientProps) {
  const router = useRouter();
  const [order, setOrder] = useState<Transaksi>(initialOrder);
  const [isCompleting, setIsCompleting] = useState(false);
  const [lastUpdated, setLastUpdated] = useState<Date>(new Date());

  const status = (order.status || 'pending').toLowerCase();
  const isDelivering = status === 'dikirim';
  const isCompleted = status === 'selesai';

  const courierLat = order.kurir_lat || -7.7332602;
  const courierLong = order.kurir_long || 110.3312137;

  // Poll for location updates every 10 seconds if order is actively delivering
  useEffect(() => {
    if (!isDelivering) return;

    const interval = setInterval(async () => {
      const updated = await getOrderByIdAction(order.id_transaksi);
      if (updated) {
        setOrder(updated);
        setLastUpdated(new Date());
      }
    }, 10000);

    return () => clearInterval(interval);
  }, [isDelivering, order.id_transaksi]);

  const handleComplete = async () => {
    if (!confirm('Konfirmasi bahwa paket pesanan telah sampai dan Anda terima?')) return;
    setIsCompleting(true);
    const res = await completeMyOrderAction(order.id_transaksi);
    setIsCompleting(false);
    if (res.success) {
      const updated = await getOrderByIdAction(order.id_transaksi);
      if (updated) setOrder(updated);
    } else {
      alert(res.error || 'Gagal menyelesaikan pesanan.');
    }
  };

  const steps = [
    { key: 'diproses', label: 'Pesanan Diterima', desc: 'Pesanan sedang diproses kasir' },
    { key: 'dikemas', label: 'Sedang Dikemas', desc: 'Barang sedang disiapkan' },
    { key: 'dikirim', label: 'Dalam Pengantaran', desc: 'Kurir sedang dalam perjalanan' },
    { key: 'selesai', label: 'Pesanan Selesai', desc: 'Paket telah sampai di tujuan' },
  ];

  const getStepIndex = (st: string) => {
    switch (st) {
      case 'selesai':
        return 3;
      case 'dikirim':
        return 2;
      case 'dikemas':
        return 1;
      case 'diproses':
      case 'pending':
      default:
        return 0;
    }
  };

  const currentStepIdx = getStepIndex(status);

  return (
    <div className="min-h-screen bg-slate-50 py-6 md:py-10 px-4">
      <div className="max-w-4xl mx-auto space-y-6">
        {/* Header */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-3">
            <Link
              href="/riwayat"
              className="w-10 h-10 rounded-xl bg-white border border-gray-200 text-gray-600 flex items-center justify-center hover:bg-gray-50 transition shadow-2xs"
            >
              <ArrowLeft className="w-5 h-5" />
            </Link>
            <div>
              <h1 className="text-2xl md:text-3xl font-black text-gray-900 flex items-center gap-2.5">
                <Truck className="w-7 h-7 text-blue-600" /> Pelacakan Pengantaran
              </h1>
              <p className="text-xs md:text-sm text-gray-500 mt-0.5">
                Kode Transaksi: <strong className="text-gray-800">{order.kode_transaksi}</strong>
              </p>
            </div>
          </div>

          <div className="flex items-center gap-2">
            <span
              className={`px-3.5 py-1.5 rounded-full text-xs font-black uppercase tracking-wider flex items-center gap-1.5 ${
                isCompleted
                  ? 'bg-emerald-100 text-emerald-800 border border-emerald-200'
                  : isDelivering
                  ? 'bg-blue-100 text-blue-800 border border-blue-200 animate-pulse'
                  : 'bg-amber-100 text-amber-800 border border-amber-200'
              }`}
            >
              <span className="w-2 h-2 rounded-full bg-current"></span>
              {status}
            </span>
          </div>
        </div>

        {/* Progress Stepper */}
        <div className="bg-white rounded-3xl p-6 md:p-8 shadow-sm border border-gray-200/80">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
            {steps.map((step, idx) => {
              const isPast = idx < currentStepIdx;
              const isCurrent = idx === currentStepIdx;
              return (
                <div key={step.key} className="space-y-2 text-center md:text-left">
                  <div className="flex items-center gap-2">
                    <div
                      className={`w-8 h-8 rounded-xl flex items-center justify-center text-xs font-black shrink-0 ${
                        isPast
                          ? 'bg-emerald-600 text-white'
                          : isCurrent
                          ? 'bg-blue-600 text-white ring-4 ring-blue-100'
                          : 'bg-gray-100 text-gray-400'
                      }`}
                    >
                      {isPast ? <CheckCircle className="w-4 h-4" /> : idx + 1}
                    </div>
                    <div
                      className={`hidden md:block h-1 flex-1 rounded-full ${
                        isPast ? 'bg-emerald-500' : 'bg-gray-100'
                      }`}
                    />
                  </div>
                  <div>
                    <h4
                      className={`text-xs md:text-sm font-extrabold ${
                        isCurrent
                          ? 'text-blue-600'
                          : isPast
                          ? 'text-gray-900'
                          : 'text-gray-400'
                      }`}
                    >
                      {step.label}
                    </h4>
                    <p className="text-[11px] text-gray-400 mt-0.5 leading-snug">{step.desc}</p>
                  </div>
                </div>
              );
            })}
          </div>
        </div>

        {/* Live GPS Map & Courier Card */}
        <div className="bg-white rounded-3xl shadow-sm border border-gray-200/80 overflow-hidden">
          <div className="p-5 border-b border-gray-100 flex items-center justify-between bg-slate-50/60">
            <div className="flex items-center gap-2.5">
              <div className="w-9 h-9 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                <Navigation className="w-5 h-5" />
              </div>
              <div>
                <h3 className="text-sm font-extrabold text-gray-900">Posisi Kurir Real-Time</h3>
                <p className="text-[11px] text-gray-500">
                  Update terakhir: {lastUpdated.toLocaleTimeString('id-ID')}
                </p>
              </div>
            </div>
            <a
              href={`https://www.google.com/maps/search/?api=1&query=${courierLat},${courierLong}`}
              target="_blank"
              rel="noopener noreferrer"
              className="px-3.5 py-1.5 rounded-xl bg-white border border-gray-200 text-xs font-bold text-gray-700 hover:bg-gray-50 transition flex items-center gap-1.5 shadow-2xs"
            >
              <ExternalLink className="w-3.5 h-3.5" /> Buka Google Maps
            </a>
          </div>

          {/* Embedded Map / OpenStreetMap Viewer */}
          <div className="relative h-[360px] md:h-[420px] w-full bg-slate-100">
            <iframe
              title="Courier Live Location"
              src={`https://www.openstreetmap.org/export/embed.html?bbox=${courierLong - 0.01}%2C${courierLat - 0.01}%2C${courierLong + 0.01}%2C${courierLat + 0.01}&layer=mapnik&marker=${courierLat}%2C${courierLong}`}
              className="w-full h-full border-none"
            />
            <div className="absolute bottom-4 left-4 bg-white/95 backdrop-blur-xs p-3.5 rounded-2xl shadow-lg border border-gray-200/80 max-w-xs flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                <Truck className="w-5 h-5" />
              </div>
              <div className="min-w-0">
                <p className="text-xs font-extrabold text-gray-900">Armada Kurir Épicerie</p>
                <p className="text-[10px] text-gray-500 truncate">
                  Lat: {courierLat.toFixed(5)}, Long: {courierLong.toFixed(5)}
                </p>
              </div>
            </div>
          </div>
        </div>

        {/* Package & Address Information */}
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          {/* Alamat Penerima */}
          <div className="bg-white rounded-3xl p-6 shadow-sm border border-gray-200/80 space-y-4">
            <div className="flex items-center gap-2 text-sm font-extrabold text-gray-900">
              <MapPin className="w-5 h-5 text-blue-600" /> Alamat Pengiriman
            </div>
            <div className="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
              <div className="flex items-center justify-between">
                <span className="font-extrabold text-sm text-gray-900">
                  {order.nama_pembeli || order.nama_pelanggan_hold || 'Pelanggan'}
                </span>
                {order.no_hp_pembeli && (
                  <span className="text-xs text-blue-600 font-bold flex items-center gap-1">
                    <Phone className="w-3.5 h-3.5" /> {order.no_hp_pembeli}
                  </span>
                )}
              </div>
              <p className="text-xs text-gray-600 leading-relaxed">
                Tujuan pengantaran pesanan Anda telah divalidasi oleh sistem kurir Épicerie.
              </p>
            </div>
          </div>

          {/* Ringkasan Belanja */}
          <div className="bg-white rounded-3xl p-6 shadow-sm border border-gray-200/80 space-y-4">
            <div className="flex items-center gap-2 text-sm font-extrabold text-gray-900">
              <Package className="w-5 h-5 text-blue-600" /> Rincian Paket ({order.items?.length || 0} Barang)
            </div>
            <div className="space-y-2.5 max-h-[160px] overflow-y-auto pr-1 scrollbar-thin">
              {order.items?.map((item, idx) => (
                <div key={idx} className="flex justify-between items-center text-xs py-1 border-b border-gray-50 last:border-0">
                  <span className="text-gray-800 font-medium truncate max-w-[200px]">
                    {item.nama_produk} × {item.jumlah}
                  </span>
                  <span className="font-extrabold text-gray-900">
                    {formatRupiah((item.harga_produk_saat_beli || 0) * (item.jumlah || 1))}
                  </span>
                </div>
              ))}
            </div>
            <div className="pt-2 border-t border-gray-100 flex justify-between items-center">
              <span className="text-xs font-bold text-gray-500">Total Tagihan</span>
              <span className="text-base font-black text-blue-600">
                {formatRupiah(order.total_bayar)}
              </span>
            </div>
          </div>
        </div>

        {/* Action: Selesai Konfirmasi */}
        {isDelivering && (
          <div className="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-3xl p-6 text-white flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xl shadow-emerald-500/20">
            <div className="space-y-1 text-center sm:text-left">
              <h3 className="font-black text-lg flex items-center justify-center sm:justify-start gap-2">
                <ShieldCheck className="w-5 h-5" /> Paket Sudah Sampai?
              </h3>
              <p className="text-emerald-100 text-xs">
                Tekan konfirmasi jika Anda telah menerima seluruh pesanan dengan kondisi baik.
              </p>
            </div>
            <button
              type="button"
              onClick={handleComplete}
              disabled={isCompleting}
              className="px-6 py-3.5 rounded-2xl bg-white text-emerald-800 hover:bg-emerald-50 font-extrabold text-sm shadow-md transition cursor-pointer flex items-center gap-2 shrink-0 disabled:opacity-50"
            >
              {isCompleting ? (
                <Loader2 className="w-4 h-4 animate-spin" />
              ) : (
                <CheckCircle className="w-4 h-4 text-emerald-600" />
              )}
              <span>Konfirmasi Pesanan Diterima</span>
            </button>
          </div>
        )}
      </div>
    </div>
  );
}
