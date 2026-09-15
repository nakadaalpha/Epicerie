import { Router } from 'express';
import {
  getTransactions,
  getTransactionById,
  createTransaction,
  updateTransactionStatus,
} from '../controllers/transaction.controller';
import {
  verifyAuth,
  requirePermission,
  requireOwnershipOrPermission,
} from '../middlewares/auth';
import { query } from '../config/db';

const router = Router();

// 1. List transactions: Staff with orders:read_all sees all; Customer sees their own orders
router.get(
  '/',
  verifyAuth,
  requirePermission('orders:read_all', 'orders:read_own'),
  getTransactions
);

// 2. Transaction detail: anti-IDOR guard verifies resource ownership OR managerial orders:read_all
router.get(
  '/:id',
  verifyAuth,
  requireOwnershipOrPermission(async (req) => {
    const id = Number(req.params.id);
    if (!id || isNaN(id)) return null;
    const rows = await query<{ id_user_pembeli: number | null }>(
      'SELECT id_user_pembeli FROM transaksi WHERE id_transaksi = $1 LIMIT 1',
      [id]
    );
    return rows[0]?.id_user_pembeli;
  }, 'orders:read_all'),
  getTransactionById
);

// 3. Create transaction: allows both guest kiosk checkout & logged in member checkout
router.post('/', createTransaction);

// 4. Update status: Courier ('deliveries:update_status') or Manager/Admin ('orders:cancel', 'orders:read_all')
router.patch(
  '/:id/status',
  verifyAuth,
  requirePermission('deliveries:update_status', 'orders:cancel', 'orders:read_all'),
  updateTransactionStatus
);

export default router;
