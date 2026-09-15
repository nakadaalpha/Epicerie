import { Request, Response } from 'express';
import { query } from '../config/db';
import { Produk } from '../types';
import { AuthenticatedRequest } from '../middlewares/auth';
import { logActivity } from '../services/audit.service';

export async function getProducts(req: Request, res: Response) {
  try {
    const { category_id, search, sort } = req.query;

    let sql = `
      SELECT 
        p.id_produk, 
        p.nama_produk, 
        p.deskripsi_produk,
        p.deskripsi_produk as deskripsi, 
        p.harga_produk,
        p.harga_produk as harga, 
        p.stok, 
        p.gambar, 
        p.id_kategori,
        k.nama_kategori,
        COALESCE(SUM(dt.jumlah), 0) as total_terjual
      FROM produk p
      LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
      LEFT JOIN detail_transaksi dt ON p.id_produk = dt.id_produk
      WHERE 1=1
    `;
    const params: any[] = [];

    if (category_id && Number(category_id) > 0) {
      params.push(Number(category_id));
      sql += ` AND p.id_kategori = $${params.length}`;
    }

    if (search && String(search).trim().length > 0) {
      params.push(`%${String(search).trim().toLowerCase()}%`);
      sql += ` AND (LOWER(p.nama_produk) LIKE $${params.length} OR LOWER(COALESCE(p.deskripsi_produk, '')) LIKE $${params.length})`;
    }

    sql += ` GROUP BY p.id_produk, k.nama_kategori `;

    if (sort === 'termurah') {
      sql += ` ORDER BY p.harga_produk ASC`;
    } else if (sort === 'termahal') {
      sql += ` ORDER BY p.harga_produk DESC`;
    } else if (sort === 'terlaris') {
      sql += ` ORDER BY total_terjual DESC, p.id_produk DESC`;
    } else if (sort === 'nama_asc') {
      sql += ` ORDER BY p.nama_produk ASC`;
    } else if (sort === 'nama_desc') {
      sql += ` ORDER BY p.nama_produk DESC`;
    } else if (sort === 'stok_sedikit') {
      sql += ` ORDER BY p.stok ASC`;
    } else if (sort === 'stok_banyak') {
      sql += ` ORDER BY p.stok DESC`;
    } else {
      sql += ` ORDER BY p.id_produk DESC`;
    }

    const rows = await query<any>(sql, params);
    const formatted = rows.map((r) => ({
      ...r,
      harga_produk: Number(r.harga_produk),
      harga: Number(r.harga_produk),
      stok: Number(r.stok),
      total_terjual: Number(r.total_terjual || 0),
    }));

    res.status(200).json({ success: true, data: formatted });
  } catch (error: any) {
    console.error('Failed to get products:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function getProductById(req: Request, res: Response) {
  try {
    const id = Number(req.params.id);
    if (!id || isNaN(id)) {
      return res.status(400).json({ success: false, error: 'ID Produk tidak valid.' });
    }

    const rows = await query<any>(
      `SELECT 
        p.id_produk, 
        p.nama_produk, 
        p.deskripsi_produk,
        p.deskripsi_produk as deskripsi, 
        p.harga_produk,
        p.harga_produk as harga, 
        p.stok, 
        p.gambar, 
        p.id_kategori,
        k.nama_kategori,
        COALESCE(SUM(dt.jumlah), 0) as total_terjual
      FROM produk p
      LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
      LEFT JOIN detail_transaksi dt ON p.id_produk = dt.id_produk
      WHERE p.id_produk = $1
      GROUP BY p.id_produk, k.nama_kategori
      LIMIT 1`,
      [id]
    );

    if (rows.length === 0) {
      return res.status(404).json({ success: false, error: 'Produk tidak ditemukan.' });
    }

    const product = rows[0];

    // Fetch product reviews
    const reviews = await query<any>(
      `SELECT 
        u.id_ulasan, u.rating, u.komentar, u.created_at,
        usr.nama as nama_user, usr.foto_profil
       FROM ulasan u
       JOIN "user" usr ON u.id_user = usr.id_user
       WHERE u.id_produk = $1
       ORDER BY u.created_at DESC`,
      [id]
    );

    // Compute rating statistics
    const totalUlasan = reviews.length;
    let sumRating = 0;
    for (const r of reviews) {
      sumRating += Number(r.rating || 0);
    }
    const avgRating = totalUlasan > 0 ? Number((sumRating / totalUlasan).toFixed(1)) : 0;

    res.status(200).json({
      success: true,
      data: {
        ...product,
        harga_produk: Number(product.harga_produk),
        harga: Number(product.harga_produk),
        stok: Number(product.stok),
        total_terjual: Number(product.total_terjual || 0),
        avg_rating: avgRating,
        total_ulasan: totalUlasan,
        reviews,
      },
    });
  } catch (error: any) {
    console.error('Failed to get product detail:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function createProduct(req: AuthenticatedRequest, res: Response) {
  try {
    const { id_kategori, nama_produk, harga_produk, stok, deskripsi_produk, gambar } = req.body;

    if (!id_kategori || !nama_produk || harga_produk === undefined || stok === undefined) {
      return res.status(400).json({ success: false, error: 'Semua kolom bertanda bintang wajib diisi.' });
    }

    const now = new Date().toISOString();
    const result = await query<any>(
      `INSERT INTO produk (
        id_kategori, nama_produk, harga_produk, stok, deskripsi_produk, gambar, created_at, updated_at
      ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8) RETURNING id_produk`,
      [id_kategori, nama_produk, harga_produk, stok, deskripsi_produk || null, gambar || null, now, now]
    );

    const newId = result[0]?.id_produk;
    await logActivity(req.user?.id_user, `Menambahkan produk baru "${nama_produk}" (ID: ${newId})`);

    res.status(201).json({
      success: true,
      message: 'Produk berhasil ditambahkan.',
      id_produk: newId,
    });
  } catch (error: any) {
    console.error('Failed to create product:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function updateProduct(req: AuthenticatedRequest, res: Response) {
  try {
    const id = Number(req.params.id);
    const { id_kategori, nama_produk, harga_produk, stok, deskripsi_produk, gambar } = req.body;

    const now = new Date().toISOString();
    await query(
      `UPDATE produk SET
        id_kategori = COALESCE($1, id_kategori),
        nama_produk = COALESCE($2, nama_produk),
        harga_produk = COALESCE($3, harga_produk),
        stok = COALESCE($4, stok),
        deskripsi_produk = COALESCE($5, deskripsi_produk),
        gambar = COALESCE($6, gambar),
        updated_at = $7
       WHERE id_produk = $8`,
      [id_kategori, nama_produk, harga_produk, stok, deskripsi_produk, gambar, now, id]
    );

    await logActivity(req.user?.id_user, `Memperbarui data produk ID ${id} (${nama_produk || 'update'})`);

    res.status(200).json({ success: true, message: 'Produk berhasil diperbarui.' });
  } catch (error: any) {
    console.error('Failed to update product:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function deleteProduct(req: AuthenticatedRequest, res: Response) {
  try {
    const id = Number(req.params.id);
    await query('DELETE FROM produk WHERE id_produk = $1', [id]);
    await logActivity(req.user?.id_user, `Menghapus produk (ID: ${id})`);
    res.status(200).json({ success: true, message: 'Produk berhasil dihapus.' });
  } catch (error: any) {
    console.error('Failed to delete product:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}
