const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch();
  const page = await browser.newPage();
  await page.setViewportSize({ width: 1280, height: 1000 });
  await page.goto('http://localhost:8000/admin/logs.php');

  // Wait for table
  await page.waitForSelector('.admin-table');

  // Hover over the first row in tbody
  await page.hover('.admin-table tbody tr:first-child');

  // Capture screenshot
  await page.screenshot({ path: 'debug_logs_table_hover.png' });

  await browser.close();
})();
