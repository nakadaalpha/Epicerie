'use client';

import React, { useState } from 'react';
import { formatRupiah } from '@/lib/utils';
import { TrendingUp } from 'lucide-react';

interface SalesChartProps {
  labels: string[];
  data: number[];
}

export const SalesChart: React.FC<SalesChartProps> = ({ labels = [], data = [] }) => {
  const [hoveredIndex, setHoveredIndex] = useState<number | null>(null);

  // SVG Chart Dimensions
  const height = 220;
  const width = 600;
  const paddingLeft = 65;
  const paddingRight = 20;
  const paddingTop = 25;
  const paddingBottom = 35;

  const chartWidth = width - paddingLeft - paddingRight;
  const chartHeight = height - paddingTop - paddingBottom;

  const maxValue = Math.max(...data, 100000);
  // Round up max to clean steps
  const stepCount = 4;
  const roundedMax = Math.ceil(maxValue / 50000) * 50000;
  const stepValue = roundedMax / stepCount;

  // Calculate coordinates for points
  const points = data.map((val, idx) => {
    const x = paddingLeft + (idx / Math.max(data.length - 1, 1)) * chartWidth;
    const y = paddingTop + chartHeight - (val / roundedMax) * chartHeight;
    return { x, y, val, label: labels[idx] || `Hari ${idx + 1}` };
  });

  // Generate smooth cubic bezier SVG path
  const buildSmoothPath = (pts: { x: number; y: number }[]) => {
    if (pts.length === 0) return '';
    if (pts.length === 1) return `M ${pts[0].x} ${pts[0].y}`;

    let path = `M ${pts[0].x} ${pts[0].y}`;
    for (let i = 0; i < pts.length - 1; i++) {
      const current = pts[i];
      const next = pts[i + 1];
      const controlX = (current.x + next.x) / 2;
      path += ` C ${controlX} ${current.y}, ${controlX} ${next.y}, ${next.x} ${next.y}`;
    }
    return path;
  };

  const linePath = buildSmoothPath(points);

  // Area path for gradient fill
  const areaPath =
    points.length > 0
      ? `${linePath} L ${points[points.length - 1].x} ${paddingTop + chartHeight} L ${points[0].x} ${
          paddingTop + chartHeight
        } Z`
      : '';

  return (
    <div className="w-full relative select-none">
      <div className="h-64 w-full relative">
        <svg
          viewBox={`0 0 ${width} ${height}`}
          className="w-full h-full overflow-visible"
          preserveAspectRatio="none"
        >
          <defs>
            <linearGradient id="salesGradient" x1="0" y1="0" x2="0" y2="1">
              <stop offset="0%" stopColor="#14b8a6" stopOpacity="0.45" />
              <stop offset="100%" stopColor="#3b82f6" stopOpacity="0.0" />
            </linearGradient>
          </defs>

          {/* Horizontal Gridlines & Y-Axis Ticks */}
          {Array.from({ length: stepCount + 1 }).map((_, i) => {
            const yVal = roundedMax - i * stepValue;
            const yPos = paddingTop + (i / stepCount) * chartHeight;
            const tickText = yVal >= 1000000 ? `${(yVal / 1000000).toFixed(1)}M` : `${Math.round(yVal / 1000)}k`;

            return (
              <g key={i}>
                <line
                  x1={paddingLeft}
                  y1={yPos}
                  x2={width - paddingRight}
                  y2={yPos}
                  stroke="#f3f4f6"
                  strokeWidth="1"
                  strokeDasharray="4 4"
                />
                <text
                  x={paddingLeft - 10}
                  y={yPos + 3}
                  textAnchor="end"
                  fontSize="10"
                  fontWeight="600"
                  fill="#9ca3af"
                >
                  Rp {tickText}
                </text>
              </g>
            );
          })}

          {/* Area Fill */}
          {areaPath && <path d={areaPath} fill="url(#salesGradient)" />}

          {/* Main Curve Line */}
          {linePath && (
            <path
              d={linePath}
              fill="none"
              stroke="#0d9488"
              strokeWidth="3.5"
              strokeLinecap="round"
              strokeLinejoin="round"
            />
          )}

          {/* Data Points & X-Axis Labels */}
          {points.map((pt, idx) => (
            <g key={idx}>
              {/* X-Axis Label */}
              <text
                x={pt.x}
                y={height - 8}
                textAnchor="middle"
                fontSize="10"
                fontWeight="700"
                fill={hoveredIndex === idx ? '#0d9488' : '#9ca3af'}
                className="transition-colors"
              >
                {pt.label}
              </text>

              {/* Hover Column Guide Line */}
              {hoveredIndex === idx && (
                <line
                  x1={pt.x}
                  y1={paddingTop}
                  x2={pt.x}
                  y2={paddingTop + chartHeight}
                  stroke="#0d9488"
                  strokeWidth="1.5"
                  strokeDasharray="3 3"
                />
              )}

              {/* Point Circle */}
              <circle
                cx={pt.x}
                cy={pt.y}
                r={hoveredIndex === idx ? '6' : '4.5'}
                fill="#ffffff"
                stroke="#0d9488"
                strokeWidth={hoveredIndex === idx ? '3.5' : '2.5'}
                className="cursor-pointer transition-all duration-150 drop-shadow-sm"
                onMouseEnter={() => setHoveredIndex(idx)}
                onMouseLeave={() => setHoveredIndex(null)}
              />

              {/* Transparent Wide Touch Target */}
              <rect
                x={pt.x - chartWidth / (points.length * 2)}
                y={paddingTop}
                width={chartWidth / points.length}
                height={chartHeight}
                fill="transparent"
                className="cursor-pointer"
                onMouseEnter={() => setHoveredIndex(idx)}
                onMouseLeave={() => setHoveredIndex(null)}
              />
            </g>
          ))}
        </svg>

        {/* Floating Tooltip */}
        {hoveredIndex !== null && points[hoveredIndex] && (
          <div
            className="absolute z-20 bg-gray-900/90 text-white text-xs rounded-xl py-1.5 px-3 shadow-xl backdrop-blur-xs pointer-events-none transform -translate-x-1/2 -translate-y-full mb-2 transition-all duration-150"
            style={{
              left: `${(points[hoveredIndex].x / width) * 100}%`,
              top: `${(points[hoveredIndex].y / height) * 100}%`,
            }}
          >
            <p className="text-[10px] text-gray-300 font-semibold">{points[hoveredIndex].label}</p>
            <p className="font-extrabold text-teal-300 text-sm">
              {formatRupiah(points[hoveredIndex].val)}
            </p>
          </div>
        )}
      </div>
    </div>
  );
};
