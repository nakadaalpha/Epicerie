import { Router } from 'express';
import {
  getAddresses,
  createAddress,
  updateAddress,
  deleteAddress,
  setPrimaryAddress,
} from '../controllers/address.controller';
import { verifyAuth } from '../middlewares/auth';

const router = Router();

router.use(verifyAuth);

router.get('/', getAddresses);
router.post('/', createAddress);
router.put('/:id', updateAddress);
router.delete('/:id', deleteAddress);
router.post('/:id/primary', setPrimaryAddress);

export default router;
