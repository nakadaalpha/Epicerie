import { Router } from 'express';
import {
  getCourierTasks,
  startDelivery,
  completeDelivery,
  updateLocation,
} from '../controllers/courier.controller';
import { verifyAuth, requirePermission } from '../middlewares/auth';

const router = Router();

router.use(verifyAuth);

router.get(
  '/tasks',
  requirePermission('deliveries:read_assigned', 'orders:read_all'),
  getCourierTasks
);

router.post(
  '/tasks/:id/start',
  requirePermission('deliveries:update_status', 'orders:read_all'),
  startDelivery
);

router.post(
  '/tasks/:id/complete',
  requirePermission('deliveries:update_status', 'orders:read_all'),
  completeDelivery
);

router.post(
  '/location',
  requirePermission('deliveries:update_status', 'orders:read_all'),
  updateLocation
);

export default router;
