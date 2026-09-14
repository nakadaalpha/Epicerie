import express, { Application, Request, Response } from 'express';
import cors from 'cors';
import helmet from 'helmet';
import morgan from 'morgan';
import cookieParser from 'cookie-parser';
import path from 'path';
import { ENV } from './config/env';
import apiRoutes from './routes';
import { errorHandler } from './middlewares/errorHandler';

export function createApp(): Application {
  const app = express();

  // Security headers (relaxed slightly for Swagger UI static assets)
  app.use(
    helmet({
      contentSecurityPolicy: false,
      crossOriginResourcePolicy: { policy: 'cross-origin' },
    })
  );

  // CORS configuration
  app.use(
    cors({
      origin: [ENV.CORS_ORIGIN, 'http://localhost:3000', 'http://127.0.0.1:3000'],
      credentials: true,
      methods: ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
      allowedHeaders: ['Content-Type', 'Authorization'],
    })
  );

  // Request logger
  if (ENV.NODE_ENV !== 'test') {
    app.use(morgan('dev'));
  }

  // Parsers
  app.use(express.json({ limit: '10mb' }));
  app.use(express.urlencoded({ extended: true, limit: '10mb' }));
  app.use(cookieParser());

  // Static files for Swagger UI (/api-docs)
  const docsPath = path.resolve(__dirname, '../docs');
  app.use('/api-docs', express.static(docsPath));

  // Mount API routes
  app.use('/api', apiRoutes);

  // Root endpoint info
  app.get('/', (req: Request, res: Response) => {
    res.json({
      name: 'Épicerie Backend API',
      status: 'active',
      version: '1.0.0',
      docs: '/api-docs',
      health: '/api/health',
    });
  });

  // 404 Handler
  app.use((req: Request, res: Response) => {
    res.status(404).json({
      success: false,
      error: `Endpoint '${req.method} ${req.originalUrl}' tidak ditemukan.`,
    });
  });

  // Centralized Error Handler
  app.use(errorHandler);

  return app;
}
