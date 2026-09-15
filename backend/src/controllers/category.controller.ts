import { Request, Response } from 'express';
import { query } from '../config/db';
import { Kategori } from '../types';
import { AuthenticatedRequest } from '../middlewares/auth';
import { logActivity } from '../services/audit.service';

export async function getCategories(req: Request, res: Response) {
  try {
    const rows = await query<any>(
      `SELECT 
         k.id_kategori, 
         k.nama_kategori, 
         k.gambar,
         COUNT(p.id_produk)::int as produk_count
       FROM kategori k
       LEFT JOIN produk p ON k.id_kategori = p.id_kategori
       GROUP BY k.id_kategori, k.nama_kategori, k.gambar
       ORDER BY k.id_kategori ASC`
    );
    res.status(200).json({ success: true, data: rows });
  } catch (error: any) {
    console.error('Failed to get categories:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function createCategory(req: AuthenticatedRequest, res: Response) {
  try {
    const { nama_kategori, gambar } = req.body;
    if (!nama_kategori) {
      return res.status(400).json({ success: false, error: 'Nama kategori wajib diisi.' });
    }

    const now = new Date().toISOString();
    const rows = await query<any>(
      `INSERT INTO kategori (nama_kategori, gambar, created_at, updated_at)
       VALUES ($1, $2, $3, $4) RETURNING id_kategori`,
      [nama_kategori, gambar || null, now, now]
    );

    const newId = rows[0]?.id_kategori;
    await logActivity(req.user?.id_user, `Menambahkan kategori baru "${nama_kategori}" (ID: ${newId})`);

    res.status(201).json({
      success: true,
      message: 'Kategori berhasil ditambahkan.',
      id_kategori: newId,
    });
  } catch (error: any) {
    console.error('Failed to create category:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function updateCategory(req: AuthenticatedRequest, res: Response) {
  try {
    const id = Number(req.params.id);
    const { nama_kategori, gambar } = req.body;

    const now = new Date().toISOString();
    await query(
      `UPDATE kategori SET
        nama_kategori = COALESCE($1, nama_kategori),
        gambar = COALESCE($2, gambar),
        updated_at = $3
       WHERE id_kategori = $4`,
      [nama_kategori, gambar, now, id]
    );

    await logActivity(req.user?.id_user, `Memperbarui kategori ID ${id} ("${nama_kategori || 'update'}")`);

    res.status(200).json({ success: true, message: 'Kategori berhasil diperbarui.' });
  } catch (error: any) {
    console.error('Failed to update category:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function deleteCategory(req: AuthenticatedRequest, res: Response) {
  try {
    const id = Number(req.params.id);
    await query('DELETE FROM kategori WHERE id_kategori = $1', [id]);
    await logActivity(req.user?.id_user, `Menghapus kategori (ID: ${id})`);
    res.status(200).json({ success: true, message: 'Kategori berhasil dihapus.' });
  } catch (error: any) {
    console.error('Failed to delete category:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}
