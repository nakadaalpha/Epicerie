import { Pool } from 'pg';

let pool: Pool | undefined;

export function getDbPool(): Pool {
  if (!pool) {
    pool = new Pool({
      connectionString: process.env.DATABASE_URL,
      ssl: {
        rejectUnauthorized: false,
      },
      max: 10,
      idleTimeoutMillis: 30000,
      connectionTimeoutMillis: 5000,
    });
  }
  return pool;
}

export async function query<T = any>(text: string, params?: any[]): Promise<T[]> {
  const p = getDbPool();
  const start = Date.now();
  try {
    const res = await p.query(text, params);
    return res.rows as T[];
  } catch (err) {
    console.error('Database query error:', err, 'Query:', text);
    throw err;
  }
}
