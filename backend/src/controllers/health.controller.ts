import { Request, Response } from 'express';
import { query } from '../config/db';

export async function checkHealth(req: Request, res: Response) {
  let dbStatus = 'disconnected';
  let dbLatency = 0;

  try {
    const start = Date.now();
    await query('SELECT 1 as ping');
    dbLatency = Date.now() - start;
    dbStatus = 'connected';
  } catch (err: any) {
    console.error('Database health ping failed:', err.message);
  }

  res.status(200).json({
    success: true,
    status: 'ok',
    service: 'Épicerie Backend API',
    uptime: process.uptime(),
    timestamp: new Date().toISOString(),
    database: {
      status: dbStatus,
      latencyMs: dbLatency,
      engine: 'PostgreSQL (Supabase)',
    },
    version: '1.0.0',
  });
}
