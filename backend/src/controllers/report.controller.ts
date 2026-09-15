import { Request, Response } from 'express';
import { query } from '../config/db';
import { AuthenticatedRequest } from '../middlewares/auth';

const DAYS_ID = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
const MONTHS_ID = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

function formatDateIndo(date: Date): string {
  const dayName = DAYS_ID[date.getDay()];
  const dayNum = String(date.getDate()).padStart(2, '0');
  const monthName = MONTHS_ID[date.getMonth()];
  return `${dayName}, ${dayNum} ${monthName}`;
}

export async function getDashboardStats(req: AuthenticatedRequest, res: Response) {
  try {
    // 1. Quick Stats
    const [
      omzetRes,
      trxHariIniRes,
      totalProdukRes,
      totalUserRes,
      pendingCardRes,
    ] = await Promise.all([
      query<{ total: string }>(
        `SELECT COALESCE(SUM(total_bayar), 0) as total 
         FROM transaksi 
         WHERE created_at::date = CURRENT_DATE AND LOWER(status) = 'selesai'`
      ).catch(() => [{ total: '0' }]),
      query<{ count: string }>(
        `SELECT COUNT(*) as count 
         FROM transaksi 
         WHERE created_at::date = CURRENT_DATE`
      ).catch(() => [{ count: '0' }]),
      query<{ count: string }>(
        `SELECT COUNT(*) as count FROM produk`
      ).catch(() => [{ count: '0' }]),
      query<{ count: string }>(
        `SELECT COUNT(*) as count 
         FROM "user" 
         WHERE LOWER(role) NOT IN ('admin', 'pemilik')`
      ).catch(() => [{ count: '0' }]),
      query<{ count: string }>(
        `SELECT COUNT(*) as count 
         FROM "user" 
         WHERE LOWER(status_cetak_kartu) = 'pending'`
      ).catch(() => [{ count: '0' }]),
    ]);

    const omzetHariIni = Number(omzetRes[0]?.total || 0);
    const totalTransaksiHariIni = Number(trxHariIniRes[0]?.count || 0);
    const totalProduk = Number(totalProdukRes[0]?.count || 0);
    const totalUser = Number(totalUserRes[0]?.count || 0);
    const pendingCardCount = Number(pendingCardRes[0]?.count || 0);

    // 2. Stok Hampir Habis (stok <= 15, limit 5)
    const stokHampirHabis = await query<any>(
      `SELECT id_produk, nama_produk, stok, gambar, harga_produk as harga 
       FROM produk 
       WHERE stok <= 15 
       ORDER BY stok ASC, id_produk ASC 
       LIMIT 5`
    ).catch(() => []);

    // 3. Produk Terlaris (Top 5 by sum of quantity sold)
    const produkTerlarisRaw = await query<any>(
      `SELECT 
         p.id_produk, 
         p.nama_produk, 
         p.stok,
         p.gambar,
         COALESCE(SUM(dt.jumlah), 0)::int as total_terjual
       FROM produk p
       LEFT JOIN detail_transaksi dt ON p.id_produk = dt.id_produk
       GROUP BY p.id_produk, p.nama_produk, p.stok, p.gambar
       ORDER BY total_terjual DESC, p.stok ASC
       LIMIT 5`
    ).catch(() => []);

    const produkTerlaris = produkTerlarisRaw.map((p) => ({
      ...p,
      stok: Number(p.stok || 0),
      total_terjual: Number(p.total_terjual || 0),
    }));

    // 4. Data Chart 7 Hari Terakhir
    const chartLabels: string[] = [];
    const chartData: number[] = [];
    const now = new Date();

    for (let i = 6; i >= 0; i--) {
      const d = new Date(now);
      d.setDate(now.getDate() - i);
      const yyyy = d.getFullYear();
      const mm = String(d.getMonth() + 1).padStart(2, '0');
      const dd = String(d.getDate()).padStart(2, '0');
      const dateStr = `${yyyy}-${mm}-${dd}`;

      chartLabels.push(formatDateIndo(d));

      const dailyRevenue = await query<{ total: string }>(
        `SELECT COALESCE(SUM(total_bayar), 0) as total 
         FROM transaksi 
         WHERE created_at::date = $1 AND LOWER(status) = 'selesai'`,
        [dateStr]
      ).catch(() => [{ total: '0' }]);

      chartData.push(Number(dailyRevenue[0]?.total || 0));
    }

    // 5. Transaksi Terbaru (5 Terakhir lengkap dengan detail items)
    const trxRows = await query<any>(
      `SELECT 
         t.id_transaksi,
         t.kode_transaksi,
         t.total_bayar,
         t.status,
         t.created_at,
         t.metode_pembayaran,
         t.nama_pelanggan_hold,
         COALESCE(t.ongkos_kirim, t.ongkir, 0) as ongkos_kirim,
         u.nama as nama_pembeli,
         u.no_hp as no_hp_pembeli,
         a.penerima,
         a.no_hp_penerima,
         a.detail_alamat
       FROM transaksi t
       LEFT JOIN "user" u ON t.id_user_pembeli = u.id_user
       LEFT JOIN alamat_pengiriman a ON t.id_alamat = a.id_alamat
       ORDER BY t.created_at DESC
       LIMIT 5`
    ).catch(() => []);

    const trxIds = trxRows.map((t) => t.id_transaksi);
    let itemsMap: Record<number, any[]> = {};

    if (trxIds.length > 0) {
      const items = await query<any>(
        `SELECT 
           dt.id_transaksi,
           dt.id_detail_transaksi,
           dt.id_produk,
           dt.jumlah,
           dt.harga_produk_saat_beli,
           p.nama_produk,
           p.gambar
         FROM detail_transaksi dt
         JOIN produk p ON dt.id_produk = p.id_produk
         WHERE dt.id_transaksi = ANY($1)`,
        [trxIds]
      ).catch(() => []);

      for (const item of items) {
        if (!itemsMap[item.id_transaksi]) {
          itemsMap[item.id_transaksi] = [];
        }
        itemsMap[item.id_transaksi].push({
          id_detail_transaksi: item.id_detail_transaksi,
          id_produk: item.id_produk,
          nama_produk: item.nama_produk,
          gambar: item.gambar,
          jumlah: Number(item.jumlah),
          harga_produk_saat_beli: Number(item.harga_produk_saat_beli),
        });
      }
    }

    const transaksiTerbaru = trxRows.map((trx) => ({
      ...trx,
      total_bayar: Number(trx.total_bayar),
      ongkos_kirim: Number(trx.ongkos_kirim || 0),
      items: itemsMap[trx.id_transaksi] || [],
    }));

    res.status(200).json({
      success: true,
      data: {
        omzetHariIni,
        totalTransaksiHariIni,
        totalProduk,
        totalUser,
        pendingCardCount,
        stokHampirHabis,
        produkTerlaris,
        chartLabels,
        chartData,
        transaksiTerbaru,
        // Legacy backward compatibility aliases:
        totalSales: omzetHariIni,
        totalTrx: totalTransaksiHariIni,
        totalProducts: totalProduk,
        lowStockCount: stokHampirHabis.length,
        recentTransactions: transaksiTerbaru,
        lowStockProducts: stokHampirHabis,
      },
    });
  } catch (error: any) {
    console.error('Failed to get dashboard stats:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function getFinancialReport(req: AuthenticatedRequest, res: Response) {
  try {
    const range = (req.query.range as string) || 'hari_ini';
    let intervalClause = "created_at::date = CURRENT_DATE";
    let labelPeriode = 'Hari Ini';

    switch (range) {
      case '1_minggu':
        intervalClause = "created_at >= CURRENT_DATE - INTERVAL '7 days'";
        labelPeriode = '7 Hari Terakhir';
        break;
      case '1_bulan':
        intervalClause = "created_at >= CURRENT_DATE - INTERVAL '30 days'";
        labelPeriode = '1 Bulan Terakhir';
        break;
      case '6_bulan':
        intervalClause = "created_at >= CURRENT_DATE - INTERVAL '6 months'";
        labelPeriode = '6 Bulan Terakhir';
        break;
      case '1_tahun':
        intervalClause = "created_at >= CURRENT_DATE - INTERVAL '1 year'";
        labelPeriode = '1 Tahun Terakhir';
        break;
      case 'hari_ini':
      default:
        intervalClause = "created_at::date = CURRENT_DATE";
        labelPeriode = 'Hari Ini';
        break;
    }

    const summary = await query<any>(
      `SELECT 
         COALESCE(SUM(total_bayar), 0) as total_omzet,
         COUNT(id_transaksi) as total_transaksi
       FROM transaksi 
       WHERE ${intervalClause} AND LOWER(status) = 'selesai'`
    );

    const totalOmzet = Number(summary[0]?.total_omzet || 0);
    const totalTransaksi = Number(summary[0]?.total_transaksi || 0);

    // Grouping by date
    const dailyData = await query<any>(
      `SELECT 
         TO_CHAR(created_at, 'YYYY-MM-DD') as tanggal,
         COALESCE(SUM(total_bayar), 0) as total,
         COUNT(id_transaksi) as jumlah_transaksi
       FROM transaksi
       WHERE ${intervalClause} AND LOWER(status) = 'selesai'
       GROUP BY TO_CHAR(created_at, 'YYYY-MM-DD')
       ORDER BY tanggal ASC`
    );

    const labels = dailyData.map((d) => d.tanggal);
    const chartData = dailyData.map((d) => Number(d.total));

    res.status(200).json({
      success: true,
      data: {
        totalOmzet,
        totalTransaksi,
        labelPeriode,
        range,
        labels,
        chartData,
        dailyData: dailyData.map((d) => ({
          ...d,
          total: Number(d.total),
          jumlah_transaksi: Number(d.jumlah_transaksi),
        })),
      },
    });
  } catch (error: any) {
    console.error('Failed to get financial report:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function getCardQueue(req: AuthenticatedRequest, res: Response) {
  try {
    const rows = await query<any>(
      `SELECT 
         id_user, nama, username, email, no_hp, role, status_cetak_kartu, updated_at
       FROM "user"
       WHERE LOWER(status_cetak_kartu) = 'pending'
       ORDER BY updated_at DESC`
    );

    const formatted = rows.map((u) => ({
      ...u,
      membership: 'Gold', // or compute tier
    }));

    res.status(200).json({ success: true, data: formatted });
  } catch (error: any) {
    console.error('Failed to get card queue:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function completeCardPrint(req: AuthenticatedRequest, res: Response) {
  try {
    const id = Number(req.params.id);
    const now = new Date().toISOString();
    await query(
      `UPDATE "user" SET status_cetak_kartu = 'completed', updated_at = $1 WHERE id_user = $2`,
      [now, id]
    );

    res.status(200).json({ success: true, message: 'Status cetak kartu berhasil ditandai selesai.' });
  } catch (error: any) {
    console.error('Failed to complete card print:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}
