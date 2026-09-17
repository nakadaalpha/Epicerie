import { SignJWT, jwtVerify } from 'jose';
import { cookies } from 'next/headers';
import bcrypt from 'bcryptjs';
import { Role, Permission } from './permissions';

const SECRET_KEY = new TextEncoder().encode(
  process.env.JWT_SECRET ||
    process.env.SESSION_SECRET ||
    'epicerie-super-secret-jwt-key-2026-production'
);
const FALLBACK_SECRET_KEY = new TextEncoder().encode('epicerie-jwt-secret-dev');

const COOKIE_NAME = 'epicerie_session';

export interface SessionPayload {
  id_user: number;
  nama: string;
  username: string;
  role: string;
  normalizedRole?: Role;
  permissions?: Permission[];
  no_hp?: string | null;
  foto_profil?: string | null;
}

export async function createSessionToken(payload: SessionPayload): Promise<string> {
  return new SignJWT({ ...payload })
    .setProtectedHeader({ alg: 'HS256' })
    .setIssuedAt()
    .setExpirationTime('7d')
    .sign(SECRET_KEY);
}

export async function verifySessionToken(token: string): Promise<SessionPayload | null> {
  try {
    const { payload } = await jwtVerify(token, SECRET_KEY);
    return payload as unknown as SessionPayload;
  } catch (err) {
    try {
      const { payload } = await jwtVerify(token, FALLBACK_SECRET_KEY);
      return payload as unknown as SessionPayload;
    } catch {
      return null;
    }
  }
}

export async function setSessionCookie(payload: SessionPayload, tokenOverride?: string) {
  const token = tokenOverride || (await createSessionToken(payload));
  const cookieStore = await cookies();
  cookieStore.set(COOKIE_NAME, token, {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    path: '/',
    maxAge: 60 * 60 * 24 * 7, // 7 days
  });
}

export async function getSession(): Promise<SessionPayload | null> {
  const cookieStore = await cookies();
  const token = cookieStore.get(COOKIE_NAME)?.value;
  if (!token) return null;
  return verifySessionToken(token);
}

export async function clearSessionCookie() {
  const cookieStore = await cookies();
  cookieStore.delete(COOKIE_NAME);
}

export function verifyPassword(plain: string, hashed: string): boolean {
  try {
    // PHP bcrypt starts with $2y$, bcryptjs requires $2a$ or $2b$ for compatibility
    const compatibleHash = hashed.startsWith('$2y$')
      ? '$2a$' + hashed.substring(4)
      : hashed;
    return bcrypt.compareSync(plain, compatibleHash);
  } catch (err) {
    console.error('Password verification error:', err);
    return false;
  }
}

export function hashPassword(plain: string): string {
  return bcrypt.hashSync(plain, 10);
}
