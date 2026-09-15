import { Router } from 'express';
import {
  getProducts,
  getProductById,
  createProduct,
  updateProduct,
  deleteProduct,
} from '../controllers/product.controller';
import { verifyAuth, requirePermission } from '../middlewares/auth';

const router = Router();

router.get('/', getProducts);
router.get('/:id', getProductById);
router.post('/', verifyAuth, requirePermission('products:create'), createProduct);
router.put('/:id', verifyAuth, requirePermission('products:update'), updateProduct);
router.delete('/:id', verifyAuth, requirePermission('products:delete'), deleteProduct);

export default router;
