import { Request, Response, NextFunction } from 'express';
import jwt from 'jsonwebtoken';
import { ENV } from '../config/env';
import {
  Permission,
  Role,
  hasPermission,
  getRolePermissions,
  normalizeRole,
} from '../config/permissions';

export interface AuthenticatedRequest extends Request {
  user?: {
    id_user: number;
    nama: string;
    username: string;
    role: string;
    normalizedRole: Role;
    permissions: Permission[];
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
    req.user = {
      ...decoded,
      normalizedRole: normalizeRole(decoded.role),
      permissions: getRolePermissions(decoded.role),
    };
    next();
  } catch (err) {
    return res.status(401).json({
      success: false,
      error: 'Sesi kedaluwarsa atau token tidak valid. Silakan login kembali.',
    });
  }
}

/**
 * Enforces granular permission-based authorization.
 * Passes if user has ANY of the specified permissions.
 */
export function requirePermission(...requiredPermissions: Permission[]) {
  return (req: AuthenticatedRequest, res: Response, next: NextFunction) => {
    if (!req.user) {
      return res.status(401).json({ success: false, error: 'Silakan login terlebih dahulu.' });
    }

    const userRole = req.user.role;
    const isAllowed = requiredPermissions.some((perm) => hasPermission(userRole, perm));

    if (!isAllowed) {
      return res.status(403).json({
        success: false,
        error: 'Akses ditolak: Anda tidak memiliki izin untuk melakukan tindakan ini.',
      });
    }

    next();
  };
}

/**
 * PBAC / Anti-IDOR Guard:
 * Grants access if user has managerial permission OR is the owner of the resource.
 */
export function requireOwnershipOrPermission(
  getOwnerId: (req: AuthenticatedRequest) => Promise<number | null | undefined> | number | null | undefined,
  fallbackPermission: Permission = 'orders:read_all'
) {
  return async (req: AuthenticatedRequest, res: Response, next: NextFunction) => {
    if (!req.user) {
      return res.status(401).json({ success: false, error: 'Silakan login terlebih dahulu.' });
    }

    // 1. Check if user has bypass/managerial permission
    if (hasPermission(req.user.role, fallbackPermission)) {
      return next();
    }

    // 2. Check ownership
    try {
      const ownerId = await getOwnerId(req);
      if (ownerId !== null && ownerId !== undefined && Number(ownerId) === Number(req.user.id_user)) {
        return next();
      }

      return res.status(403).json({
        success: false,
        error: 'Akses ditolak: Anda tidak memiliki izin untuk mengakses data ini.',
      });
    } catch (error: any) {
      return res.status(500).json({
        success: false,
        error: 'Gagal memverifikasi hak kepemilikan akses.',
      });
    }
  };
}

/**
 * Legacy role-based guard for backward compatibility, with normalized role resolution.
 */
export function requireRoles(...allowedRoles: string[]) {
  return (req: AuthenticatedRequest, res: Response, next: NextFunction) => {
    if (!req.user) {
      return res.status(401).json({ success: false, error: 'Silakan login terlebih dahulu.' });
    }

    const userRole = normalizeRole(req.user.role);
    const isAllowed = allowedRoles.some((r) => normalizeRole(r) === userRole);

    if (!isAllowed) {
      return res.status(403).json({
        success: false,
        error: 'Anda tidak memiliki hak akses untuk tindakan ini.',
      });
    }

    next();
  };
}
