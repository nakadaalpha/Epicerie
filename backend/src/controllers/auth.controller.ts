import { Request, Response } from 'express';
import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import { query } from '../config/db';
import { ENV } from '../config/env';
import { AuthenticatedRequest } from '../middlewares/auth';

function verifyPassword(password: string, hash?: string): boolean {
  if (!hash) return false;
  const compatibleHash = hash.replace(/^\$2y\$/, '$2a$');
  try {
    return bcrypt.compareSync(password, compatibleHash);
  } catch (err) {
    return false;
  }
}

function hashPassword(password: string): string {
  const salt = bcrypt.genSaltSync(10);
  return bcrypt.hashSync(password, salt);
}

export async function login(req: Request, res: Response) {
  try {
    const { identifier, password } = req.body;
    if (!identifier || !password) {
      return res.status(400).json({ success: false, error: 'Username/Email/No. HP dan Password wajib diisi.' });
    }

    const cleanIdentifier = String(identifier).trim().toLowerCase();

    const rows = await query<any>(
      `SELECT * FROM "user" 
       WHERE LOWER(username) = $1 
          OR LOWER(email) = $1 
          OR no_hp = $2 
       LIMIT 1`,
      [cleanIdentifier, String(identifier).trim()]
    );

    if (rows.length === 0) {
      return res.status(401).json({ success: false, error: 'Akun tidak ditemukan. Silakan periksa kembali.' });
    }

    const user = rows[0];

    const isMatch = verifyPassword(password, user.password);
    if (!isMatch) {
      return res.status(401).json({ success: false, error: 'Kata sandi salah. Silakan coba lagi.' });
    }

    const payload = {
      id_user: Number(user.id_user),
      nama: user.nama,
      username: user.username,
      role: user.role,
      no_hp: user.no_hp,
      foto_profil: user.foto_profil,
    };

    const token = jwt.sign(payload, ENV.JWT_SECRET, { expiresIn: '7d' });

    // Set HTTP-only cookie
    res.cookie('session', token, {
      httpOnly: true,
      secure: ENV.NODE_ENV === 'production',
      sameSite: 'lax',
      maxAge: 7 * 24 * 60 * 60 * 1000,
    });

    res.status(200).json({
      success: true,
      message: 'Login berhasil.',
      token,
      user: payload,
    });
  } catch (error: any) {
    console.error('Login error:', error);
    res.status(500).json({ success: false, error: error.message || 'Gagal memproses login.' });
  }
}

export async function register(req: Request, res: Response) {
  try {
    const { nama, username, no_hp, pin_keamanan, password, password_confirmation } = req.body;

    if (!nama || !username || !no_hp || !pin_keamanan || !password) {
      return res.status(400).json({ success: false, error: 'Semua data pendaftaran wajib diisi.' });
    }

    if (password !== password_confirmation) {
      return res.status(400).json({ success: false, error: 'Konfirmasi kata sandi tidak cocok.' });
    }

    if (pin_keamanan.length !== 6 || !/^\d+$/.test(pin_keamanan)) {
      return res.status(400).json({ success: false, error: 'PIN Keamanan harus tepat 6 digit angka.' });
    }

    const cleanUsername = String(username).trim().toLowerCase();
    const cleanPhone = String(no_hp).trim();

    // Check duplicate username or phone
    const existing = await query<any>(
      'SELECT id_user, username, no_hp FROM "user" WHERE LOWER(username) = $1 OR no_hp = $2 LIMIT 1',
      [cleanUsername, cleanPhone]
    );

    if (existing.length > 0) {
      if (existing[0].username.toLowerCase() === cleanUsername) {
        return res.status(400).json({ success: false, error: 'Username sudah digunakan. Silakan pilih username lain.' });
      }
      return res.status(400).json({ success: false, error: 'Nomor HP sudah terdaftar. Silakan gunakan nomor lain atau login.' });
    }

    // Generate 12-digit Member ID: DDMMYY + 6 random digits
    const today = new Date();
    const d = String(today.getDate()).padStart(2, '0');
    const m = String(today.getMonth() + 1).padStart(2, '0');
    const y = String(today.getFullYear()).slice(-2);
    const randomSuffix = Math.floor(100000 + Math.random() * 900000);
    const idUser = Number(`${d}${m}${y}${randomSuffix}`);

    const hashedPassword = hashPassword(password);
    const now = new Date().toISOString();

    await query(
      `INSERT INTO "user" (
        id_user, nama, username, password, no_hp, role, pin_keamanan, status_cetak_kartu, created_at, updated_at
      ) VALUES ($1, $2, $3, $4, $5, 'pelanggan', $6, 'pending', $7, $8)`,
      [idUser, String(nama).trim(), cleanUsername, hashedPassword, cleanPhone, pin_keamanan, now, now]
    );

    const payload = {
      id_user: idUser,
      nama: String(nama).trim(),
      username: cleanUsername,
      role: 'pelanggan',
      no_hp: cleanPhone,
      foto_profil: null,
    };

    const token = jwt.sign(payload, ENV.JWT_SECRET, { expiresIn: '7d' });

    res.cookie('session', token, {
      httpOnly: true,
      secure: ENV.NODE_ENV === 'production',
      sameSite: 'lax',
      maxAge: 7 * 24 * 60 * 60 * 1000,
    });

    res.status(201).json({
      success: true,
      message: 'Pendaftaran berhasil. Selamat datang di Épicerie!',
      token,
      user: payload,
    });
  } catch (error: any) {
    console.error('Register error:', error);
    res.status(500).json({ success: false, error: error.message || 'Gagal memproses pendaftaran.' });
  }
}

