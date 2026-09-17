import { Request, Response } from 'express';
import { query, withTransaction } from '../config/db';
import { ENV } from '../config/env';
import { AuthenticatedRequest } from '../middlewares/auth';
import { hasPermission } from '../config/permissions';
import { logActivity } from '../services/audit.service';

export async function getTransactions(req: AuthenticatedRequest, res: Response) {
  try {
    const { status, search, sort, limit = 100 } = req.query;

    let sql = `
      SELECT 
        t.*,
        u.nama as nama_pembeli,
        u.no_hp as no_hp_pembeli,
        COALESCE(t.ongkos_kirim, t.ongkir, 0) as ongkos_kirim,
        COUNT(dt.id_detail_transaksi) as total_items
      FROM transaksi t
      LEFT JOIN "user" u ON t.id_user_pembeli = u.id_user
      LEFT JOIN detail_transaksi dt ON t.id_transaksi = dt.id_transaksi
      WHERE 1=1
    `;
    const params: any[] = [];

    // RBAC: If caller does not have storewide 'orders:read_all' permission, filter strictly to their own orders
    if (!hasPermission(req.user?.role, 'orders:read_all')) {
      if (!req.user?.id_user) {
        return res.status(401).json({ success: false, error: 'Silakan login terlebih dahulu.' });
      }
      params.push(req.user.id_user);
      sql += ` AND t.id_user_pembeli = $${params.length}`;
    }

    if (status && String(status).trim() !== '') {
      params.push(String(status).trim());
      sql += ` AND LOWER(t.status) = LOWER($${params.length})`;
    }

    if (search && String(search).trim() !== '') {
      params.push(`%${String(search).trim().toLowerCase()}%`);
      sql += ` AND (LOWER(t.kode_transaksi) LIKE $${params.length} OR LOWER(COALESCE(u.nama, '')) LIKE $${params.length} OR LOWER(COALESCE(t.nama_pelanggan_hold, '')) LIKE $${params.length})`;
    }

    sql += ` GROUP BY t.id_transaksi, u.nama, u.no_hp `;

    if (sort === 'terlama') {
      sql += ` ORDER BY t.created_at ASC, t.id_transaksi ASC`;
    } else if (sort === 'terbesar') {
      sql += ` ORDER BY t.total_bayar DESC`;
    } else if (sort === 'terkecil') {
      sql += ` ORDER BY t.total_bayar ASC`;
    } else {
      sql += ` ORDER BY t.created_at DESC, t.id_transaksi DESC`;
    }

    params.push(Number(limit));
    sql += ` LIMIT $${params.length}`;

    const rows = await query<any>(sql, params);

    // Fetch items for all retrieved transactions
    const trxIds = rows.map((r) => r.id_transaksi);
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

    const formatted = rows.map((trx) => ({
      ...trx,
      total_bayar: Number(trx.total_bayar),
      ongkos_kirim: Number(trx.ongkos_kirim || 0),
      items: itemsMap[trx.id_transaksi] || [],
    }));

    res.status(200).json({ success: true, data: formatted });
  } catch (error: any) {
    console.error('Failed to get transactions:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function getTransactionById(req: AuthenticatedRequest, res: Response) {
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
      tipe_pengiriman = 'pickup',
      status: requestedStatus,
    } = req.body;

    if (!items || !Array.isArray(items) || items.length === 0) {
      return res.status(400).json({ success: false, error: 'Keranjang belanja kosong.' });
    }

    const userId = req.user?.id_user || null;
    let finalOngkir = Number(ongkir || 0);

    // Business Logic: Delivery rules & Membership Gold Free Shipping
    if (tipe_pengiriman === 'delivery' || id_alamat) {
      // Calculate subtotal from items
      const calculatedSubtotal = items.reduce(
        (sum: number, it: any) =>
          sum + Number(it.harga || it.harga_produk_saat_beli || 0) * Number(it.jumlah || 1),
        0
      );

      // Rule: Minimum belanja delivery Rp 30.000
      if (calculatedSubtotal < 30000) {
        return res.status(400).json({
          success: false,
          error: 'Belanja kurang dari batas minimum pengiriman (Rp 30.000).',
        });
      }

      // Check Gold Membership
      if (userId) {
        const userRows = await query<any>(
          'SELECT role, membership FROM "user" WHERE id_user = $1',
          [userId]
        );
        const membership = (userRows[0]?.membership || '').toLowerCase();
        if (membership === 'gold') {
          finalOngkir = 0; // Free shipping for Gold Member
        }
      }
    }

    const kodeTransaksi = `TRX-${Date.now()}`;
    const now = new Date().toISOString();
    const finalStatus =
      requestedStatus ||
      (metode_pembayaran.toLowerCase() === 'midtrans' ? 'pending' : 'selesai');

    // ACID Transaction Execution
    const result = await withTransaction(async (client) => {
      // 1. Check stock availability for all items with row locking
      for (const item of items) {
        const prodRes = await client.query(
          'SELECT id_produk, nama_produk, stok FROM produk WHERE id_produk = $1 FOR UPDATE',
          [item.id_produk]
        );
        if (prodRes.rows.length === 0) {
          throw new Error(`Produk dengan ID #${item.id_produk} tidak ditemukan.`);
        }
        const currentStock = prodRes.rows[0].stok;
        if (currentStock < item.jumlah) {
          throw new Error(
            `Stok produk "${prodRes.rows[0].nama_produk}" tidak mencukupi (Tersisa ${currentStock} unit).`
          );
        }
      }

      // 2. Insert into transaksi
      const trxResult = await client.query(
        `INSERT INTO transaksi (
          kode_transaksi, total_bayar, status, metode_pembayaran, 
          nama_pelanggan_hold, id_user_pembeli, id_alamat, ongkos_kirim, 
          tanggal_transaksi, created_at, updated_at
        ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)
        RETURNING id_transaksi, kode_transaksi`,
        [
          kodeTransaksi,
          total_bayar,
          finalStatus,
          metode_pembayaran,
          nama_pelanggan_hold || null,
          userId,
          id_alamat || null,
          finalOngkir,
          now,
          now,
          now,
        ]
      );

      const idTransaksi = trxResult.rows[0].id_transaksi;

      // 3. Insert items and decrement stock atomically
      for (const item of items) {
        await client.query(
          `INSERT INTO detail_transaksi (
            id_transaksi, id_produk, jumlah, harga_produk_saat_beli, created_at, updated_at
          ) VALUES ($1, $2, $3, $4, $5, $6)`,
          [
            idTransaksi,
            item.id_produk,
            item.jumlah,
            item.harga || item.harga_produk_saat_beli,
            now,
            now,
          ]
        );

        await client.query(
          `UPDATE produk 
           SET stok = stok - $1, updated_at = $2 
           WHERE id_produk = $3`,
          [item.jumlah, now, item.id_produk]
        );
      }

      return { idTransaksi, kodeTransaksi };
    });

    if (userId) {
      await logActivity(
        userId,
        `Membuat pesanan ${result.kodeTransaksi} total Rp ${Number(total_bayar).toLocaleString(
          'id-ID'
        )}`
      );
    }

    res.status(201).json({
      success: true,
      message: 'Transaksi berhasil disimpan secara aman.',
      data: {
        id_transaksi: result.idTransaksi,
        kode_transaksi: result.kodeTransaksi,
      },
    });
  } catch (error: any) {
    console.error('Failed to create transaction:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function createSnapToken(req: AuthenticatedRequest, res: Response) {
  try {
    const { total_bayar, order_id, customer_details } = req.body;

    if (!total_bayar || total_bayar <= 0) {
      return res.status(400).json({ success: false, error: 'Total bayar tidak valid.' });
    }

    const orderId = order_id || `MID-${Date.now()}-${Math.floor(Math.random() * 900 + 100)}`;
    const serverKey = ENV.MIDTRANS_SERVER_KEY;

    if (!serverKey) {
      return res.status(500).json({
        success: false,
        error: 'MIDTRANS_SERVER_KEY belum dikonfigurasi di server.',
      });
    }

    const isProduction = ENV.NODE_ENV === 'production';
    const midtransUrl = isProduction
      ? 'https://app.midtrans.com/snap/v1/transactions'
      : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

    const authString = Buffer.from(`${serverKey}:`).toString('base64');

    const snapPayload = {
      transaction_details: {
        order_id: orderId,
        gross_amount: Math.round(Number(total_bayar)),
      },
      customer_details: {
        first_name: customer_details?.nama || req.user?.nama || 'Pelanggan',
        email: customer_details?.email || (req.user as any)?.email || 'customer@epicerie.local',
        phone: customer_details?.no_hp || req.user?.no_hp || '',
      },
    };

    const midtransRes = await fetch(midtransUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        Accept: 'application/json',
        Authorization: `Basic ${authString}`,
      },
      body: JSON.stringify(snapPayload),
    });

    const data = (await midtransRes.json()) as any;

    if (!midtransRes.ok) {
      console.error('Midtrans Snap Error:', data);
      return res.status(midtransRes.status).json({
        success: false,
        error: data?.error_messages
          ? data.error_messages.join(', ')
          : 'Gagal membuat Midtrans Snap Token.',
      });
    }

    res.status(200).json({
      success: true,
      data: {
        token: data?.token,
        redirect_url: data?.redirect_url,
        order_id: orderId,
      },
    });
  } catch (error: any) {
    console.error('Failed to create snap token:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function handleMidtransNotification(req: Request, res: Response) {
  try {
    const { order_id, transaction_status, fraud_status } = req.body;

    if (!order_id) {
      return res.status(400).json({ success: false, error: 'order_id wajib disertakan.' });
    }

    let newStatus: string | null = null;

    if (transaction_status === 'capture') {
      if (fraud_status === 'accept') {
        newStatus = 'diproses';
      }
    } else if (transaction_status === 'settlement') {
      newStatus = 'diproses';
    } else if (
      transaction_status === 'cancel' ||
      transaction_status === 'deny' ||
      transaction_status === 'expire'
    ) {
      newStatus = 'batal';
    } else if (transaction_status === 'pending') {
      newStatus = 'pending';
    }

    if (newStatus) {
      const now = new Date().toISOString();
      await query(
        `UPDATE transaksi 
         SET status = $1, updated_at = $2 
         WHERE kode_transaksi = $3`,
        [newStatus, now, order_id]
      );
    }

    res.status(200).json({ success: true, message: 'Notifikasi Midtrans berhasil diproses.' });
  } catch (error: any) {
    console.error('Failed to handle Midtrans notification:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function updateTransactionStatus(req: AuthenticatedRequest, res: Response) {
  try {
    const id = Number(req.params.id);
    const { status } = req.body;

    const validStatuses = ['pending', 'diproses', 'dikemas', 'dikirim', 'selesai', 'batal'];
    if (!validStatuses.includes(status)) {
      return res.status(400).json({
        success: false,
        error: `Status tidak valid. Opsi: ${validStatuses.join(', ')}`,
      });
    }

    const now = new Date().toISOString();
    await query('UPDATE transaksi SET status = $1, updated_at = $2 WHERE id_transaksi = $3', [
      status,
      now,
      id,
    ]);

    await logActivity(req.user?.id_user, `Mengubah status pesanan #${id} menjadi "${status}"`);

    res.status(200).json({
      success: true,
      message: `Status transaksi berhasil diubah menjadi '${status}'.`,
    });
  } catch (error: any) {
    console.error('Failed to update transaction status:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function completeCustomerOrder(req: AuthenticatedRequest, res: Response) {
  try {
    const id = Number(req.params.id);
    const userId = req.user?.id_user;

    if (!id || isNaN(id)) {
      return res.status(400).json({ success: false, error: 'ID transaksi tidak valid.' });
    }

    const rows = await query<any>(
      'SELECT id_transaksi, id_user_pembeli, status FROM transaksi WHERE id_transaksi = $1 LIMIT 1',
      [id]
    );

    if (rows.length === 0) {
      return res.status(404).json({ success: false, error: 'Pesanan tidak ditemukan.' });
    }

    const trx = rows[0];
    // Must be buyer or staff
    if (trx.id_user_pembeli !== userId && !hasPermission(req.user?.role, 'orders:read_all')) {
      return res.status(403).json({ success: false, error: 'Anda tidak memiliki akses ke pesanan ini.' });
    }

    const now = new Date().toISOString();
    await query('UPDATE transaksi SET status = $1, updated_at = $2 WHERE id_transaksi = $3', [
      'selesai',
      now,
      id,
    ]);

    await logActivity(userId, `Pelanggan mengonfirmasi pesanan #${id} telah selesai diterima`);

    res.status(200).json({
      success: true,
      message: 'Terima kasih! Pesanan telah selesai.',
    });
  } catch (error: any) {
    console.error('Failed to complete order:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}
