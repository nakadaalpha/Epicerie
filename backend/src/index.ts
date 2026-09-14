import { createApp } from './app';
import { ENV } from './config/env';
import { getDbPool, closeDbPool } from './config/db';

const app = createApp();

const server = app.listen(ENV.PORT, () => {
  console.log(`🚀 [Épicerie Backend] Server berjalan di http://localhost:${ENV.PORT}`);
  console.log(`📑 [Swagger Docs] Tersedia di http://localhost:${ENV.PORT}/api-docs/`);
  console.log(`🩺 [Health Check] Tersedia di http://localhost:${ENV.PORT}/api/health`);

  // Initialize DB pool connection check
  getDbPool()
    .query('SELECT 1')
    .then(() => {
      console.log('✅ [Database] Berhasil terhubung ke Supabase PostgreSQL.');
    })
    .catch((err) => {
      console.error('❌ [Database] Gagal terhubung ke database:', err.message);
    });
});

// Fast Graceful Shutdown on Ctrl+C (SIGINT) and kill (SIGTERM)
const shutdown = async (signal: string) => {
  console.log(`\n🛑 [Épicerie Backend] Menerima ${signal}. Mematikan server...`);

  // Close keep-alive sockets immediately
  if (typeof (server as any).closeAllConnections === 'function') {
    (server as any).closeAllConnections();
  }

  server.close(async () => {
    try {
      await closeDbPool();
    } catch {
      // ignore
    }
    process.exit(0);
  });

  // Fallback force exit in 1 second if any handles linger
  setTimeout(() => {
    process.exit(0);
  }, 1000).unref();
};

process.on('SIGINT', () => shutdown('SIGINT'));
process.on('SIGTERM', () => shutdown('SIGTERM'));
