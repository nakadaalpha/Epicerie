import { Request, Response } from 'express';
import { query } from '../config/db';
import { Kategori } from '../types';

export async function getCategories(req: Request, res: Response) {
  try {
    const rows = await query<Kategori>(
      'SELECT id_kategori, nama_kategori, gambar FROM kategori ORDER BY id_kategori ASC'
    );
    res.status(200).json({ success: true, data: rows });
  } catch (error: any) {
    console.error('Failed to get categories:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}
