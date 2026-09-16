import { Pool, PoolClient } from 'pg';
import { ENV } from './env';

let pool: Pool | undefined;

export function getDbPool(): Pool {
  if (!pool) {
    if (!ENV.DATABASE_URL) {
      console.warn('⚠️ DATABASE_URL is not set. Database operations will fail.');
    }
    pool = new Pool({
      connectionString: ENV.DATABASE_URL,
      ssl: {
        rejectUnauthorized: false,
      },
      max: 15,
      idleTimeoutMillis: 30000,
      connectionTimeoutMillis: 5000,
    });

    pool.on('error', (err) => {
      console.error('Unexpected error on idle database client', err);
    });
  }
  return pool;
}

export async function closeDbPool(): Promise<void> {
  if (pool) {
    await pool.end();
    pool = undefined;
  }
}

export async function query<T = any>(text: string, params?: any[]): Promise<T[]> {
  const p = getDbPool();
  try {
    const res = await p.query(text, params);
    return res.rows as T[];
  } catch (err) {
    console.error('Database query error:', err, '\nQuery:', text);
    throw err;
  }
}

export async function withTransaction<T>(
  callback: (client: PoolClient) => Promise<T>
): Promise<T> {
  const p = getDbPool();
  const client = await p.connect();
  try {
    await client.query('BEGIN');
    const result = await callback(client);
    await client.query('COMMIT');
    return result;
  } catch (error) {
    await client.query('ROLLBACK');
    throw error;
  } finally {
    client.release();
  }
}
