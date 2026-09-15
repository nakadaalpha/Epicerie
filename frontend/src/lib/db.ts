import { Pool } from 'pg';

let pool: Pool | undefined;

export function getDbPool(): Pool {
  if (!pool) {
    if (!process.env.DATABASE_URL) {
      console.warn('⚠️ DATABASE_URL is not set in frontend.');
    }
    pool = new Pool({
      connectionString: process.env.DATABASE_URL,
      ssl: {
        rejectUnauthorized: false,
      },
      max: 10,
      idleTimeoutMillis: 30000,
      connectionTimeoutMillis: 5000,
    });

    pool.on('error', (err) => {
      console.error('⚠️ Database pool error in frontend:', err.message || err);
    });
  }
  return pool;
}

export async function query<T = any>(text: string, params?: any[]): Promise<T[]> {
  try {
    const p = getDbPool();
    const res = await p.query(text, params);
    return (res?.rows || []) as T[];
  } catch (err: any) {
    console.error('⚠️ Database query error in frontend:', err.message || err);
    return [] as T[];
  }
}
