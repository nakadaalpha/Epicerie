import { query } from '../config/db';

export async function logActivity(
  idUser: number | null | undefined,
  jenisAktivitas: string
): Promise<void> {
  if (!idUser) return;

  try {
    const now = new Date().toISOString();
    await query(
      `INSERT INTO log_aktivitas (id_user, waktu_aktivitas, jenis_aktivitas)
       VALUES ($1, $2, $3)`,
      [idUser, now, jenisAktivitas]
    );
  } catch (error) {
    // Non-blocking: audit logging error should be reported but not fail the transaction
    console.error('⚠️ Failed to record audit log:', error);
  }
}
