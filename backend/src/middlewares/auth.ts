import { Request, Response, NextFunction } from 'express';
import jwt from 'jsonwebtoken';
import { ENV } from '../config/env';

export interface AuthenticatedRequest extends Request {
  user?: {
    id_user: number;
    nama: string;
    username: string;
    role: string;
    no_hp?: string;
  };
}

export function verifyAuth(req: AuthenticatedRequest, res: Response, next: NextFunction) {
  let token: string | undefined;

  // 1. Check Bearer token in Authorization header
  const authHeader = req.headers.authorization;
  if (authHeader && authHeader.startsWith('Bearer ')) {
    token = authHeader.split(' ')[1];
  }

  // 2. Fallback to cookie
  if (!token && req.cookies && req.cookies.session) {
    token = req.cookies.session;
  }

  if (!token) {
    return res.status(401).json({
      success: false,
      error: 'Akses ditolak. Token autentikasi tidak ditemukan.',
    });
  }

  try {
    const decoded = jwt.verify(token, ENV.JWT_SECRET) as any;
    req.user = decoded;
    next();
  } catch (err) {
    return res.status(401).json({
      success: false,
      error: 'Sesi kedaluwarsa atau token tidak valid. Silakan login kembali.',
    });
  }
}

export function requireRoles(...allowedRoles: string[]) {
  return (req: AuthenticatedRequest, res: Response, next: NextFunction) => {
    if (!req.user) {
      return res.status(401).json({ success: false, error: 'Silakan login terlebih dahulu.' });
    }

    const userRole = (req.user.role || '').toLowerCase();
    const isAllowed = allowedRoles.some((r) => r.toLowerCase() === userRole);

    if (!isAllowed) {
      return res.status(403).json({
        success: false,
        error: 'Anda tidak memiliki hak akses untuk tindakan ini.',
      });
    }

    next();
  };
}
