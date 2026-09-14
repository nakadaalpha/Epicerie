import { Router } from 'express';
import {
  login,
  register,
  getMe,
  logout,
  verifyForgotPin,
  resetPassword,
} from '../controllers/auth.controller';
import { verifyAuth } from '../middlewares/auth';

const router = Router();

router.post('/login', login);
router.post('/register', register);
router.get('/me', verifyAuth, getMe);
router.post('/logout', logout);
router.post('/verify-pin', verifyForgotPin);
router.post('/reset-password', resetPassword);

export default router;
