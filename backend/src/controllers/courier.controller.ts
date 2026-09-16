import { Response } from 'express';
import { query } from '../config/db';
import { AuthenticatedRequest } from '../middlewares/auth';
import { logActivity } from '../services/audit.service';

export async function getCourierTasks(req: AuthenticatedRequest, res: Response) {
  try {
    const { status } = req.query;

    let sql = `
      SELECT 
        t.*,
        u.nama as nama_pembeli,
        u.no_hp as no_hp_pembeli,
        a.label as label_alamat,
        a.penerima as nama_penerima,
        a.no_hp_penerima,
        a.detail_alamat,
        COALESCE(t.ongkos_kirim, t.ongkir, 0) as ongkos_kirim,
        COUNT(dt.id_detail_transaksi) as total_items
      FROM transaksi t
      LEFT JOIN "user" u ON t.id_user_pembeli = u.id_user
      LEFT JOIN alamat_pengiriman a ON t.id_alamat = a.id_alamat
      LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
      WHERE t.status IN ('diproses', 'dikemas', 'dikirim', 'selesai')
    `;
    const params: any[] = [];

    if (status) {
      params.push(String(status).toLowerCase());
      sql += ` AND LOWER(t.status) = $${params.length}`;
    }

    sql += ` GROUP BY t.id_transaksi, u.nama, u.no_hp, a.label, a.penerima, a.no_hp_penerima, a.detail_alamat
             ORDER BY t.created_at DESC LIMIT 50`;

    const tasks = await query<any>(sql, params);

    // Fetch items
    const trxIds = tasks.map((t) => t.id_transaksi);
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

    const formatted = tasks.map((t) => ({
      ...t,
      items: itemsMap[t.id_transaksi] || [],
    }));

    res.status(200).json({ success: true, data: formatted });
  } catch (error: any) {
    console.error('Failed to get courier tasks:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function startDelivery(req: AuthenticatedRequest, res: Response) {
  try {
    const id = Number(req.params.id);
    const courierId = req.user?.id_user;
    const now = new Date().toISOString();

    const result = await query<any>(
      `UPDATE transaksi
       SET status = 'dikirim', id_karyawan = COALESCE(id_karyawan, $1), updated_at = $2
       WHERE id_transaksi = $3
       RETURNING id_transaksi, kode_transaksi, status`,
      [courierId || null, now, id]
    );

    if (result.length === 0) {
      return res.status(404).json({ success: false, error: 'Transaksi tidak ditemukan.' });
    }

    await logActivity(courierId, `Memulai pengiriman pesanan #${id} (${result[0].kode_transaksi})`);

    res.status(200).json({
      success: true,
      message: 'Status pesanan diubah menjadi dikirim. GPS Pengantaran aktif!',
      data: result[0],
    });
  } catch (error: any) {
    console.error('Failed to start delivery:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function completeDelivery(req: AuthenticatedRequest, res: Response) {
  try {
    const id = Number(req.params.id);
    const courierId = req.user?.id_user;
    const now = new Date().toISOString();

    const result = await query<any>(
      `UPDATE transaksi
       SET status = 'selesai', id_karyawan = COALESCE(id_karyawan, $1), updated_at = $2
       WHERE id_transaksi = $3
       RETURNING id_transaksi, kode_transaksi, status`,
      [courierId || null, now, id]
    );

    if (result.length === 0) {
      return res.status(404).json({ success: false, error: 'Transaksi tidak ditemukan.' });
    }

    await logActivity(courierId, `Menyelesaikan pengiriman pesanan #${id} (${result[0].kode_transaksi})`);

    res.status(200).json({
      success: true,
      message: 'Pengiriman berhasil diselesaikan!',
      data: result[0],
    });
  } catch (error: any) {
    console.error('Failed to complete delivery:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function updateLocation(req: AuthenticatedRequest, res: Response) {
  try {
    const { id_transaksi, lat, long } = req.body;

    if (!id_transaksi || lat === undefined || long === undefined) {
      return res.status(400).json({
        success: false,
        error: 'id_transaksi, lat, dan long wajib dikirim.',
      });
    }

    const now = new Date().toISOString();
    await query(
      `UPDATE transaksi
       SET kurir_lat = $1, kurir_long = $2, updated_at = $3
       WHERE id_transaksi = $4`,
      [Number(lat), Number(long), now, Number(id_transaksi)]
    );

    res.status(200).json({
      success: true,
      message: 'Lokasi kurir berhasil diperbarui.',
      data: { id_transaksi, lat, long },
    });
  } catch (error: any) {
    console.error('Failed to update courier location:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}
