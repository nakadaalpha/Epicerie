import { Router } from 'express';
import {
  login,
  register,
  getMe,
  logout,
  verifyForgotPin,
  resetPassword,
  updateProfile,
  requestCardPrint,
} from '../controllers/auth.controller';
import { verifyAuth } from '../middlewares/auth';

const router = Router();

router.post('/login', login);
router.post('/register', register);
router.get('/me', verifyAuth, getMe);
router.put('/profile', verifyAuth, updateProfile);
router.post('/request-card', verifyAuth, requestCardPrint);
router.post('/logout', logout);
router.post('/verify-pin', verifyForgotPin);
router.post('/reset-password', resetPassword);

export default router;
