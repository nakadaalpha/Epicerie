import { Request, Response } from 'express';
import { query } from '../config/db';
import { Slider } from '../types';
import { AuthenticatedRequest } from '../middlewares/auth';
import { logActivity } from '../services/audit.service';

export async function getSliders(req: Request, res: Response) {
  try {
    const { all } = req.query;
    const whereClause = all === 'true' || all === '1' ? '' : 'WHERE is_active = true';
    const rows = await query<Slider>(
      `SELECT id_slider, judul, deskripsi, gambar, urutan, is_active FROM sliders ${whereClause} ORDER BY urutan ASC`
    );
    res.status(200).json({ success: true, data: rows });
  } catch (error: any) {
    console.error('Failed to get sliders:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function createSlider(req: AuthenticatedRequest, res: Response) {
  try {
    const { judul, deskripsi, gambar, urutan = 0, is_active = true } = req.body;
    if (!judul || !gambar) {
      return res.status(400).json({ success: false, error: 'Judul dan gambar slider wajib diisi.' });
    }

    const now = new Date().toISOString();
    const rows = await query<any>(
      `INSERT INTO sliders (judul, deskripsi, gambar, urutan, is_active, created_at, updated_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7) RETURNING id_slider`,
      [judul, deskripsi || null, gambar, urutan, is_active, now, now]
    );

    const newId = rows[0]?.id_slider;
    await logActivity(req.user?.id_user, `Menambahkan banner slider "${judul}" (ID: ${newId})`);

    res.status(201).json({
      success: true,
      message: 'Slider berhasil ditambahkan.',
      id_slider: newId,
    });
  } catch (error: any) {
    console.error('Failed to create slider:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function updateSlider(req: AuthenticatedRequest, res: Response) {
  try {
    const id = Number(req.params.id);
    const { judul, deskripsi, gambar, urutan, is_active } = req.body;

    const now = new Date().toISOString();
    await query(
      `UPDATE sliders SET
        judul = COALESCE($1, judul),
        deskripsi = COALESCE($2, deskripsi),
        gambar = COALESCE($3, gambar),
        urutan = COALESCE($4, urutan),
        is_active = COALESCE($5, is_active),
        updated_at = $6
       WHERE id_slider = $7`,
      [judul, deskripsi, gambar, urutan, is_active, now, id]
    );

    await logActivity(req.user?.id_user, `Memperbarui slider ID ${id}`);

    res.status(200).json({ success: true, message: 'Slider berhasil diperbarui.' });
  } catch (error: any) {
    console.error('Failed to update slider:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function deleteSlider(req: AuthenticatedRequest, res: Response) {
  try {
    const id = Number(req.params.id);
    await query('DELETE FROM sliders WHERE id_slider = $1', [id]);
    await logActivity(req.user?.id_user, `Menghapus slider (ID: ${id})`);
    res.status(200).json({ success: true, message: 'Slider berhasil dihapus.' });
  } catch (error: any) {
    console.error('Failed to delete slider:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}
