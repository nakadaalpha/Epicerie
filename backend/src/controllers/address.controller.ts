import { Response } from 'express';
import { query, withTransaction } from '../config/db';
import { AuthenticatedRequest } from '../middlewares/auth';
import { AlamatPengiriman } from '../types';

export async function getAddresses(req: AuthenticatedRequest, res: Response) {
  try {
    const userId = req.user?.id_user;
    if (!userId) {
      return res.status(401).json({ success: false, error: 'Silakan login terlebih dahulu.' });
    }

    const addresses = await query<any>(
      `SELECT 
         id_alamat, 
         id_user, 
         label, 
         penerima, 
         no_hp_penerima, 
         detail_alamat, 
         plus_code, 
         COALESCE(is_primary, is_utama, 0) as is_primary,
         COALESCE(is_primary, is_utama, 0) as is_utama,
         created_at, 
         updated_at
       FROM alamat_pengiriman
       WHERE id_user = $1
       ORDER BY COALESCE(is_primary, is_utama, 0) DESC, id_alamat DESC`,
      [userId]
    );

    res.status(200).json({
      success: true,
      data: addresses.map((a) => ({
        ...a,
        is_primary: Number(a.is_primary) === 1 ? 1 : 0,
        is_utama: Number(a.is_utama) === 1 ? 1 : 0,
      })),
    });
  } catch (error: any) {
    console.error('Failed to get addresses:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function createAddress(req: AuthenticatedRequest, res: Response) {
  try {
    const userId = req.user?.id_user;
    if (!userId) {
      return res.status(401).json({ success: false, error: 'Silakan login terlebih dahulu.' });
    }

    const { label, penerima, no_hp_penerima, detail_alamat, plus_code, is_primary } = req.body;

    if (!label || !penerima || !no_hp_penerima || !detail_alamat) {
      return res.status(400).json({
        success: false,
        error: 'Label, nama penerima, no HP, dan detail alamat wajib diisi.',
      });
    }

    const result = await withTransaction(async (client) => {
      // Check existing count
      const countRes = await client.query(
        'SELECT COUNT(*) as cnt FROM alamat_pengiriman WHERE id_user = $1',
        [userId]
      );
      const isFirst = parseInt(countRes.rows[0].cnt, 10) === 0;
      const willBePrimary = isFirst || Boolean(is_primary);

      if (willBePrimary) {
        await client.query(
          'UPDATE alamat_pengiriman SET is_primary = 0 WHERE id_user = $1',
          [userId]
        ).catch(() => {
          return client.query('UPDATE alamat_pengiriman SET is_utama = false WHERE id_user = $1', [userId]);
        });
      }

      const now = new Date().toISOString();
      let insertRes;
      try {
        insertRes = await client.query(
          `INSERT INTO alamat_pengiriman (
             id_user, label, penerima, no_hp_penerima, detail_alamat, plus_code, is_primary, created_at, updated_at
           ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9)
           RETURNING *`,
          [
            userId,
            label.trim(),
            penerima.trim(),
            no_hp_penerima.trim(),
            detail_alamat.trim(),
            plus_code || null,
            willBePrimary ? 1 : 0,
            now,
            now,
          ]
        );
      } catch (colErr: any) {
        // Fallback for schemas with is_utama
        insertRes = await client.query(
          `INSERT INTO alamat_pengiriman (
             id_user, label, penerima, no_hp_penerima, detail_alamat, is_utama, created_at, updated_at
           ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8)
           RETURNING *`,
          [
            userId,
            label.trim(),
            penerima.trim(),
            no_hp_penerima.trim(),
            detail_alamat.trim(),
            willBePrimary,
            now,
            now,
          ]
        );
      }

      return insertRes.rows[0];
    });

    res.status(201).json({
      success: true,
      message: 'Alamat pengiriman berhasil ditambahkan.',
      data: result,
    });
  } catch (error: any) {
    console.error('Failed to create address:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function updateAddress(req: AuthenticatedRequest, res: Response) {
  try {
    const userId = req.user?.id_user;
    const addressId = Number(req.params.id);

    if (!userId) {
      return res.status(401).json({ success: false, error: 'Silakan login terlebih dahulu.' });
    }

    const { label, penerima, no_hp_penerima, detail_alamat, plus_code, is_primary } = req.body;

    const result = await withTransaction(async (client) => {
      // Check ownership
      const checkRes = await client.query(
        'SELECT id_alamat FROM alamat_pengiriman WHERE id_alamat = $1 AND id_user = $2',
        [addressId, userId]
      );
      if (checkRes.rows.length === 0) {
        throw new Error('Alamat tidak ditemukan atau bukan milik Anda.');
      }

      if (is_primary) {
        await client.query(
          'UPDATE alamat_pengiriman SET is_primary = 0 WHERE id_user = $1',
          [userId]
        ).catch(() => {
          return client.query('UPDATE alamat_pengiriman SET is_utama = false WHERE id_user = $1', [userId]);
        });
      }

      const now = new Date().toISOString();
      let updateRes;
      try {
        updateRes = await client.query(
          `UPDATE alamat_pengiriman
           SET label = COALESCE($1, label),
               penerima = COALESCE($2, penerima),
               no_hp_penerima = COALESCE($3, no_hp_penerima),
               detail_alamat = COALESCE($4, detail_alamat),
               plus_code = COALESCE($5, plus_code),
               is_primary = CASE WHEN $6 IS NOT NULL THEN $6 ELSE is_primary END,
               updated_at = $7
           WHERE id_alamat = $8 AND id_user = $9
           RETURNING *`,
          [
            label?.trim(),
            penerima?.trim(),
            no_hp_penerima?.trim(),
            detail_alamat?.trim(),
            plus_code || null,
            is_primary !== undefined ? (is_primary ? 1 : 0) : null,
            now,
            addressId,
            userId,
          ]
        );
      } catch (colErr: any) {
        updateRes = await client.query(
          `UPDATE alamat_pengiriman
           SET label = COALESCE($1, label),
               penerima = COALESCE($2, penerima),
               no_hp_penerima = COALESCE($3, no_hp_penerima),
               detail_alamat = COALESCE($4, detail_alamat),
               is_utama = CASE WHEN $5 IS NOT NULL THEN $5 ELSE is_utama END,
               updated_at = $6
           WHERE id_alamat = $7 AND id_user = $8
           RETURNING *`,
          [
            label?.trim(),
            penerima?.trim(),
            no_hp_penerima?.trim(),
            detail_alamat?.trim(),
            is_primary !== undefined ? Boolean(is_primary) : null,
            now,
            addressId,
            userId,
          ]
        );
      }

      return updateRes.rows[0];
    });

    res.status(200).json({
      success: true,
      message: 'Alamat pengiriman berhasil diperbarui.',
      data: result,
    });
  } catch (error: any) {
    console.error('Failed to update address:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function deleteAddress(req: AuthenticatedRequest, res: Response) {
  try {
    const userId = req.user?.id_user;
    const addressId = Number(req.params.id);

    if (!userId) {
      return res.status(401).json({ success: false, error: 'Silakan login terlebih dahulu.' });
    }

    const deleteRes = await query(
      'DELETE FROM alamat_pengiriman WHERE id_alamat = $1 AND id_user = $2 RETURNING id_alamat',
      [addressId, userId]
    );

    if (deleteRes.length === 0) {
      return res.status(404).json({ success: false, error: 'Alamat tidak ditemukan.' });
    }

    res.status(200).json({ success: true, message: 'Alamat berhasil dihapus.' });
  } catch (error: any) {
    console.error('Failed to delete address:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function setPrimaryAddress(req: AuthenticatedRequest, res: Response) {
  try {
    const userId = req.user?.id_user;
    const addressId = Number(req.params.id);

    if (!userId) {
      return res.status(401).json({ success: false, error: 'Silakan login terlebih dahulu.' });
    }

    await withTransaction(async (client) => {
      // Check ownership
      const checkRes = await client.query(
        'SELECT id_alamat FROM alamat_pengiriman WHERE id_alamat = $1 AND id_user = $2',
        [addressId, userId]
      );
      if (checkRes.rows.length === 0) {
        throw new Error('Alamat tidak ditemukan atau bukan milik Anda.');
      }

      await client.query(
        'UPDATE alamat_pengiriman SET is_primary = 0 WHERE id_user = $1',
        [userId]
      ).catch(() => {
        return client.query('UPDATE alamat_pengiriman SET is_utama = false WHERE id_user = $1', [userId]);
      });

      await client.query(
        'UPDATE alamat_pengiriman SET is_primary = 1 WHERE id_alamat = $1 AND id_user = $2',
        [addressId, userId]
      ).catch(() => {
        return client.query('UPDATE alamat_pengiriman SET is_utama = true WHERE id_alamat = $1 AND id_user = $2', [addressId, userId]);
      });
    });

    res.status(200).json({ success: true, message: 'Alamat utama berhasil diperbarui.' });
  } catch (error: any) {
    console.error('Failed to set primary address:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}
