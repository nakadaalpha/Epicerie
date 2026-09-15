// Unified HTTP Client for Decoupled Express Backend

export const API_BASE_URL =
  process.env.NEXT_PUBLIC_API_URL || 'http://localhost:5000';

export interface ApiResponse<T = any> {
  success: boolean;
  data?: T;
  message?: string;
  error?: string;
  user?: any;
  token?: string;
  id_user?: number;
  nama?: string;
  [key: string]: any;
}

export async function apiFetch<T = any>(
  endpoint: string,
  options: RequestInit = {}
): Promise<ApiResponse<T>> {
  const url = `${API_BASE_URL}${endpoint.startsWith('/') ? '' : '/'}${endpoint}`;

  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    ...((options.headers as Record<string, string>) || {}),
  };

  // If executing on the Next.js server side (SSR / Server Action), forward session token
  if (typeof window === 'undefined') {
    try {
      const { cookies } = await import('next/headers');
      const cookieStore = await cookies();
      const token = cookieStore.get('epicerie_session')?.value;
      if (token && !headers['Authorization'] && !headers['authorization']) {
        headers['Authorization'] = `Bearer ${token}`;
        headers['Cookie'] = `session=${token}`;
      }
    } catch (e) {
      // Cookies not accessible outside of request scope
    }
  }

  try {
    const res = await fetch(url, {
      ...options,
      headers,
      credentials: 'include',
      cache: options.cache || 'no-store',
    });

    const data = await res.json();
    return data as ApiResponse<T>;
  } catch (error: any) {
    console.error(`[API Fetch Error] ${options.method || 'GET'} ${url}:`, error);
    return {
      success: false,
      error: error?.message || 'Gagal menghubungi server backend.',
    };
  }
}
