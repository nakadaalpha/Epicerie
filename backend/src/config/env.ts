import dotenv from 'dotenv';
import path from 'path';

// Load .env from backend directory
dotenv.config({ path: path.resolve(__dirname, '../../.env') });

export const ENV = {
  PORT: parseInt(process.env.PORT || '5000', 10),
  NODE_ENV: process.env.NODE_ENV || 'development',
  DATABASE_URL: process.env.DATABASE_URL || '',
  CORS_ORIGIN: process.env.CORS_ORIGIN || 'http://localhost:3000',
  JWT_SECRET: process.env.JWT_SECRET || 'epicerie-jwt-secret-dev',
  CLOUDINARY_URL: process.env.CLOUDINARY_URL || '',
  MIDTRANS_SERVER_KEY: process.env.MIDTRANS_SERVER_KEY || '',
  MIDTRANS_CLIENT_KEY: process.env.MIDTRANS_CLIENT_KEY || '',
  PUSHER_APP_ID: process.env.PUSHER_APP_ID || '',
  PUSHER_KEY: process.env.PUSHER_KEY || '',
  PUSHER_SECRET: process.env.PUSHER_SECRET || '',
  PUSHER_CLUSTER: process.env.PUSHER_CLUSTER || 'ap1',
};
