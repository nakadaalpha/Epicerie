'use client';

import React, { useState, useEffect, useRef } from 'react';
import Link from 'next/link';
import {
  Truck,
  MapPin,
  Phone,
  Navigation,
  CheckCircle,
  Clock,
  Package,
  ArrowRight,
  LogOut,
  RefreshCw,
  ExternalLink,
  Store,
  ChevronRight,
  Check,
} from 'lucide-react';
import {
  startDeliveryAction,
  completeDeliveryAction,
  updateCourierLocationAction,
} from '@/app/actions/courier';
import { logoutAction } from '@/app/actions/auth';

interface CourierTask {
  id_transaksi: number;
  kode_transaksi: string;
  id_user_pembeli?: number;
  total_bayar: number;
  status: string;
  nama_pembeli?: string;
  no_hp_pembeli?: string;
  label_alamat?: string;
  nama_penerima?: string;
  no_hp_penerima?: string;
  detail_alamat?: string;
  ongkos_kirim?: number;
  total_items?: number;
  created_at: string;
  items: Array<{
    id_detail_transaksi: number;
    id_produk: number;
    nama_produk: string;
    gambar?: string;
    jumlah: number;
    harga_produk_saat_beli: number;
  }>;
}

interface KurirDashboardProps {
  initialTasks: CourierTask[];
  currentUser?: any;
}

