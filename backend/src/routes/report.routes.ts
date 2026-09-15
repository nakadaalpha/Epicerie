import { Router } from 'express';
import {
  getDashboardStats,
  getFinancialReport,
  getCardQueue,
  completeCardPrint,
} from '../controllers/report.controller';
import { verifyAuth, requirePermission } from '../middlewares/auth';

const router = Router();

router.get('/dashboard', verifyAuth, requirePermission('reports:daily'), getDashboardStats);
router.get('/financial', verifyAuth, requirePermission('reports:financial', 'reports:daily'), getFinancialReport);
router.get('/card-queue', verifyAuth, requirePermission('reports:daily'), getCardQueue);
router.post('/card-queue/:id/complete', verifyAuth, requirePermission('reports:daily'), completeCardPrint);

export default router;
