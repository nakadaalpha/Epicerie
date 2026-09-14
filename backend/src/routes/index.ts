import { Router } from 'express';
import healthRoutes from './health.routes';
import authRoutes from './auth.routes';
import productRoutes from './product.routes';
import categoryRoutes from './category.routes';
import sliderRoutes from './slider.routes';
import transactionRoutes from './transaction.routes';
import reviewRoutes from './review.routes';

const router = Router();

router.use('/health', healthRoutes);
router.use('/auth', authRoutes);
router.use('/products', productRoutes);
router.use('/categories', categoryRoutes);
router.use('/sliders', sliderRoutes);
router.use('/transactions', transactionRoutes);
router.use('/reviews', reviewRoutes);

export default router;
