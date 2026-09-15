import { Router } from 'express';
import {
  createReview,
  getProductReviews,
  getPendingReviews,
  getUserReviews,
} from '../controllers/review.controller';
import { verifyAuth, requirePermission } from '../middlewares/auth';

const router = Router();

router.post('/', verifyAuth, requirePermission('reviews:write_purchased'), createReview);
router.get('/product/:id', getProductReviews);
router.get('/pending', verifyAuth, requirePermission('reviews:write_purchased'), getPendingReviews);
router.get('/history', verifyAuth, requirePermission('reviews:write_purchased'), getUserReviews);

export default router;
