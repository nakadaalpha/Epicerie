'use client';

import React, { useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/navigation';
import {
  Menu,
  Box,
  Users,
  Receipt,
  Coins,
  TrendingUp,
  Crown,
  AlertTriangle,
  Clock,
  Check,
  Truck,
  Package,
} from 'lucide-react';
import { formatRupiah } from '@/lib/utils';
import { AdminSidebar } from './AdminSidebar';
import { SalesChart } from './SalesChart';
import { TransactionDetailModal } from './TransactionDetailModal';
import { DashboardData } from '@/app/actions/shop';

interface AdminDashboardClientProps {
  initialStats: DashboardData;
  currentUser?: {
    id_user: number;
    nama: string;
    username: string;
    role: string;
    foto_profil?: string | null;
  } | null;
}

function timeAgo(dateString: string): string {
  try {
    const date = new Date(dateString);
    const now = new Date();
    const diffMs = now.getTime() - date.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffMins < 1) return 'Baru saja';
    if (diffMins < 60) return `${diffMins} menit lalu`;
    if (diffHours < 24) return `${diffHours} jam lalu`;
    if (diffDays === 1) return 'Kemarin';
    return `${diffDays} hari lalu`;
  } catch {
    return 'Baru saja';
  }
}

export const AdminDashboardClient: React.FC<AdminDashboardClientProps> = ({
  initialStats,
  currentUser,
}) => {
  const router = useRouter();
  const [stats, setStats] = useState<DashboardData>(initialStats);
  const [isSidebarOpen, setIsSidebarOpen] = useState(false);
  const [selectedTrx, setSelectedTrx] = useState<DashboardData['transaksiTerbaru'][0] | null>(null);

  const handleTransactionClick = (trx: DashboardData['transaksiTerbaru'][0]) => {
    setSelectedTrx(trx);
  };

  const handleStatusUpdated = () => {
    router.refresh();
  };

  return (
    <div className="bg-gradient-to-br from-blue-500 to-teal-400 h-screen flex overflow-hidden font-sans select-none">
      {/* 1. SIDEBAR KIRI TETAP */}
      <AdminSidebar
        isOpen={isSidebarOpen}
        onClose={() => setIsSidebarOpen(false)}
        currentUser={currentUser}
        pendingCardCount={stats.pendingCardCount}
      />

      {/* 2. AREA KONTEN UTAMA */}
      <main className="flex-1 flex flex-col h-screen overflow-hidden relative">
        {/* Header Khusus Mobile */}
        <header className="md:hidden h-16 flex items-center justify-between px-6 shrink-0 z-30">
          <span className="font-extrabold text-white text-lg drop-shadow-md">
            Ringkasan Toko
          </span>
          <button
            type="button"
            onClick={() => setIsSidebarOpen(true)}
            className="text-white bg-white/20 p-2 rounded-lg backdrop-blur-xs shadow-xs hover:bg-white/30 transition cursor-pointer"
            title="Buka Menu"
          >
            <Menu className="w-6 h-6" />
          </button>
        </header>

        {/* Scrollable Main Content */}
        <div className="flex-1 overflow-y-auto p-4 md:p-8 relative scroll-smooth scrollbar-thin">
          <div className="max-w-7xl mx-auto space-y-8">
            {/* A. 4 KARTU STATISTIK ATAS (FROSTED GLASS) */}
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
              {/* 1. Total Produk */}
              <div className="bg-white/80 backdrop-blur-xs p-5 rounded-2xl shadow-xs border border-white/60 flex flex-col justify-between hover:shadow-md transition">
                <div className="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">
                  Total Produk
                </div>
                <div className="flex justify-between items-end">
                  <span className="text-3xl font-black text-gray-800">
                    {stats.totalProduk}
                  </span>
                  <Box className="w-8 h-8 text-blue-200 stroke-[1.5]" />
                </div>
              </div>

              {/* 2. Pelanggan */}
              <div className="bg-white/80 backdrop-blur-xs p-5 rounded-2xl shadow-xs border border-white/60 flex flex-col justify-between hover:shadow-md transition">
                <div className="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">
                  Pelanggan
                </div>
                <div className="flex justify-between items-end">
                  <span className="text-3xl font-black text-gray-800">
                    {stats.totalUser}
                  </span>
                  <Users className="w-8 h-8 text-teal-200 stroke-[1.5]" />
                </div>
              </div>

              {/* 3. Order Hari Ini */}
              <div className="bg-white/80 backdrop-blur-xs p-5 rounded-2xl shadow-xs border border-white/60 flex flex-col justify-between hover:shadow-md transition">
                <div className="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">
                  Order Hari Ini
                </div>
                <div className="flex justify-between items-end">
                  <span className="text-3xl font-black text-blue-600">
                    {stats.totalTransaksiHariIni}
                  </span>
                  <Receipt className="w-8 h-8 text-indigo-200 stroke-[1.5]" />
                </div>
              </div>

              {/* 4. Omzet Hari Ini */}
              <div className="bg-white/80 backdrop-blur-xs p-5 rounded-2xl shadow-xs border border-white/60 flex flex-col justify-between hover:shadow-md transition">
                <div className="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">
                  Omzet Hari Ini
                </div>
                <div className="flex justify-between items-end">
                  <span className="text-xl font-black text-teal-600">
                    {formatRupiah(stats.omzetHariIni)}
                  </span>
                  <Coins className="w-8 h-8 text-yellow-400 stroke-[1.5]" />
                </div>
              </div>
            </div>

            {/* B. GRID 2 KOLOM (KIRI: CHART + BEST SELLER | KANAN: STOK MENIPIS + TRX TERBARU) */}
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
              {/* BAGIAN KIRI (2 KOLOM) */}
              <div className="lg:col-span-2 space-y-6">
                {/* 1. CHART PENDAPATAN */}
                <div className="bg-white rounded-[2rem] p-6 shadow-xl border border-white/40">
                  <div className="flex justify-between items-center mb-6">
                    <h3 className="font-bold text-gray-800 text-lg flex items-center gap-2">
                      <TrendingUp className="w-5 h-5 text-blue-500" />
                      <span>Pendapatan</span>
                    </h3>
                    <div className="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-xs font-bold">
                      7 Hari Terakhir
                    </div>
                  </div>
                  <SalesChart labels={stats.chartLabels} data={stats.chartData} />
                </div>

                {/* 2. PRODUK PALING LARIS */}
                <div className="bg-white rounded-[2rem] p-6 shadow-xl border border-white/40">
                  <h3 className="font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <Crown className="w-5 h-5 text-yellow-500" />
                    <span>Paling Laris</span>
                  </h3>
                  <div className="overflow-x-auto">
                    <table className="w-full text-sm text-left text-gray-500">
                      <thead className="text-xs text-gray-400 uppercase bg-gray-50 rounded-lg">
                        <tr>
                          <th className="px-4 py-3 rounded-l-lg">Produk</th>
                          <th className="px-4 py-3 text-center">Terjual</th>
                          <th className="px-4 py-3 text-right rounded-r-lg">Stok</th>
                        </tr>
                      </thead>
                      <tbody>
                        {stats.produkTerlaris && stats.produkTerlaris.length > 0 ? (
                          stats.produkTerlaris.map((item, index) => (
                            <tr
                              key={item.id_produk}
                              className="border-b border-gray-50 last:border-0 hover:bg-gray-50 transition"
                            >
                              <td className="px-4 py-3 font-bold text-gray-700 flex items-center gap-3">
                                <div className="w-6 h-6 rounded-md bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-extrabold shadow-2xs shrink-0">
                                  {index + 1}
                                </div>
                                <span className="truncate max-w-[200px] font-bold">
                                  {item.nama_produk}
                                </span>
                              </td>
                              <td className="px-4 py-3 text-center">
                                <span className="bg-blue-50 text-blue-600 text-xs font-extrabold px-2.5 py-0.5 rounded-md">
                                  {item.total_terjual}
                                </span>
                              </td>
                              <td
                                className={`px-4 py-3 text-right font-extrabold ${
                                  item.stok <= 15 ? 'text-red-500' : 'text-gray-400'
                                }`}
                              >
                                {item.stok}
                              </td>
                            </tr>
                          ))
                        ) : (
                          <tr>
                            <td colSpan={3} className="text-center py-6 text-gray-400 text-xs">
                              Belum ada data produk terjual.
                            </td>
                          </tr>
                        )}
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>

              {/* BAGIAN KANAN (1 KOLOM) */}
              <div className="space-y-6">
                {/* 1. STOK MENIPIS */}
                <div className="bg-white rounded-[2rem] p-6 shadow-xl border border-white/40 relative overflow-hidden">
                  <div className="absolute -top-6 -right-6 w-24 h-24 bg-red-100 rounded-full opacity-50 blur-xl" />
                  <h3 className="font-bold text-gray-800 mb-4 flex items-center gap-2 relative z-10">
                    <AlertTriangle className="w-5 h-5 text-red-500" />
                    <span>Stok Menipis</span>
                  </h3>
                  <div className="space-y-3 relative z-10">
                    {stats.stokHampirHabis && stats.stokHampirHabis.length > 0 ? (
                      stats.stokHampirHabis.map((item) => (
                        <div
                          key={item.id_produk}
                          className="flex justify-between items-center bg-gray-50 p-3 rounded-xl border border-gray-100 hover:border-red-200 hover:bg-red-50 transition group"
                        >
                          <div className="flex items-center gap-3 overflow-hidden">
                            <div className="w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse shrink-0" />
                            <div className="min-w-0">
                              <p className="font-bold text-gray-700 text-sm truncate max-w-[130px]">
                                {item.nama_produk}
                              </p>
                              <p className="text-[10px] text-gray-400 group-hover:text-red-500">
                                Sisa: <span className="font-bold">{item.stok}</span>
                              </p>
                            </div>
                          </div>
                          <Link
                            href="/admin"
                            className="text-[10px] font-bold bg-white text-gray-600 border border-gray-200 px-3 py-1.5 rounded-lg hover:text-red-500 hover:border-red-200 shadow-2xs transition"
                          >
                            + Isi
                          </Link>
                        </div>
                      ))
                    ) : (
                      <div className="text-center py-6 text-gray-400 text-xs">
                        Stok aman terkendali.
                      </div>
                    )}
                  </div>
                </div>

                {/* 2. TRANSAKSI TERBARU */}
                <div className="bg-white rounded-[2rem] p-6 shadow-xl border border-white/40 relative overflow-hidden">
                  <div className="flex justify-between items-center mb-4">
                    <h3 className="font-bold text-gray-800 flex items-center gap-2">
                      <Clock className="w-5 h-5 text-blue-500" />
                      <span>Transaksi Terbaru</span>
                    </h3>
                    <Link
                      href="/admin"
                      className="text-[10px] font-bold text-blue-500 hover:text-blue-700 hover:underline"
                    >
                      Lihat Semua
                    </Link>
                  </div>

                  <div className="space-y-3">
                    {stats.transaksiTerbaru && stats.transaksiTerbaru.length > 0 ? (
                      stats.transaksiTerbaru.map((trx) => {
                        const statusLower = (trx.status || '').toLowerCase();
                        return (
                          <div
                            key={trx.id_transaksi}
                            onClick={() => handleTransactionClick(trx)}
                            className="flex items-center gap-3 p-3 rounded-xl hover:bg-blue-50/50 border border-transparent hover:border-blue-100 transition cursor-pointer group"
                          >
                            {/* Status Circle Icon */}
                            <div
                              className={`w-10 h-10 rounded-full flex items-center justify-center shrink-0 ${
                                statusLower === 'selesai'
                                  ? 'bg-green-100 text-green-600'
                                  : statusLower === 'dikirim'
                                  ? 'bg-blue-100 text-blue-600'
                                  : 'bg-yellow-100 text-yellow-600'
                              }`}
                            >
                              {statusLower === 'selesai' ? (
                                <Check className="w-5 h-5 stroke-[2.5]" />
                              ) : statusLower === 'dikirim' ? (
                                <Truck className="w-5 h-5 stroke-[2]" />
                              ) : (
                                <Package className="w-5 h-5 stroke-[2]" />
                              )}
                            </div>

                            {/* Info */}
                            <div className="flex-1 min-w-0">
                              <div className="flex justify-between items-center">
                                <p className="text-xs font-bold text-gray-800 group-hover:text-blue-600 transition truncate">
                                  {trx.kode_transaksi}
                                </p>
                                <p className="text-[10px] text-gray-400 shrink-0 ml-1">
                                  {timeAgo(trx.created_at)}
                                </p>
                              </div>
                              <p className="text-[11px] text-gray-500 truncate mt-0.5">
                                {trx.nama_pembeli || trx.nama_pelanggan_hold || 'Guest'} •{' '}
                                <span className="font-bold text-gray-700">
                                  {formatRupiah(trx.total_bayar)}
                                </span>
                              </p>
                            </div>
                          </div>
                        );
                      })
                    ) : (
                      <div className="text-center py-6 text-gray-400 text-xs">
                        Belum ada transaksi.
                      </div>
                    )}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>

      {/* 3. MODAL DETAIL TRANSAKSI POPUP */}
      <TransactionDetailModal
        isOpen={selectedTrx !== null}
        onClose={() => setSelectedTrx(null)}
        transaction={selectedTrx}
        onStatusUpdated={handleStatusUpdated}
      />
    </div>
  );
};
