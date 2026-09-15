export type Role =
  | 'PEMILIK'
  | 'MANAJER'
  | 'KASIR'
  | 'GUDANG'
  | 'KURIR'
  | 'PELANGGAN';

export type Permission =
  | '*'
  // Produk & Kategori & Slider
  | 'products:*'
  | 'products:read'
  | 'products:create'
  | 'products:update'
  | 'products:delete'
  | 'categories:manage'
  | 'categories:read'
  | 'sliders:manage'
  | 'sliders:read'
  // Kasir & POS
  | 'pos:access'
  | 'orders:create'
  | 'orders:read_today'
  | 'orders:cancel'
  | 'orders:read_all'
  | 'receipt:print'
  // Inventaris & Gudang
  | 'inventory:*'
  | 'inventory:read'
  | 'inventory:adjust_stock'
  // Pengiriman & Kurir
  | 'deliveries:read_assigned'
  | 'deliveries:update_status'
  // Laporan & Keuangan
  | 'reports:daily'
  | 'reports:financial'
  // Pelanggan
  | 'orders:read_own'
  | 'profile:manage_own'
  | 'reviews:write_purchased';

export const ROLE_PERMISSIONS: Record<Role, Permission[]> = {
  PEMILIK: ['*'],
  MANAJER: [
    'products:*',
    'categories:manage',
    'sliders:manage',
    'inventory:*',
    'orders:read_all',
    'orders:cancel',
    'reports:daily',
    'reports:financial',
    'pos:access',
    'receipt:print',
  ],
  KASIR: [
    'pos:access',
    'orders:create',
    'orders:read_today',
    'orders:read_all',
    'receipt:print',
    'products:read',
    'orders:read_own',
  ],
  GUDANG: [
    'products:read',
    'products:create',
    'products:update',
    'inventory:read',
    'inventory:adjust_stock',
    'categories:manage',
  ],
  KURIR: [
    'deliveries:read_assigned',
    'deliveries:update_status',
    'orders:read_own',
  ],
  PELANGGAN: [
    'products:read',
    'categories:read',
    'sliders:read',
    'orders:create',
    'orders:read_own',
    'profile:manage_own',
    'reviews:write_purchased',
  ],
};

export function normalizeRole(roleStr?: string | null): Role {
  if (!roleStr) return 'PELANGGAN';
  const clean = roleStr.trim().toUpperCase();

  switch (clean) {
    case 'PEMILIK':
    case 'ADMIN':
    case 'SUPERADMIN':
    case 'OWNER':
      return 'PEMILIK';

    case 'MANAJER':
    case 'MANAGER':
    case 'SUPERVISOR':
      return 'MANAJER';

    case 'KASIR':
    case 'CASHIER':
    case 'KARYAWAN': // Legacy fallback: karyawan bertindak sebagai staf kasir/toko
    case 'STAFF':
      return 'KASIR';

    case 'GUDANG':
    case 'INVENTORY':
    case 'WAREHOUSE':
      return 'GUDANG';

    case 'KURIR':
    case 'COURIER':
    case 'DRIVER':
      return 'KURIR';

    case 'PELANGGAN':
    case 'CUSTOMER':
    case 'MEMBER':
    case 'USER':
    case 'KIOSK':
    default:
      return 'PELANGGAN';
  }
}

export function hasPermission(roleStr: string | undefined | null, permission: Permission): boolean {
  const role = normalizeRole(roleStr);
  const permissions = ROLE_PERMISSIONS[role] || [];

  // 1. Superadmin wildcard
  if (permissions.includes('*')) {
    return true;
  }

  // 2. Exact match
  if (permissions.includes(permission)) {
    return true;
  }

  // 3. Resource wildcard match (e.g., 'products:*' matches 'products:create')
  if (permission.includes(':')) {
    const [resource] = permission.split(':');
    const wildcardPerm = `${resource}:*` as Permission;
    if (permissions.includes(wildcardPerm)) {
      return true;
    }
  }

  return false;
}

export function getRolePermissions(roleStr: string | undefined | null): Permission[] {
  const role = normalizeRole(roleStr);
  return ROLE_PERMISSIONS[role] || [];
}
