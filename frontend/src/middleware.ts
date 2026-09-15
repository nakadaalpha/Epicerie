import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';
import { jwtVerify } from 'jose';
import { hasPermission, isStaff, Permission } from './lib/permissions';

const SECRET_KEY = new TextEncoder().encode(
  process.env.JWT_SECRET ||
    process.env.SESSION_SECRET ||
    'epicerie-super-secret-jwt-key-2026-production'
);

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
    return null;
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

    if (!isStaff(session.role)) {
      // Regular customer cannot access admin panel
      return NextResponse.redirect(new URL('/', request.url));
    }

    return NextResponse.next();
  }

  // 3. Guard for POS Cashier Kiosk: /kiosk
  if (pathname.startsWith('/kiosk')) {
    if (!session) {
      const loginUrl = new URL('/login', request.url);
      loginUrl.searchParams.set('callbackUrl', pathname);
      return NextResponse.redirect(loginUrl);
    }

    if (!hasPermission(session.role, 'pos:access')) {
      // If logged in as customer without POS access, redirect to storefront
      return NextResponse.redirect(new URL('/', request.url));
    }

    return NextResponse.next();
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
