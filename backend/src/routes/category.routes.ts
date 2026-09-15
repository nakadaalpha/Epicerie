import { Router } from 'express';
import {
  getCategories,
  createCategory,
  updateCategory,
  deleteCategory,
} from '../controllers/category.controller';
import { verifyAuth, requirePermission } from '../middlewares/auth';

const router = Router();

router.get('/', getCategories);
router.post('/', verifyAuth, requirePermission('categories:manage'), createCategory);
router.put('/:id', verifyAuth, requirePermission('categories:manage'), updateCategory);
router.delete('/:id', verifyAuth, requirePermission('categories:manage'), deleteCategory);

export default router;
