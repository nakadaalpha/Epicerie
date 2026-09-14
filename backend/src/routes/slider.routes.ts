import { Router } from 'express';
import { getSliders } from '../controllers/slider.controller';

const router = Router();

router.get('/', getSliders);

export default router;
