import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';
import { jwtVerify } from 'jose';
import { hasPermission, isStaff, Permission } from './lib/permissions';

const SECRET_KEY = new TextEncoder().encode(
  process.env.JWT_SECRET ||
    process.env.SESSION_SECRET ||
    'epicerie-super-secret-jwt-key-2026-production'
);
const FALLBACK_SECRET_KEY = new TextEncoder().encode('epicerie-jwt-secret-dev');

const COOKIE_NAME = 'epicerie_session';

interface DecodedSession {
  id_user: number;
  nama: string;
  username: string;
  role: string;
  permissions?: Permission[];
}

async function getSessionFromRequest(req: NextRequest): Promise<DecodedSession | null> {
  const token = req.cookies.get(COOKIE_NAME)?.value;
  if (!token) return null;

  try {
    const { payload } = await jwtVerify(token, SECRET_KEY);
    return payload as unknown as DecodedSession;
  } catch (err) {
    try {
      const { payload } = await jwtVerify(token, FALLBACK_SECRET_KEY);
      return payload as unknown as DecodedSession;
    } catch {
      return null;
    }
  }
}

export async function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;
  const session = await getSessionFromRequest(request);

  // 1. Guard for Auth Pages: Redirect logged-in users away from /login and /register
  if (pathname === '/login' || pathname === '/register') {
    if (session) {
      if (isStaff(session.role)) {
        return NextResponse.redirect(new URL('/admin', request.url));
      }
      return NextResponse.redirect(new URL('/', request.url));
    }
    return NextResponse.next();
  }

  // 2. Guard for Admin / Management Panel: /admin/*
  if (pathname.startsWith('/admin')) {
    if (!session) {
      const loginUrl = new URL('/login', request.url);
      loginUrl.searchParams.set('callbackUrl', pathname);
      return NextResponse.redirect(loginUrl);
    }

    // POS Cashier Kiosk guard inside admin
    if (pathname.startsWith('/admin/kiosk')) {
      if (!hasPermission(session.role, 'pos:access') && !isStaff(session.role)) {
        return NextResponse.redirect(new URL('/', request.url));
      }
      return NextResponse.next();
    }

    if (!isStaff(session.role)) {
      // Regular customer cannot access admin panel
      return NextResponse.redirect(new URL('/', request.url));
    }

    return NextResponse.next();
  }

  // 3. Redirect legacy /kiosk to /admin/kiosk
  if (pathname.startsWith('/kiosk')) {
    const target = pathname.replace(/^\/kiosk/, '/admin/kiosk');
    return NextResponse.redirect(new URL(target || '/admin/kiosk', request.url));
  }

  // 4. Guard for Product Reviews: /ulasan
  if (pathname.startsWith('/ulasan')) {
    if (!session) {
      const loginUrl = new URL('/login', request.url);
      loginUrl.searchParams.set('callbackUrl', pathname);
      return NextResponse.redirect(loginUrl);
    }
    return NextResponse.next();
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    /*
     * Match all request paths except for the ones starting with:
     * - api (API routes)
     * - _next/static (static files)
     * - _next/image (image optimization files)
     * - favicon.ico, sitemap.xml, robots.txt
     */
    '/((?!api|_next/static|_next/image|favicon.ico|sitemap.xml|robots.txt).*)',
  ],
};
