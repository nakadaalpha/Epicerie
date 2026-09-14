import { Router } from 'express';
import {
  getTransactions,
  getTransactionById,
  createTransaction,
  updateTransactionStatus,
} from '../controllers/transaction.controller';
import { verifyAuth, requireRoles } from '../middlewares/auth';

const router = Router();

router.get('/', getTransactions);
router.get('/:id', getTransactionById);
router.post('/', createTransaction); // Allows both guest kiosk checkout & logged in member checkout
router.patch('/:id/status', verifyAuth, requireRoles('pemilik', 'admin', 'karyawan', 'kasir', 'kurir'), updateTransactionStatus);

export default router;
