'use client';

import React, { useState, useTransition } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import {
  Menu,
  FileText,
  DollarSign,
  ShoppingCart,
  TrendingUp,
  Printer,
  Calendar,
  Filter,
} from 'lucide-react';
import { AdminSidebar } from './AdminSidebar';
import { formatRupiah } from '@/lib/utils';
import { SalesChart } from './SalesChart';

interface LaporanClientProps {
  reportData: {
    totalOmzet: number;
    totalTransaksi: number;
    labelPeriode: string;
    range: string;
    labels: string[];
    chartData: number[];
    dailyData: any[];
  };
  currentUser: any;
  pendingCardCount: number;
}

export function LaporanClient({
  reportData,
  currentUser,
  pendingCardCount,
}: LaporanClientProps) {
  const router = useRouter();
  const searchParams = useSearchParams();
  const [isSidebarOpen, setIsSidebarOpen] = useState(false);
  const [selectedRange, setSelectedRange] = useState(reportData.range || 'hari_ini');
  const [isPending, startTransition] = useTransition();

  const handleRangeChange = (range: string) => {
    setSelectedRange(range);
    startTransition(() => {
      router.push(`/admin/laporan?range=${range}`);
    });
  };

  const handlePrint = () => {
    window.print();
  };

  return (
    <div className="bg-gradient-to-br from-blue-500 to-teal-400 h-screen flex overflow-hidden font-sans select-none">
      <AdminSidebar
        isOpen={isSidebarOpen}
        onClose={() => setIsSidebarOpen(false)}
        currentUser={currentUser}
        pendingCardCount={pendingCardCount}
      />

      <main className="flex-1 flex flex-col h-screen overflow-hidden relative">
        {/* Mobile Header */}
        <header className="md:hidden h-16 flex items-center justify-between px-6 shrink-0 z-30">
          <span className="font-extrabold text-white text-lg drop-shadow-md">
            Laporan Keuangan
          </span>
          <button
            type="button"
            onClick={() => setIsSidebarOpen(true)}
            className="text-white bg-white/20 p-2 rounded-lg backdrop-blur-xs shadow-xs hover:bg-white/30 transition cursor-pointer"
          >
            <Menu className="w-6 h-6" />
          </button>
        </header>

        {/* Content */}
        <div className="flex-1 overflow-y-auto p-4 md:p-8 relative scrollbar-thin">
          <div className="max-w-7xl mx-auto space-y-6">
            {/* Header Controls */}
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
              <div>
                <h1 className="text-2xl md:text-3xl font-black text-white drop-shadow-sm">
                  Laporan Keuangan & Penjualan
                </h1>
                <p className="text-white/80 text-sm mt-0.5">
                  Ringkasan omzet toko, transaksi berhasil, dan grafik pertumbuhan bisnis.
                </p>
              </div>

              <div className="flex items-center gap-3">
                {/* Range Filter */}
                <div className="relative">
                  <select
                    value={selectedRange}
                    onChange={(e) => handleRangeChange(e.target.value)}
                    className="bg-white border border-gray-200 text-gray-700 py-3 pl-4 pr-10 rounded-2xl focus:outline-none focus:ring-2 focus:ring-blue-500 font-bold shadow-md cursor-pointer appearance-none"
                  >
                    <option value="hari_ini">Hari Ini</option>
                    <option value="1_minggu">7 Hari Terakhir</option>
                    <option value="1_bulan">1 Bulan Terakhir</option>
                    <option value="6_bulan">6 Bulan Terakhir</option>
                    <option value="1_tahun">1 Tahun Terakhir</option>
                  </select>
                  <Filter className="pointer-events-none absolute right-3 top-3.5 w-4 h-4 text-gray-400" />
                </div>

                {/* Print Button */}
                <button
                  type="button"
                  onClick={handlePrint}
                  className="bg-blue-600 hover:bg-blue-700 text-white py-3 px-5 rounded-2xl font-bold shadow-lg shadow-blue-500/30 transition flex items-center gap-2 cursor-pointer"
                >
                  <Printer className="w-4 h-4" />
                  <span className="hidden md:inline">Cetak Laporan</span>
                </button>
              </div>
            </div>

            {/* Top 2 Big Summary Cards */}
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
              {/* Total Omzet */}
              <div className="bg-white rounded-[2rem] p-6 shadow-xl border border-white/40 flex items-center justify-between group hover:shadow-2xl transition relative overflow-hidden">
                <div className="absolute right-0 top-0 p-4 opacity-5 pointer-events-none">
                  <DollarSign className="w-44 h-44 text-blue-600 transform rotate-12" />
                </div>
                <div className="relative z-10">
                  <p className="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">
                    Total Pendapatan (Omzet)
                  </p>
                  <h2 className="text-3xl md:text-4xl font-black text-gray-800">
                    {formatRupiah(reportData.totalOmzet)}
                  </h2>
                  <p className="text-xs text-gray-400 mt-2 font-medium">
                    Periode: <span className="font-bold text-blue-600">{reportData.labelPeriode}</span>
                  </p>
                </div>
                <div className="relative z-10 w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-3xl shadow-xs group-hover:scale-110 transition duration-300">
                  <DollarSign className="w-8 h-8" />
                </div>
              </div>

              {/* Total Transaksi */}
              <div className="bg-white rounded-[2rem] p-6 shadow-xl border border-white/40 flex items-center justify-between group hover:shadow-2xl transition relative overflow-hidden">
                <div className="absolute right-0 top-0 p-4 opacity-5 pointer-events-none">
                  <ShoppingCart className="w-44 h-44 text-green-600 transform -rotate-12" />
                </div>
                <div className="relative z-10">
                  <p className="text-gray-400 text-xs font-bold uppercase tracking-wider mb-1">
                    Transaksi Berhasil
                  </p>
                  <h2 className="text-3xl md:text-4xl font-black text-gray-800">
                    {reportData.totalTransaksi}{' '}
                    <span className="text-base text-gray-400 font-medium">Pesanan Selesai</span>
                  </h2>
                  <p className="text-xs text-gray-400 mt-2 font-medium">
                    Periode: <span className="font-bold text-green-600">{reportData.labelPeriode}</span>
                  </p>
                </div>
                <div className="relative z-10 w-16 h-16 bg-green-50 text-green-600 rounded-2xl flex items-center justify-center text-3xl shadow-xs group-hover:scale-110 transition duration-300">
                  <ShoppingCart className="w-8 h-8" />
                </div>
              </div>
            </div>

            {/* Sales Chart Section */}
            <div className="bg-white rounded-[2rem] p-6 md:p-8 shadow-2xl border border-white/40">
              <div className="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                <h3 className="text-gray-800 font-extrabold text-xl flex items-center gap-2">
                  <TrendingUp className="w-6 h-6 text-blue-600" />
                  Grafik Tren Pendapatan
                </h3>
                <span className="text-xs font-bold bg-gray-100 text-gray-600 px-3.5 py-1.5 rounded-full">
                  {reportData.labelPeriode}
                </span>
              </div>

              <div className="h-80 w-full">
                <SalesChart
                  labels={
                    reportData.labels.length > 0
                      ? reportData.labels
                      : ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']
                  }
                  data={
                    reportData.chartData.length > 0
                      ? reportData.chartData
                      : [0, 0, 0, 0, 0, 0, 0]
                  }
                />
              </div>
            </div>

            {/* Detailed Daily Breakdown Table */}
            {reportData.dailyData && reportData.dailyData.length > 0 && (
              <div className="bg-white rounded-[2rem] p-6 md:p-8 shadow-2xl border border-white/40">
                <h3 className="text-gray-800 font-extrabold text-xl mb-6">
                  Rincian Penjualan Per Hari
                </h3>
                <div className="overflow-x-auto">
                  <table className="w-full text-left border-collapse">
                    <thead>
                      <tr className="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider border-b border-gray-200">
                        <th className="p-4">Tanggal</th>
                        <th className="p-4 text-center">Jumlah Transaksi</th>
                        <th className="p-4 text-right">Total Penerimaan</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                      {reportData.dailyData.map((d, idx) => (
                        <tr key={idx} className="hover:bg-gray-50/70 transition">
                          <td className="p-4 font-bold text-gray-700 text-sm">{d.tanggal}</td>
                          <td className="p-4 text-center font-bold text-gray-600 text-sm">
                            {d.jumlah_transaksi} Transaksi
                          </td>
                          <td className="p-4 text-right font-black text-blue-600 text-sm">
                            {formatRupiah(d.total)}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}
          </div>
        </div>
      </main>
    </div>
  );
}
