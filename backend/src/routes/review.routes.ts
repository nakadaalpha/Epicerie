import { Router } from 'express';
import {
  createReview,
  getProductReviews,
  getPendingReviews,
  getUserReviews,
} from '../controllers/review.controller';
import { verifyAuth } from '../middlewares/auth';

const router = Router();

router.post('/', verifyAuth, createReview);
router.get('/product/:id', getProductReviews);
router.get('/pending', verifyAuth, getPendingReviews);
router.get('/history', verifyAuth, getUserReviews);

export default router;
