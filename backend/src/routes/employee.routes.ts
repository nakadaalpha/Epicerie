import { Router } from 'express';
import {
  getEmployees,
  createEmployee,
  updateEmployee,
  deleteEmployee,
} from '../controllers/employee.controller';
import { verifyAuth, requireRoles } from '../middlewares/auth';

const router = Router();

router.use(verifyAuth);
router.use(requireRoles('pemilik', 'admin', 'manajer'));

router.get('/', getEmployees);
router.post('/', createEmployee);
router.put('/:id', updateEmployee);
router.delete('/:id', deleteEmployee);

export default router;
