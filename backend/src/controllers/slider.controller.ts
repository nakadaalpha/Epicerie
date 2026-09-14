import { Request, Response } from 'express';
import { query } from '../config/db';
import { Slider } from '../types';

export async function getSliders(req: Request, res: Response) {
  try {
    const rows = await query<Slider>(
      'SELECT id_slider, judul, deskripsi, gambar, urutan, is_active FROM sliders WHERE is_active = true ORDER BY urutan ASC'
    );
    res.status(200).json({ success: true, data: rows });
  } catch (error: any) {
    console.error('Failed to get sliders:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}
