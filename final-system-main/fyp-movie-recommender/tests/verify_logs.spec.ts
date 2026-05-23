import { test, expect } from '@playwright/test';

test('logs table alignment and hover', async ({ page }) => {
  await page.goto('http://localhost:8000/admin/logs.php');
  await page.waitForSelector('.admin-table');

  // Take a baseline screenshot of the logs table
  await page.screenshot({ path: 'debug_logs_table_alignment.png' });

  // Hover over the first row
  await page.hover('.admin-table tbody tr:first-child');
  await page.screenshot({ path: 'debug_logs_table_hover.png' });
});
