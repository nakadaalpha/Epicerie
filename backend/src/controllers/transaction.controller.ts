import { Request, Response } from 'express';
import { query } from '../config/db';
import { AuthenticatedRequest } from '../middlewares/auth';

export async function getTransactions(req: AuthenticatedRequest, res: Response) {
  try {
    const { status, limit = 20 } = req.query;

    let sql = `
      SELECT 
        t.*,
        u.nama as nama_pembeli,
        u.no_hp as no_hp_pembeli,
        COUNT(dt.id_detail_transaksi) as total_items
      FROM transaksi t
      LEFT JOIN "user" u ON t.id_user_pembeli = u.id_user
      LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
      WHERE 1=1
    `;
    const params: any[] = [];

    // If customer, filter to only their own orders
    if (req.user && req.user.role === 'pelanggan') {
      params.push(req.user.id_user);
      sql += ` AND t.id_user_pembeli = $${params.length}`;
    }

    if (status) {
      params.push(status);
      sql += ` AND t.status = $${params.length}`;
    }

    sql += ` GROUP BY t.id_transaksi, u.nama, u.no_hp ORDER BY t.id_transaksi DESC LIMIT $${params.length + 1}`;
    params.push(Number(limit));

    const rows = await query<any>(sql, params);
    res.status(200).json({ success: true, data: rows });
  } catch (error: any) {
    console.error('Failed to get transactions:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function getTransactionById(req: Request, res: Response) {
  try {
    const id = Number(req.params.id);
    const trxRows = await query<any>(
      `SELECT t.*, u.nama as nama_pembeli, u.no_hp as no_hp_pembeli
       FROM transaksi t
       LEFT JOIN "user" u ON t.id_user_pembeli = u.id_user
       WHERE t.id_transaksi = $1
       LIMIT 1`,
      [id]
    );

    if (trxRows.length === 0) {
      return res.status(404).json({ success: false, error: 'Transaksi tidak ditemukan.' });
    }

    const transaction = trxRows[0];

    const items = await query<any>(
      `SELECT 
        dt.id_detail_transaksi,
        dt.id_produk,
        dt.jumlah,
        dt.harga_produk_saat_beli,
        p.nama_produk,
        p.gambar
       FROM detail_transaksi dt
       JOIN produk p ON dt.id_produk = p.id_produk
       WHERE dt.id_transaksi = $1`,
      [id]
    );

    res.status(200).json({
      success: true,
      data: {
        ...transaction,
        items,
      },
    });
  } catch (error: any) {
    console.error('Failed to get transaction detail:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function createTransaction(req: AuthenticatedRequest, res: Response) {
  try {
    const {
      items,
      total_bayar,
      metode_pembayaran = 'Tunai',
      nama_pelanggan_hold,
      id_alamat,
      ongkir = 0,
    } = req.body;

    if (!items || !Array.isArray(items) || items.length === 0) {
      return res.status(400).json({ success: false, error: 'Keranjang belanja kosong.' });
    }

    const userId = req.user?.id_user || null;
    const kodeTransaksi = `TRX-${Date.now()}`;
    const now = new Date().toISOString();

    // 1. Insert into transaksi
    const trxResult = await query<any>(
      `INSERT INTO transaksi (
        kode_transaksi, total_bayar, status, metode_pembayaran, 
        nama_pelanggan_hold, id_user_pembeli, id_alamat, ongkos_kirim, 
        tanggal_transaksi, created_at, updated_at
      ) VALUES ($1, $2, 'selesai', $3, $4, $5, $6, $7, $8, $9, $10)
      RETURNING id_transaksi`,
      [
        kodeTransaksi,
        total_bayar,
        metode_pembayaran,
        nama_pelanggan_hold || null,
        userId,
        id_alamat || null,
        ongkir,
        now,
        now,
        now,
      ]
    );

    const idTransaksi = trxResult[0].id_transaksi;

    // 2. Insert items and decrement stock
    for (const item of items) {
      await query(
        `INSERT INTO detail_transaksi (
          id_transaksi, id_produk, jumlah, harga_produk_saat_beli, created_at, updated_at
        ) VALUES ($1, $2, $3, $4, $5, $6)`,
        [idTransaksi, item.id_produk, item.jumlah, item.harga, now, now]
      );

      await query(
        `UPDATE produk 
         SET stok = GREATEST(0, stok - $1), updated_at = $2 
         WHERE id_produk = $3`,
        [item.jumlah, now, item.id_produk]
      );
    }

    res.status(201).json({
      success: true,
      message: 'Transaksi berhasil disimpan.',
      data: {
        id_transaksi: idTransaksi,
        kode_transaksi: kodeTransaksi,
      },
    });
  } catch (error: any) {
    console.error('Failed to create transaction:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function updateTransactionStatus(req: Request, res: Response) {
  try {
    const id = Number(req.params.id);
    const { status } = req.body;

    const validStatuses = ['pending', 'diproses', 'dikemas', 'dikirim', 'selesai', 'batal'];
    if (!validStatuses.includes(status)) {
      return res.status(400).json({ success: false, error: `Status tidak valid. Opsi: ${validStatuses.join(', ')}` });
    }

    const now = new Date().toISOString();
    await query(
      'UPDATE transaksi SET status = $1, updated_at = $2 WHERE id_transaksi = $3',
      [status, now, id]
    );

    res.status(200).json({ success: true, message: `Status transaksi berhasil diubah menjadi '${status}'.` });
  } catch (error: any) {
    console.error('Failed to update transaction status:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}
