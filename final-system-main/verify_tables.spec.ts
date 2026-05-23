import { test, expect } from '@playwright/test';
import path from 'path';

test('capture table screenshots', async ({ page }) => {
  // Go to the users page
  await page.goto('http://localhost:8000/php_backend/admin/user.php');
  await page.waitForLoadState('networkidle');
  await page.screenshot({ path: 'debug_user_table.png', fullPage: true });

  // Hover over the first row in the table
  const firstRow = page.locator('table tbody tr').first();
  if (await firstRow.count() > 0) {
    await firstRow.hover();
    // Wait a bit for any transitions
    await page.waitForTimeout(500);
    await page.screenshot({ path: 'debug_user_table_hover.png', fullPage: true });
  }

  // Go to the logs page
  await page.goto('http://localhost:8000/php_backend/admin/logs.php');
  await page.waitForLoadState('networkidle');
  await page.screenshot({ path: 'debug_logs_table.png', fullPage: true });
});
