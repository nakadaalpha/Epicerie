import { Response } from 'express';
import bcrypt from 'bcryptjs';
import { query } from '../config/db';
import { AuthenticatedRequest } from '../middlewares/auth';
import { logActivity } from '../services/audit.service';

export async function getEmployees(req: AuthenticatedRequest, res: Response) {
  try {
    const employees = await query<any>(
      `SELECT 
         id_user, 
         nama, 
         username, 
         role, 
         email, 
         no_hp, 
         foto_profil, 
         created_at, 
         updated_at
       FROM "user"
       WHERE LOWER(role) IN ('karyawan', 'kasir', 'kurir', 'gudang', 'manajer', 'admin')
       ORDER BY id_user DESC`
    );

    res.status(200).json({ success: true, data: employees });
  } catch (error: any) {
    console.error('Failed to get employees:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function createEmployee(req: AuthenticatedRequest, res: Response) {
  try {
    const { nama, username, password, role = 'karyawan', no_hp } = req.body;

    if (!nama || !username || !password) {
      return res.status(400).json({
        success: false,
        error: 'Nama, username, dan password wajib diisi.',
      });
    }

    if (password.length < 4) {
      return res.status(400).json({
        success: false,
        error: 'Password minimal 4 karakter.',
      });
    }

    // Check unique username
    const existing = await query(
      'SELECT id_user FROM "user" WHERE LOWER(username) = LOWER($1)',
      [username.trim()]
    );
    if (existing.length > 0) {
      return res.status(400).json({ success: false, error: 'Username sudah digunakan.' });
    }

    const hashedPassword = await bcrypt.hash(password, 10);
    const now = new Date().toISOString();

    const insertRes = await query<any>(
      `INSERT INTO "user" (nama, username, password, role, no_hp, created_at, updated_at)
       VALUES ($1, $2, $3, $4, $5, $6, $7)
       RETURNING id_user, nama, username, role, no_hp, created_at`,
      [
        nama.trim(),
        username.trim(),
        hashedPassword,
        role.toLowerCase().trim(),
        no_hp?.trim() || null,
        now,
        now,
      ]
    );

    await logActivity(
      req.user?.id_user,
      `Menambahkan karyawan baru: ${nama.trim()} (${username.trim()} - ${role})`
    );

    res.status(201).json({
      success: true,
      message: 'Karyawan berhasil ditambahkan.',
      data: insertRes[0],
    });
  } catch (error: any) {
    console.error('Failed to create employee:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function updateEmployee(req: AuthenticatedRequest, res: Response) {
  try {
    const id = Number(req.params.id);
    const { nama, username, password, role, no_hp } = req.body;

    const existingUser = await query<any>(
      'SELECT id_user, role FROM "user" WHERE id_user = $1',
      [id]
    );
    if (existingUser.length === 0) {
      return res.status(404).json({ success: false, error: 'Karyawan tidak ditemukan.' });
    }

    if (username) {
      const duplicate = await query(
        'SELECT id_user FROM "user" WHERE LOWER(username) = LOWER($1) AND id_user != $2',
        [username.trim(), id]
      );
      if (duplicate.length > 0) {
        return res.status(400).json({ success: false, error: 'Username sudah digunakan oleh akun lain.' });
      }
    }

    let hashedPassword = null;
    if (password && password.trim() !== '') {
      if (password.length < 4) {
        return res.status(400).json({ success: false, error: 'Password baru minimal 4 karakter.' });
      }
      hashedPassword = await bcrypt.hash(password, 10);
    }

    const now = new Date().toISOString();
    const updateRes = await query<any>(
      `UPDATE "user"
       SET nama = COALESCE($1, nama),
           username = COALESCE($2, username),
           password = COALESCE($3, password),
           role = COALESCE($4, role),
           no_hp = COALESCE($5, no_hp),
           updated_at = $6
       WHERE id_user = $7
       RETURNING id_user, nama, username, role, no_hp, updated_at`,
      [
        nama?.trim() || null,
        username?.trim() || null,
        hashedPassword,
        role?.toLowerCase().trim() || null,
        no_hp?.trim() || null,
        now,
        id,
      ]
    );

    await logActivity(
      req.user?.id_user,
      `Memperbarui data karyawan ID #${id} (${updateRes[0].nama})`
    );

    res.status(200).json({
      success: true,
      message: 'Data karyawan berhasil diperbarui.',
      data: updateRes[0],
    });
  } catch (error: any) {
    console.error('Failed to update employee:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function deleteEmployee(req: AuthenticatedRequest, res: Response) {
  try {
    const id = Number(req.params.id);

    if (id === req.user?.id_user) {
      return res.status(400).json({ success: false, error: 'Anda tidak dapat menghapus akun Anda sendiri.' });
    }

    const check = await query<any>('SELECT nama, role FROM "user" WHERE id_user = $1', [id]);
    if (check.length === 0) {
      return res.status(404).json({ success: false, error: 'Karyawan tidak ditemukan.' });
    }

    if (check[0].role?.toLowerCase() === 'pemilik') {
      return res.status(403).json({ success: false, error: 'Akun Pemilik utama tidak dapat dihapus.' });
    }

    await query('DELETE FROM "user" WHERE id_user = $1', [id]);

    await logActivity(req.user?.id_user, `Menghapus akun staf: ${check[0].nama} (ID #${id})`);

    res.status(200).json({ success: true, message: 'Akun karyawan berhasil dihapus.' });
  } catch (error: any) {
    console.error('Failed to delete employee:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}
