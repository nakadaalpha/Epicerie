import { Router } from 'express';
import {
  getProducts,
  getProductById,
  createProduct,
  updateProduct,
  deleteProduct,
} from '../controllers/product.controller';
import { verifyAuth, requireRoles } from '../middlewares/auth';

const router = Router();

router.get('/', getProducts);
router.get('/:id', getProductById);
router.post('/', verifyAuth, requireRoles('pemilik', 'admin'), createProduct);
router.put('/:id', verifyAuth, requireRoles('pemilik', 'admin'), updateProduct);
router.delete('/:id', verifyAuth, requireRoles('pemilik', 'admin'), deleteProduct);

export default router;
