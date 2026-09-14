import { Request, Response } from 'express';
import { query } from '../config/db';
import { AuthenticatedRequest } from '../middlewares/auth';

export async function createReview(req: AuthenticatedRequest, res: Response) {
  try {
    const userId = req.user?.id_user;
    if (!userId) {
      return res.status(401).json({ success: false, error: 'Silakan login terlebih dahulu.' });
    }

    const { id_produk, rating, komentar } = req.body;
    const numRating = Number(rating);

    if (!id_produk || !numRating || !komentar) {
      return res.status(400).json({ success: false, error: 'Produk, rating bintang, dan komentar ulasan wajib diisi.' });
    }

    if (numRating < 1 || numRating > 5) {
      return res.status(400).json({ success: false, error: 'Rating bintang harus antara 1 sampai 5.' });
    }

    // 1. Check if user has bought this product in a completed transaction
    const purchaseCheck = await query<any>(
      `SELECT dt.id_detail_transaksi 
       FROM detail_transaksi dt
       JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
       WHERE t.id_user_pembeli = $1 
         AND dt.id_produk = $2 
         AND t.status = 'selesai'
       LIMIT 1`,
      [userId, id_produk]
    );

    if (purchaseCheck.length === 0) {
      return res.status(403).json({
        success: false,
        error: 'Anda hanya dapat mengulas produk yang telah Anda beli dengan status pesanan selesai.',
      });
    }

    // 2. Check if user already reviewed this product
    const existingReview = await query<any>(
      'SELECT id_ulasan FROM ulasan WHERE id_user = $1 AND id_produk = $2 LIMIT 1',
      [userId, id_produk]
    );

    if (existingReview.length > 0) {
      return res.status(400).json({
        success: false,
        error: 'Anda sudah pernah memberikan ulasan untuk produk ini.',
      });
    }

    // 3. Insert review
    const now = new Date().toISOString();
    const result = await query<any>(
      `INSERT INTO ulasan (id_user, id_produk, rating, komentar, created_at, updated_at)
       VALUES ($1, $2, $3, $4, $5, $6)
       RETURNING id_ulasan`,
      [userId, id_produk, numRating, String(komentar).trim(), now, now]
    );

    res.status(201).json({
      success: true,
      message: 'Terima kasih! Ulasan Anda berhasil diterbitkan.',
      id_ulasan: result[0]?.id_ulasan,
    });
  } catch (error: any) {
    console.error('Failed to create review:', error);
    res.status(500).json({ success: false, error: error.message || 'Gagal menyimpan ulasan.' });
  }
}

export async function getProductReviews(req: Request, res: Response) {
  try {
    const id = Number(req.params.id);
    if (!id || isNaN(id)) {
      return res.status(400).json({ success: false, error: 'ID Produk tidak valid.' });
    }

    // Fetch reviews list
    const reviews = await query<any>(
      `SELECT 
        u.id_ulasan, 
        u.rating, 
        u.komentar, 
        u.created_at,
        usr.id_user,
        usr.nama as nama_user, 
        usr.foto_profil
       FROM ulasan u
       JOIN "user" usr ON u.id_user = usr.id_user
       WHERE u.id_produk = $1
       ORDER BY u.created_at DESC`,
      [id]
    );

    // Compute aggregated rating stats
    const total = reviews.length;
    let sum = 0;
    const distribution: Record<number, number> = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };

    for (const r of reviews) {
      sum += r.rating;
      distribution[r.rating] = (distribution[r.rating] || 0) + 1;
    }

    const average = total > 0 ? Number((sum / total).toFixed(1)) : 0;

    res.status(200).json({
      success: true,
      data: {
        total,
        average,
        distribution,
        reviews,
      },
    });
  } catch (error: any) {
    console.error('Failed to get product reviews:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function getPendingReviews(req: AuthenticatedRequest, res: Response) {
  try {
    const userId = req.user?.id_user;
    if (!userId) {
      return res.status(401).json({ success: false, error: 'Silakan login terlebih dahulu.' });
    }

    // Find distinct products purchased by user in completed orders that don't have a review yet
    const rows = await query<any>(
      `SELECT DISTINCT ON (p.id_produk)
        p.id_produk,
        p.nama_produk,
        p.harga_produk,
        p.gambar,
        k.nama_kategori,
        t.tanggal_transaksi as tgl_beli,
        t.id_transaksi
       FROM detail_transaksi dt
       JOIN transaksi t ON dt.id_transaksi = t.id_transaksi
       JOIN produk p ON dt.id_produk = p.id_produk
       LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
       LEFT JOIN ulasan u ON u.id_user = t.id_user_pembeli AND u.id_produk = p.id_produk
       WHERE t.id_user_pembeli = $1 
         AND t.status = 'selesai'
         AND u.id_ulasan IS NULL
       ORDER BY p.id_produk, t.tanggal_transaksi DESC`,
      [userId]
    );

    res.status(200).json({ success: true, data: rows });
  } catch (error: any) {
    console.error('Failed to get pending reviews:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function getUserReviews(req: AuthenticatedRequest, res: Response) {
  try {
    const userId = req.user?.id_user;
    if (!userId) {
      return res.status(401).json({ success: false, error: 'Silakan login terlebih dahulu.' });
    }

    const rows = await query<any>(
      `SELECT 
        u.id_ulasan,
        u.rating,
        u.komentar,
        u.created_at,
        p.id_produk,
        p.nama_produk,
        p.gambar,
        p.harga_produk,
        k.nama_kategori
       FROM ulasan u
       JOIN produk p ON u.id_produk = p.id_produk
       LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
       WHERE u.id_user = $1
       ORDER BY u.created_at DESC`,
      [userId]
    );

    res.status(200).json({ success: true, data: rows });
  } catch (error: any) {
    console.error('Failed to get user review history:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}
