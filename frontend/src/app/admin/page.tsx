import React from 'react';
import { query } from '@/lib/db';
import { Navbar } from '@/components/Navbar';
import { formatRupiah } from '@/lib/utils';
import {
  DollarSign,
  ShoppingBag,
  Package,
  AlertTriangle,
  Clock,
  CheckCircle2,
  TrendingUp,
} from 'lucide-react';
import { Transaksi, Produk } from '@/types';

export const dynamic = 'force-dynamic';

export default async function AdminPage() {
  // Fetch stats and data in parallel
  const [
    totalSalesRes,
    trxCountRes,
    productCountRes,
    lowStockRes,
    recentTrxRes,
    productsRes,
  ] = await Promise.all([
    query<{ total: string }>(
      "SELECT COALESCE(SUM(total_bayar), 0) as total FROM transaksi WHERE status = 'selesai'"
    ).catch(() => [{ total: '0' }]),
    query<{ count: string }>('SELECT COUNT(*) as count FROM transaksi').catch(() => [{ count: '0' }]),
    query<{ count: string }>('SELECT COUNT(*) as count FROM produk').catch(() => [{ count: '0' }]),
    query<{ count: string }>('SELECT COUNT(*) as count FROM produk WHERE stok <= 5').catch(() => [{ count: '0' }]),
    query<Transaksi>('SELECT * FROM transaksi ORDER BY created_at DESC LIMIT 8').catch(() => []),
    query<any>(`
      SELECT p.*, p.harga_produk as harga, k.nama_kategori 
      FROM produk p 
      LEFT JOIN kategori k ON p.id_kategori = k.id_kategori 
      ORDER BY p.stok ASC 
      LIMIT 10
    `).catch(() => []),
  ]);

  const totalSales = Number(totalSalesRes[0]?.total || 0);
  const totalTrx = Number(trxCountRes[0]?.count || 0);
  const totalProducts = Number(productCountRes[0]?.count || 0);
  const lowStockCount = Number(lowStockRes[0]?.count || 0);
  const recentTransactions = recentTrxRes || [];
  const lowStockProducts = productsRes || [];

  return (
    <div className="min-h-screen bg-gray-50 flex flex-col">
      <Navbar />

      <main className="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        {/* Header Title */}
        <div>
          <h1 className="text-2xl font-black text-gray-900 tracking-tight">
            Dasbor Pengelola Épicerie
          </h1>
          <p className="text-xs text-gray-500 mt-1">
            Ringkasan performa penjualan, stok barang, dan pemantauan transaksi toko.
          </p>
        </div>

        {/* 4 Stat Cards */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {/* Total Revenue */}
          <div className="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
              <span className="text-xs font-bold text-gray-500 block">
                Total Penjualan
              </span>
              <span className="text-xl sm:text-2xl font-black text-gray-900 mt-1 block">
                {formatRupiah(totalSales)}
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
              <DollarSign className="w-6 h-6" />
            </div>
          </div>

          {/* Total Transactions */}
          <div className="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
              <span className="text-xs font-bold text-gray-500 block">
                Total Transaksi
              </span>
              <span className="text-xl sm:text-2xl font-black text-gray-900 mt-1 block">
                {totalTrx} Pesanan
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center">
              <ShoppingBag className="w-6 h-6" />
            </div>
          </div>

          {/* Total Products */}
          <div className="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
              <span className="text-xs font-bold text-gray-500 block">
                Katalog Produk
              </span>
              <span className="text-xl sm:text-2xl font-black text-gray-900 mt-1 block">
                {totalProducts} Varian
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
              <Package className="w-6 h-6" />
            </div>
          </div>

          {/* Stock Alert */}
          <div className="bg-white p-5 rounded-3xl border border-gray-100 shadow-sm flex items-center justify-between">
            <div>
              <span className="text-xs font-bold text-gray-500 block">
                Perlu Restok
              </span>
              <span className="text-xl sm:text-2xl font-black text-amber-600 mt-1 block">
                {lowStockCount} Produk
              </span>
            </div>
            <div className="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
              <AlertTriangle className="w-6 h-6" />
            </div>
          </div>
        </div>

        {/* 2-Column Grid: Recent Transactions & Low Stock Table */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
          {/* Recent Transactions */}
          <div className="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-4">
                <h2 className="font-extrabold text-base text-gray-900 flex items-center gap-2">
                  <Clock className="w-4 h-4 text-blue-600" />
                  <span>Transaksi Kasir Terbaru</span>
                </h2>
                <span className="text-xs font-semibold text-gray-400">
                  8 Terakhir
                </span>
              </div>

              <div className="divide-y divide-gray-50 overflow-x-auto">
                <table className="w-full text-left text-xs">
                  <thead>
                    <tr className="text-gray-400 font-bold border-b border-gray-100">
                      <th className="pb-3">Kode / Pelanggan</th>
                      <th className="pb-3">Metode</th>
                      <th className="pb-3 text-right">Total</th>
                      <th className="pb-3 text-right">Status</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-50">
                    {recentTransactions.map((trx) => (
                      <tr key={trx.id_transaksi} className="hover:bg-gray-50/60">
                        <td className="py-3">
                          <span className="font-bold text-gray-800 block">
                            {trx.kode_transaksi}
                          </span>
                          <span className="text-[11px] text-gray-400">
                            {trx.nama_pelanggan_hold || 'Walk-in'} •{' '}
                            {new Date(trx.created_at || '').toLocaleTimeString('id-ID', {
                              hour: '2-digit',
                              minute: '2-digit',
                            })}
                          </span>
                        </td>
                        <td className="py-3">
                          <span className="px-2 py-0.5 rounded-md bg-gray-100 font-semibold text-gray-600 text-[11px]">
                            {trx.metode_pembayaran || 'Tunai'}
                          </span>
                        </td>
                        <td className="py-3 text-right font-extrabold text-gray-900">
                          {formatRupiah(Number(trx.total_bayar))}
                        </td>
                        <td className="py-3 text-right">
                          <span className="inline-flex items-center gap-1 text-emerald-600 font-bold bg-emerald-50 px-2 py-0.5 rounded-full text-[10px]">
                            <CheckCircle2 className="w-3 h-3" />
                            <span>{trx.status}</span>
                          </span>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          {/* Low Stock Warning */}
          <div className="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm flex flex-col justify-between">
            <div>
              <div className="flex items-center justify-between mb-4">
                <h2 className="font-extrabold text-base text-gray-900 flex items-center gap-2">
                  <TrendingUp className="w-4 h-4 text-amber-500" />
                  <span>Inventaris & Stok Terendah</span>
                </h2>
                <span className="text-xs font-semibold text-gray-400">
                  Prioritas Restok
                </span>
              </div>

              <div className="divide-y divide-gray-50 overflow-x-auto">
                <table className="w-full text-left text-xs">
                  <thead>
                    <tr className="text-gray-400 font-bold border-b border-gray-100">
                      <th className="pb-3">Produk</th>
                      <th className="pb-3">Kategori</th>
                      <th className="pb-3 text-right">Harga</th>
                      <th className="pb-3 text-right">Sisa Stok</th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-50">
                    {lowStockProducts.map((p) => (
                      <tr key={p.id_produk} className="hover:bg-gray-50/60">
                        <td className="py-2.5 flex items-center gap-2.5">
                          {p.gambar ? (
                            <img
                              src={p.gambar}
                              alt={p.nama_produk}
                              className="w-8 h-8 rounded-lg object-contain bg-gray-50 p-0.5 border"
                            />
                          ) : (
                            <div className="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                              <Package className="w-4 h-4 text-gray-400" />
                            </div>
                          )}
                          <span className="font-bold text-gray-800 line-clamp-1">
                            {p.nama_produk}
                          </span>
                        </td>
                        <td className="py-2.5 text-gray-500">
                          {p.nama_kategori || 'Umum'}
                        </td>
                        <td className="py-2.5 text-right font-bold text-gray-800">
                          {formatRupiah(Number(p.harga))}
                        </td>
                        <td className="py-2.5 text-right">
                          <span
                            className={`px-2 py-0.5 rounded-md font-extrabold text-[11px] ${
                              p.stok === 0
                                ? 'bg-red-50 text-red-600'
                                : p.stok <= 5
                                ? 'bg-amber-50 text-amber-700'
                                : 'bg-emerald-50 text-emerald-700'
                            }`}
                          >
                            {p.stok} unit
                          </span>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}
