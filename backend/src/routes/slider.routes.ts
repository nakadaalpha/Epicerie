import { Router } from 'express';
import {
  getSliders,
  createSlider,
  updateSlider,
  deleteSlider,
} from '../controllers/slider.controller';
import { verifyAuth, requirePermission } from '../middlewares/auth';

const router = Router();

router.get('/', getSliders);
router.post('/', verifyAuth, requirePermission('sliders:manage'), createSlider);
router.put('/:id', verifyAuth, requirePermission('sliders:manage'), updateSlider);
router.delete('/:id', verifyAuth, requirePermission('sliders:manage'), deleteSlider);

export default router;