export const KurirDashboardClient: React.FC<KurirDashboardProps> = ({
  initialTasks,
  currentUser,
}) => {
  const [tasks, setTasks] = useState<CourierTask[]>(initialTasks);
  const [activeTab, setActiveTab] = useState<'semua' | 'tugas' | 'dikirim' | 'selesai'>('tugas');
  const [loadingId, setLoadingId] = useState<number | null>(null);
  const [activeTrackingId, setActiveTrackingId] = useState<number | null>(null);
  const [currentCoords, setCurrentCoords] = useState<{ lat: number; lng: number } | null>(null);
  const [gpsStatus, setGpsStatus] = useState<string>('Standby');
  const [successMessage, setSuccessMessage] = useState<string | null>(null);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);

  const watchIdRef = useRef<number | null>(null);

  // Toko Lat Long origin (Epicerie HQ Sleman)
  const latToko = -7.73326;
  const longToko = 110.33121;

  // Cleanup geolocation watcher on unmount
  useEffect(() => {
    return () => {
      if (watchIdRef.current !== null && navigator.geolocation) {
        navigator.geolocation.clearWatch(watchIdRef.current);
      }
    };
  }, []);

  const showNotification = (msg: string, isError = false) => {
    if (isError) {
      setErrorMessage(msg);
      setTimeout(() => setErrorMessage(null), 4000);
    } else {
      setSuccessMessage(msg);
      setTimeout(() => setSuccessMessage(null), 4000);
    }
  };

  const handleStartDelivery = async (trxId: number) => {
    setLoadingId(trxId);
    try {
      const res = await startDeliveryAction(trxId);
      if (res.success) {
        setTasks((prev) =>
          prev.map((t) => (t.id_transaksi === trxId ? { ...t, status: 'dikirim' } : t))
        );
        showNotification('Pesanan berhasil diubah menjadi DIKIRIM. GPS Pengantaran Aktif!');

        // Start GPS tracking
        setActiveTrackingId(trxId);
        startGpsBroadcast(trxId);
      } else {
        showNotification(res.error || 'Gagal memulai pengantaran.', true);
      }
    } catch (e: any) {
      showNotification(e.message || 'Terjadi kesalahan sistem.', true);
    } finally {
      setLoadingId(null);
    }
  };

  const handleCompleteDelivery = async (trxId: number) => {
    if (!confirm('Pastikan paket telah diterima oleh pemesan. Tandai pesanan sebagai SELESAI?')) {
      return;
    }

    setLoadingId(trxId);
    try {
      const res = await completeDeliveryAction(trxId);
      if (res.success) {
        setTasks((prev) =>
          prev.map((t) => (t.id_transaksi === trxId ? { ...t, status: 'selesai' } : t))
        );
        showNotification('Selamat! Tugas pengantaran berhasil diselesaikan.');

        if (activeTrackingId === trxId) {
          stopGpsBroadcast();
        }
      } else {
        showNotification(res.error || 'Gagal menyelesaikan pengantaran.', true);
      }
    } catch (e: any) {
      showNotification(e.message || 'Terjadi kesalahan sistem.', true);
    } finally {
      setLoadingId(null);
    }
  };

  const startGpsBroadcast = (trxId: number) => {
    if (!navigator.geolocation) {
      setGpsStatus('Geolocation tidak didukung oleh browser Anda.');
      return;
    }

    setGpsStatus('Menghubungkan sinyal GPS...');
    watchIdRef.current = navigator.geolocation.watchPosition(
      async (pos) => {
        const { latitude, longitude } = pos.coords;
        setCurrentCoords({ lat: latitude, lng: longitude });
        setGpsStatus(`GPS Aktif: ${latitude.toFixed(5)}, ${longitude.toFixed(5)}`);

        // Send to server
        await updateCourierLocationAction({
          id_transaksi: trxId,
          lat: latitude,
          long: longitude,
        });
      },
      (err) => {
        console.warn('GPS Watcher error:', err.message);
        setGpsStatus('Gagal membaca koordinat GPS.');
      },
      {
        enableHighAccuracy: true,
        timeout: 15000,
        maximumAge: 5000,
      }
    );
  };

  const stopGpsBroadcast = () => {
    if (watchIdRef.current !== null && navigator.geolocation) {
      navigator.geolocation.clearWatch(watchIdRef.current);
      watchIdRef.current = null;
    }
    setActiveTrackingId(null);
    setGpsStatus('Standby');
  };

  // Filtering
  const filteredTasks = tasks.filter((t) => {
    const s = t.status.toLowerCase();
    if (activeTab === 'tugas') return s === 'diproses' || s === 'dikemas';
    if (activeTab === 'dikirim') return s === 'dikirim';
    if (activeTab === 'selesai') return s === 'selesai';
    return true;
  });

  const getStatusBadge = (status: string) => {
    const s = status.toLowerCase();
    if (s === 'dikirim') {
      return (
        <span className="px-2.5 py-1 rounded-lg text-xs font-black bg-blue-50 text-blue-600 border border-blue-200/80 animate-pulse">
          DIKIRIM
        </span>
      );
    }
    if (s === 'selesai') {
      return (
        <span className="px-2.5 py-1 rounded-lg text-xs font-black bg-emerald-50 text-emerald-600 border border-emerald-200/80">
          SELESAI
        </span>
      );
    }
    return (
      <span className="px-2.5 py-1 rounded-lg text-xs font-black bg-amber-50 text-amber-600 border border-amber-200/80">
        PERLU DIKIRIM
      </span>
    );
  };

  const formatRupiah = (val: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      maximumFractionDigits: 0,
    }).format(val);
  };

  return (
    <div className="min-h-screen bg-slate-50 text-gray-900 pb-12 font-sans">
      {/* Top Navbar */}
      <header className="bg-white border-b border-gray-200/80 sticky top-0 z-30 shadow-xs">
        <div className="max-w-5xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center text-white shadow-md shadow-blue-200">
              <Truck className="w-5 h-5" />
            </div>
            <div>
              <h1 className="font-black text-base text-gray-900 leading-tight">
                Dashboard Kurir
              </h1>
              <p className="text-[11px] font-semibold text-gray-400">
                Épicerie Delivery Service
              </p>
            </div>
          </div>

          <div className="flex items-center gap-3">
            <div className="text-right hidden sm:block">
              <span className="block text-xs font-bold text-gray-800">
                {currentUser?.nama || 'Petugas Kurir'}
              </span>
              <span className="block text-[10px] font-semibold text-blue-600">
                {gpsStatus}
              </span>
            </div>

            <Link
              href="/"
              className="p-2 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition"
              title="Ke Toko"
            >
              <Store className="w-5 h-5" />
            </Link>

            <form action={logoutAction}>
              <button
                type="submit"
                className="p-2 text-red-500 hover:bg-red-50 rounded-lg transition"
                title="Keluar"
              >
                <LogOut className="w-5 h-5" />
              </button>
            </form>
          </div>
        </div>
      </header>

      {/* Notifications */}
      {successMessage && (
        <div className="max-w-5xl mx-auto px-4 sm:px-6 mt-4">
          <div className="bg-emerald-500 text-white font-bold text-xs p-3.5 rounded-xl shadow-md flex items-center gap-2">
            <Check className="w-4 h-4" />
            <span>{successMessage}</span>
          </div>
        </div>
      )}

      {errorMessage && (
        <div className="max-w-5xl mx-auto px-4 sm:px-6 mt-4">
          <div className="bg-rose-500 text-white font-bold text-xs p-3.5 rounded-xl shadow-md flex items-center gap-2">
            <span>{errorMessage}</span>
          </div>
        </div>
      )}

      {/* Main Content Area */}
      <main className="max-w-5xl mx-auto px-4 sm:px-6 mt-6">
        {/* GPS Live Broadcasting Status Card */}
        {activeTrackingId && (
          <div className="mb-6 p-4 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="w-3 h-3 rounded-full bg-emerald-400 animate-ping" />
              <div>
                <span className="text-xs font-bold uppercase tracking-wider block">
                  Siaran GPS Aktif untuk Pesanan #{activeTrackingId}
                </span>
                <span className="text-[11px] opacity-85">
                  Posisi saat ini: {currentCoords ? `${currentCoords.lat.toFixed(5)}, ${currentCoords.lng.toFixed(5)}` : 'Menghitung...'}
                </span>
              </div>
            </div>
            <button
              onClick={stopGpsBroadcast}
              className="px-3 py-1.5 bg-white/20 hover:bg-white/30 rounded-lg text-xs font-bold transition cursor-pointer"
            >
              Matikan GPS
            </button>
          </div>
        )}

        {/* Tab Filters */}
        <div className="flex items-center gap-2 overflow-x-auto pb-2 mb-6 scrollbar-none">
          <button
            onClick={() => setActiveTab('tugas')}
            className={`px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 ${
              activeTab === 'tugas'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'bg-white text-gray-600 border border-gray-200/80 hover:bg-gray-50'
            }`}
          >
            Perlu Diantar (
            {tasks.filter((t) => t.status === 'diproses' || t.status === 'dikemas').length}
            )
          </button>
          <button
            onClick={() => setActiveTab('dikirim')}
            className={`px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 ${
              activeTab === 'dikirim'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'bg-white text-gray-600 border border-gray-200/80 hover:bg-gray-50'
            }`}
          >
            Sedang Dikirim ({tasks.filter((t) => t.status === 'dikirim').length})
          </button>
          <button
            onClick={() => setActiveTab('selesai')}
            className={`px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 ${
              activeTab === 'selesai'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'bg-white text-gray-600 border border-gray-200/80 hover:bg-gray-50'
            }`}
          >
            Selesai ({tasks.filter((t) => t.status === 'selesai').length})
          </button>
          <button
            onClick={() => setActiveTab('semua')}
            className={`px-4 py-2 rounded-xl text-xs font-bold transition shrink-0 ${
              activeTab === 'semua'
                ? 'bg-blue-600 text-white shadow-sm'
                : 'bg-white text-gray-600 border border-gray-200/80 hover:bg-gray-50'
            }`}
          >
            Semua ({tasks.length})
          </button>
        </div>

        {/* Task Cards List */}
        {filteredTasks.length === 0 ? (
          <div className="bg-white rounded-3xl border border-gray-200/80 p-12 text-center shadow-xs">
            <div className="w-16 h-16 bg-blue-50 text-blue-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
              <Package className="w-8 h-8" />
            </div>
            <h3 className="font-black text-gray-800 text-base mb-1">
              Tidak Ada Tugas Pengantaran
            </h3>
            <p className="text-xs text-gray-400 max-w-sm mx-auto">
              Saat ini belum ada paket pada kategori ini. Anda dapat memeriksa kembali secara berkala.
            </p>
          </div>
        ) : (
          <div className="space-y-4">
            {filteredTasks.map((task) => {
              const addressText = task.detail_alamat || 'Alamat tidak ditentukan (Ambil Sendiri)';
              const destinationQuery = encodeURIComponent(`${addressText} Sleman Yogyakarta`);
              const mapsUrl = `https://www.google.com/maps/dir/?api=1&origin=${latToko},${longToko}&destination=${destinationQuery}&travelmode=driving`;
              const isDelivering = task.status.toLowerCase() === 'dikirim';
              const isCompleted = task.status.toLowerCase() === 'selesai';
              const canStart = task.status.toLowerCase() === 'diproses' || task.status.toLowerCase() === 'dikemas';

              return (
                <div
                  key={task.id_transaksi}
                  className="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs hover:shadow-md transition duration-200"
                >
                  {/* Top Bar: Code, Date & Status */}
                  <div className="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-gray-100">
                    <div className="flex items-center gap-2">
                      <span className="font-black text-sm text-gray-900 tracking-tight">
                        {task.kode_transaksi}
                      </span>
                      <span className="text-xs text-gray-400">
                        • {new Date(task.created_at).toLocaleDateString('id-ID', {
                          day: 'numeric',
                          month: 'short',
                          hour: '2-digit',
                          minute: '2-digit',
                        })}
                      </span>
                    </div>
                    <div>{getStatusBadge(task.status)}</div>
                  </div>

                  {/* Body Content */}
                  <div className="py-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    {/* Customer & Address Details */}
                    <div className="space-y-2">
                      <div className="flex items-start gap-2">
                        <MapPin className="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                        <div>
                          <p className="text-xs font-extrabold text-gray-800">
                            {task.nama_penerima || task.nama_pembeli || 'Pelanggan'}
                            {task.label_alamat ? ` (${task.label_alamat})` : ''}
                          </p>
                          <p className="text-xs text-gray-500 leading-relaxed mt-0.5">
                            {addressText}
                          </p>
                        </div>
                      </div>

                      {(task.no_hp_penerima || task.no_hp_pembeli) && (
                        <div className="flex items-center gap-2 pl-6">
                          <Phone className="w-3.5 h-3.5 text-gray-400" />
                          <a
                            href={`tel:${task.no_hp_penerima || task.no_hp_pembeli}`}
                            className="text-xs font-bold text-blue-600 hover:underline"
                          >
                            {task.no_hp_penerima || task.no_hp_pembeli}
                          </a>
                        </div>
                      )}

                      {/* Navigation Link to Google Maps */}
                      <div className="pt-2 pl-6">
                        <a
                          href={mapsUrl}
                          target="_blank"
                          rel="noreferrer"
                          className="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-xl text-xs font-bold transition border border-emerald-200/60 shadow-xs"
                        >
                          <Navigation className="w-3.5 h-3.5" />
                          <span>Buka Petunjuk Arah (Maps)</span>
                          <ExternalLink className="w-3 h-3 opacity-60" />
                        </a>
                      </div>
                    </div>

                    {/* Products Summary & Total */}
                    <div className="bg-gray-50/60 rounded-xl p-3.5 border border-gray-100 flex flex-col justify-between">
                      <div>
                        <span className="text-[11px] font-bold text-gray-400 uppercase tracking-wider block mb-2">
                          Barang Pesanan ({task.items?.length || task.total_items || 0} item)
                        </span>
                        <div className="space-y-2 max-h-24 overflow-y-auto pr-1">
                          {task.items?.map((item) => (
                            <div
                              key={item.id_detail_transaksi}
                              className="flex items-center justify-between text-xs"
                            >
                              <span className="font-semibold text-gray-700 line-clamp-1">
                                {item.jumlah}x {item.nama_produk}
                              </span>
                              <span className="font-bold text-gray-900 shrink-0">
                                {formatRupiah(item.harga_produk_saat_beli * item.jumlah)}
                              </span>
                            </div>
                          ))}
                        </div>
                      </div>

                      <div className="pt-2 mt-2 border-t border-gray-200/60 flex justify-between items-center text-xs">
                        <span className="text-gray-500 font-bold">Total Tagihan:</span>
                        <span className="font-black text-sm text-blue-600">
                          {formatRupiah(Number(task.total_bayar))}
                        </span>
                      </div>
                    </div>
                  </div>

                  {/* Actions Footer */}
                  <div className="pt-3 border-t border-gray-100 flex items-center justify-end gap-2">
                    {canStart && (
                      <button
                        onClick={() => handleStartDelivery(task.id_transaksi)}
                        disabled={loadingId === task.id_transaksi}
                        className="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-2 cursor-pointer disabled:opacity-50"
                      >
                        <Navigation className="w-4 h-4" />
                        <span>
                          {loadingId === task.id_transaksi ? 'Memproses...' : 'Mulai Antar (Aktifkan GPS)'}
                        </span>
                      </button>
                    )}

                    {isDelivering && (
                      <button
                        onClick={() => handleCompleteDelivery(task.id_transaksi)}
                        disabled={loadingId === task.id_transaksi}
                        className="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow-sm flex items-center gap-2 cursor-pointer disabled:opacity-50"
                      >
                        <CheckCircle className="w-4 h-4" />
                        <span>
                          {loadingId === task.id_transaksi ? 'Menyelesaikan...' : 'Selesaikan Pengantaran'}
                        </span>
                      </button>
                    )}

                    {isCompleted && (
                      <div className="flex items-center gap-1.5 text-xs font-bold text-emerald-600 bg-emerald-50 px-3 py-1.5 rounded-lg">
                        <CheckCircle className="w-4 h-4" />
                        <span>Pengiriman Selesai</span>
                      </div>
                    )}
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </main>
    </div>
  );
};