export async function getMe(req: AuthenticatedRequest, res: Response) {
  try {
    if (!req.user) {
      return res.status(401).json({ success: false, error: 'Pengguna belum login.' });
    }

    const rows = await query<any>('SELECT * FROM "user" WHERE id_user = $1 LIMIT 1', [req.user.id_user]);
    if (rows.length === 0) {
      return res.status(404).json({ success: false, error: 'Profil tidak ditemukan.' });
    }

    const user = rows[0];

    // Calculate customer membership status
    let membership = 'Classic';
    let discountPercent = 0;
    let badgeColor = 'bg-slate-100 text-slate-700 border-slate-300';
    let totalSpent = 0;
    let totalCompletedOrders = 0;

    if (user.role === 'pelanggan') {
      const stats = await query<any>(
        `SELECT 
          COALESCE(SUM(total_bayar), 0) as total_spent,
          COUNT(id_transaksi) as total_completed
         FROM transaksi 
         WHERE id_user_pembeli = $1 AND status = 'selesai'`,
        [user.id_user]
      );

      totalSpent = Number(stats[0]?.total_spent || 0);
      totalCompletedOrders = Number(stats[0]?.total_completed || 0);

      if (totalSpent >= 2000000 || totalCompletedOrders >= 30) {
        membership = 'Gold';
        discountPercent = 10;
        badgeColor = 'bg-amber-100 text-amber-900 border-amber-300';
      } else if (totalSpent >= 1000000 || totalCompletedOrders >= 20) {
        membership = 'Silver';
        discountPercent = 5;
        badgeColor = 'bg-gray-200 text-gray-800 border-gray-400';
      } else if (totalSpent >= 500000 || totalCompletedOrders >= 10) {
        membership = 'Bronze';
        discountPercent = 2;
        badgeColor = 'bg-orange-100 text-orange-800 border-orange-300';
      }
    }

    res.status(200).json({
      success: true,
      user: {
        id_user: Number(user.id_user),
        nama: user.nama,
        username: user.username,
        email: user.email,
        no_hp: user.no_hp,
        role: user.role,
        foto_profil: user.foto_profil,
        status_cetak_kartu: user.status_cetak_kartu,
        membership,
        discountPercent,
        badgeColor,
        totalSpent,
        totalCompletedOrders,
      },
    });
  } catch (error: any) {
    console.error('Get profile error:', error);
    res.status(500).json({ success: false, error: error.message });
  }
}

export async function logout(req: Request, res: Response) {
  res.clearCookie('session');
  res.status(200).json({ success: true, message: 'Logout berhasil.' });
}

export async function verifyForgotPin(req: Request, res: Response) {
  try {
    const { username, no_hp, pin_keamanan } = req.body;
    if (!username || !no_hp || !pin_keamanan) {
      return res.status(400).json({ success: false, error: 'Username, No. HP, dan PIN wajib diisi.' });
    }

    const rows = await query<any>(
      `SELECT id_user, nama, username 
       FROM "user" 
       WHERE LOWER(username) = $1 AND no_hp = $2 AND pin_keamanan = $3 
       LIMIT 1`,
      [String(username).trim().toLowerCase(), String(no_hp).trim(), String(pin_keamanan).trim()]
    );

    if (rows.length === 0) {
      return res.status(400).json({ success: false, error: 'Verifikasi Gagal! Username, No. HP, atau PIN salah.' });
    }

    res.status(200).json({ success: true, id_user: rows[0].id_user, nama: rows[0].nama });
  } catch (error: any) {
    res.status(500).json({ success: false, error: error.message || 'Gagal memverifikasi data.' });
  }
}

export async function resetPassword(req: Request, res: Response) {
  try {
    const { id_user, password, password_confirmation } = req.body;
    if (password !== password_confirmation) {
      return res.status(400).json({ success: false, error: 'Konfirmasi password baru tidak cocok.' });
    }
    if (String(password).length < 6) {
      return res.status(400).json({ success: false, error: 'Password baru minimal 6 karakter.' });
    }

    const hashedPassword = hashPassword(password);
    const now = new Date().toISOString();

    await query(
      `UPDATE "user" SET password = $1, updated_at = $2 WHERE id_user = $3`,
      [hashedPassword, now, id_user]
    );

    res.status(200).json({ success: true, message: 'Password berhasil diubah. Silakan login kembali.' });
  } catch (error: any) {
    res.status(500).json({ success: false, error: error.message || 'Gagal mengubah password.' });
  }
}

