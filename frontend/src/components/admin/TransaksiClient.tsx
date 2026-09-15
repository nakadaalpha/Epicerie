'use client';

import React, { useState, useTransition } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import {
  Menu,
  Receipt,
  Search,
  Filter,
  ArrowUpDown,
  Truck,
  CheckCircle,
  Clock,
  Box,
  Eye,
  Send,
  Check,
  X,
} from 'lucide-react';
import { AdminSidebar } from './AdminSidebar';
import { TransactionDetailModal } from './TransactionDetailModal';
import { formatRupiah } from '@/lib/utils';
import { updateOrderStatusAction } from '@/app/actions/shop';

interface TransaksiClientProps {
  initialTransactions: any[];
  currentUser: any;
  pendingCardCount: number;
}

export function TransaksiClient({
  initialTransactions,
  currentUser,
  pendingCardCount,
}: TransaksiClientProps) {
  const router = useRouter();
  const searchParams = useSearchParams();

  const [isSidebarOpen, setIsSidebarOpen] = useState(false);
  const [selectedTrx, setSelectedTrx] = useState<any | null>(null);
  const [searchTerm, setSearchTerm] = useState(searchParams.get('search') || '');
  const [statusFilter, setStatusFilter] = useState(searchParams.get('status') || '');
  const [sortFilter, setSortFilter] = useState(searchParams.get('sort') || 'terbaru');
  const [isPending, startTransition] = useTransition();

  const handleFilterChange = (newStatus?: string, newSort?: string, newSearch?: string) => {
    const params = new URLSearchParams();
    const s = newStatus !== undefined ? newStatus : statusFilter;
    const so = newSort !== undefined ? newSort : sortFilter;
    const q = newSearch !== undefined ? newSearch : searchTerm;

    if (s) params.set('status', s);
    if (so) params.set('sort', so);
    if (q) params.set('search', q);

    startTransition(() => {
      router.push(`/admin/transaksi?${params.toString()}`);
    });
  };

  const handleQuickStatus = async (idTransaksi: number, nextStatus: string) => {
    const res = await updateOrderStatusAction(idTransaksi, nextStatus);
    if (res.success) {
      startTransition(() => {
        router.refresh();
      });
    } else {
      alert(res.error || 'Gagal mengubah status pesanan');
    }
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
            Kelola Pesanan
          </span>
          <button
            type="button"
            onClick={() => setIsSidebarOpen(true)}
            className="text-white bg-white/20 p-2 rounded-lg backdrop-blur-xs shadow-xs hover:bg-white/30 transition cursor-pointer"
          >
            <Menu className="w-6 h-6" />
          </button>
        </header>

        {/* Scrollable Container */}
        <div className="flex-1 overflow-y-auto p-4 md:p-8 relative scrollbar-thin">
          <div className="max-w-7xl mx-auto space-y-6">
            {/* Header Title */}
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
              <div>
                <h1 className="text-2xl md:text-3xl font-black text-white drop-shadow-sm">
                  Daftar Pesanan
                </h1>
                <p className="text-white/80 text-sm mt-0.5">
                  Pantau, perbarui status, dan proses pengiriman pesanan pelanggan.
                </p>
              </div>
            </div>

            {/* Filter Controls */}
            <div className="flex flex-col md:flex-row gap-4">
              {/* Search Bar */}
              <div className="relative flex-1">
                <input
                  type="text"
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  onKeyDown={(e) => e.key === 'Enter' && handleFilterChange(undefined, undefined, searchTerm)}
                  placeholder="Cari Kode Transaksi / Nama Pembeli..."
                  className="w-full p-3.5 pl-12 pr-10 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 placeholder-gray-400 transition bg-white/80 backdrop-blur-md border border-white/40 font-medium"
                />
                <Search className="absolute left-4 top-3.5 w-5 h-5 text-blue-500" />
                {searchTerm && (
                  <button
                    type="button"
                    onClick={() => {
                      setSearchTerm('');
                      handleFilterChange(undefined, undefined, '');
                    }}
                    className="absolute right-4 top-3.5 text-gray-400 hover:text-red-500 transition cursor-pointer"
                  >
                    <X className="w-5 h-5" />
                  </button>
                )}
              </div>

              {/* Status Filter */}
              <div className="relative min-w-[180px]">
                <select
                  value={statusFilter}
                  onChange={(e) => {
                    setStatusFilter(e.target.value);
                    handleFilterChange(e.target.value, undefined, undefined);
                  }}
                  className="w-full p-3.5 pl-10 pr-8 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 bg-white/80 backdrop-blur-md border border-white/40 cursor-pointer font-bold appearance-none"
                >
                  <option value="">Semua Status</option>
                  <option value="dikemas">📦 Dikemas</option>
                  <option value="dikirim">🚚 Dikirim</option>
                  <option value="selesai">✅ Selesai</option>
                  <option value="batal">❌ Dibatalkan</option>
                </select>
                <Filter className="absolute left-3.5 top-3.5 w-5 h-5 text-blue-500 pointer-events-none" />
              </div>

              {/* Sort Filter */}
              <div className="relative min-w-[180px]">
                <select
                  value={sortFilter}
                  onChange={(e) => {
                    setSortFilter(e.target.value);
                    handleFilterChange(undefined, e.target.value, undefined);
                  }}
                  className="w-full p-3.5 pl-10 pr-8 rounded-2xl shadow-lg outline-none focus:ring-2 focus:ring-white/50 text-gray-700 bg-white/80 backdrop-blur-md border border-white/40 cursor-pointer font-bold appearance-none"
                >
                  <option value="terbaru">Terbaru Ditambahkan</option>
                  <option value="terlama">Terlama</option>
                  <option value="terbesar">Nominal Tertinggi</option>
                  <option value="terkecil">Nominal Terendah</option>
                </select>
                <ArrowUpDown className="absolute left-3.5 top-3.5 w-5 h-5 text-blue-500 pointer-events-none" />
              </div>
            </div>

            {/* Main Orders Table Card */}
            <div className="bg-white rounded-[2rem] p-6 md:p-8 shadow-2xl min-h-[550px] relative border border-white/40">
              <div className="flex flex-col md:flex-row justify-between items-end md:items-center mb-6 border-b border-gray-100 pb-4">
                <div>
                  <h2 className="text-gray-800 font-extrabold text-2xl tracking-tight">
                    Pesanan Masuk
                  </h2>
                  <p className="text-gray-400 text-sm mt-1">
                    Kelola dan ubah status pengiriman secara langsung.
                  </p>
                </div>
                <span className="text-xs font-bold text-blue-600 bg-blue-50 px-4 py-2 rounded-full mt-2 md:mt-0 shadow-xs border border-blue-100">
                  Total: {initialTransactions.length} Pesanan
                </span>
              </div>

              {/* Table Header Desktop */}
              <div className="hidden md:flex px-4 py-3 bg-gray-50/50 rounded-xl mb-3 text-xs font-bold text-gray-400 uppercase tracking-wider border border-gray-100">
                <div className="w-20">Faktur</div>
                <div className="w-48">Pelanggan</div>
                <div className="flex-1">Item Pesanan</div>
                <div className="w-32">Total</div>
                <div className="w-32 text-center">Status</div>
                <div className="w-44 text-center">Pengiriman</div>
                <div className="w-20 text-right">Aksi</div>
              </div>

              {/* Order List */}
              {initialTransactions.length === 0 ? (
                <div className="py-24 text-center">
                  <div className="w-20 h-20 bg-blue-50 text-blue-400 rounded-3xl flex items-center justify-center mx-auto mb-4 border border-blue-100 shadow-sm">
                    <Receipt className="w-10 h-10" />
                  </div>
                  <h3 className="font-bold text-gray-700 text-lg">Tidak ada transaksi ditemukan</h3>
                  <p className="text-gray-400 text-sm max-w-sm mx-auto mt-1">
                    Pesanan dengan filter yang dipilih belum ada. Coba gunakan kata kunci atau filter lain.
                  </p>
                </div>
              ) : (
                <div className="flex flex-col gap-3">
                  {initialTransactions.map((trx) => {
                    const statusLower = String(trx.status || '').toLowerCase();
                    const isSelesai = statusLower === 'selesai';
                    const isKirim = statusLower === 'dikirim';
                    const isKemas = statusLower === 'dikemas' || statusLower === 'diproses' || statusLower === 'pending';

                    return (
                      <div
                        key={trx.id_transaksi}
                        className="group flex flex-col md:flex-row md:items-center p-4 bg-white border border-gray-100 rounded-2xl hover:shadow-md hover:border-blue-200 hover:bg-blue-50/20 transition-all duration-200 relative overflow-hidden"
                      >
                        <div className="absolute left-0 top-0 bottom-0 w-1.5 bg-blue-500 opacity-0 group-hover:opacity-100 transition-opacity" />

                        {/* Col 1: Icon & Invoice */}
                        <div
                          className="flex items-center w-full md:w-20 mb-3 md:mb-0 cursor-pointer"
                          onClick={() => setSelectedTrx(trx)}
                        >
                          <div className="w-11 h-11 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center font-bold shadow-xs border border-blue-100 group-hover:bg-white transition">
                            <Receipt className="w-5 h-5" />
                          </div>
                          <div className="md:hidden ml-3">
                            <h3 className="font-bold text-gray-800 text-sm">{trx.kode_transaksi}</h3>
                            <p className="text-xs text-gray-400">
                              {new Date(trx.created_at).toLocaleDateString('id-ID', {
                                day: 'numeric',
                                month: 'short',
                                year: 'numeric',
                              })}
                            </p>
                          </div>
                        </div>

                        {/* Col 2: Customer */}
                        <div className="w-full md:w-48 mb-2 md:mb-0 pr-2">
                          <h3 className="hidden md:block font-bold text-gray-800 text-sm truncate">
                            {trx.kode_transaksi}
                          </h3>
                          <p className="text-xs font-bold text-gray-700 truncate">
                            {trx.nama_pembeli || trx.nama_pelanggan_hold || 'Pelanggan Walk-in'}
                          </p>
                          {trx.no_hp_pembeli && (
                            <p className="text-[11px] text-gray-400 truncate">{trx.no_hp_pembeli}</p>
                          )}
                        </div>

                        {/* Col 3: Items Preview */}
                        <div
                          className="flex-1 min-w-0 pr-4 mb-2 md:mb-0 cursor-pointer"
                          onClick={() => setSelectedTrx(trx)}
                        >
                          {trx.items && trx.items.length > 0 ? (
                            <div className="flex flex-col gap-0.5">
                              <span className="text-xs font-semibold text-gray-700 truncate">
                                <span className="font-black text-blue-600 mr-1">
                                  {trx.items[0].jumlah}x
                                </span>
                                {trx.items[0].nama_produk}
                              </span>
                              {trx.items.length > 1 && (
                                <span className="text-[11px] font-bold text-blue-500">
                                  +{trx.items.length - 1} produk lainnya
                                </span>
                              )}
                            </div>
                          ) : (
                            <span className="text-xs text-gray-400">
                              {trx.total_items || 0} barang
                            </span>
                          )}
                        </div>

                        {/* Col 4: Total Amount */}
                        <div className="w-full md:w-32 flex items-center mb-2 md:mb-0">
                          <span className="font-extrabold text-blue-600 text-sm">
                            {formatRupiah(trx.total_bayar)}
                          </span>
                        </div>

                        {/* Col 5: Status Badge */}
                        <div className="w-full md:w-32 flex md:justify-center items-center mb-2 md:mb-0">
                          <span
                            className={`text-[10px] font-bold px-3 py-1 rounded-full border flex items-center gap-1.5 shadow-xs ${
                              isSelesai
                                ? 'bg-green-50 text-green-700 border-green-200'
                                : isKirim
                                ? 'bg-blue-50 text-blue-700 border-blue-200'
                                : isKemas
                                ? 'bg-yellow-50 text-yellow-700 border-yellow-200'
                                : 'bg-gray-100 text-gray-700 border-gray-200'
                            }`}
                          >
                            {isSelesai && <CheckCircle className="w-3 h-3 text-green-600" />}
                            {isKirim && <Truck className="w-3 h-3 text-blue-600" />}
                            {isKemas && <Box className="w-3 h-3 text-yellow-600" />}
                            {String(trx.status || 'PENDING').toUpperCase()}
                          </span>
                        </div>

                        {/* Col 6: Quick Action Button */}
                        <div className="w-full md:w-44 flex items-center justify-center mb-2 md:mb-0 px-2">
                          {isKemas && (
                            <button
                              type="button"
                              onClick={() => handleQuickStatus(trx.id_transaksi, 'dikirim')}
                              className="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-xs transition flex items-center justify-center gap-2 w-full cursor-pointer"
                            >
                              <Send className="w-3.5 h-3.5" /> Kirim Sekarang
                            </button>
                          )}
                          {isKirim && (
                            <button
                              type="button"
                              onClick={() => handleQuickStatus(trx.id_transaksi, 'selesai')}
                              className="bg-green-600 hover:bg-green-700 text-white text-xs font-bold px-4 py-2 rounded-xl shadow-xs transition flex items-center justify-center gap-2 w-full cursor-pointer"
                            >
                              <Check className="w-3.5 h-3.5" /> Tandai Selesai
                            </button>
                          )}
                          {isSelesai && (
                            <span className="text-[11px] font-bold text-gray-400 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100 flex items-center gap-1">
                              <CheckCircle className="w-3.5 h-3.5 text-green-500" /> Selesai
                            </span>
                          )}
                        </div>

                        {/* Col 7: View Detail */}
                        <div className="flex items-center justify-end w-full md:w-20 gap-2">
                          <button
                            type="button"
                            onClick={() => setSelectedTrx(trx)}
                            className="bg-white text-gray-600 hover:text-blue-600 p-2 rounded-xl border border-gray-200 hover:border-blue-300 shadow-xs transition cursor-pointer"
                            title="Lihat Detail Transaksi"
                          >
                            <Eye className="w-4 h-4" />
                          </button>
                        </div>
                      </div>
                    );
                  })}
                </div>
              )}
            </div>
          </div>
        </div>
      </main>

      {/* Detail Modal */}
      {selectedTrx && (
        <TransactionDetailModal
          isOpen={Boolean(selectedTrx)}
          transaction={selectedTrx}
          onClose={() => setSelectedTrx(null)}
          onStatusUpdated={() => {
            router.refresh();
          }}
        />
      )}
    </div>
  );
}
