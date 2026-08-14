import { chromium } from 'playwright';

const BASE = 'http://localhost:50000';

async function sleep(ms) {
  return new Promise(r => setTimeout(r, ms));
}

async function run() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext();
  const page = await context.newPage();

  const results = { passed: 0, failed: 0, errors: [] };
  function assert(name, ok, detail) {
    if (ok) {
      console.log(`  ✅ ${name}`);
      results.passed++;
    } else {
      console.log(`  ❌ ${name} — ${detail || ''}`);
      results.failed++;
      results.errors.push({ name, detail });
    }
  }

  try {
    // ===== 1. トップページ =====
    console.log('\n=== 1. Top Page ===');
    await page.goto(BASE, { waitUntil: 'networkidle' });
    await sleep(1500);

    const title = await page.title();
    assert('Page title is "RHEMS CDN Tools"', title === 'RHEMS CDN Tools', `Got: ${title}`);

    const bodyText = await page.textContent('body');
    assert('Shows "Please choose a CDN account"', bodyText.includes('Please choose a CDN account'));
    assert('Shows "cloudfront" in account list', bodyText.includes('cloudfront'));
    assert('Shows "stg-reducer" in account list', bodyText.includes('stg-reducer'));
    assert('Shows "N/A" badge for notification', bodyText.includes('N/A'));

    // ===== 2. Purge ページに遷移 =====
    console.log('\n=== 2. Navigate to Purge Page ===');
    const stgLink = page.locator('tr', { hasText: 'stg-reducer' });
    await stgLink.click();
    await page.waitForURL('**/cdn/cloudfront/stg-reducer', { timeout: 5000 });
    await sleep(1000);

    const purgeTitle = await page.title();
    assert('Purge page title shows "CloudFront"', purgeTitle === 'RHEMS CDN Tools - CloudFront');

    const purgeBody = await page.textContent('body');
    assert('Shows "CloudFront - stg-reducer" heading', purgeBody.includes('CloudFront - stg-reducer'));
    assert('Shows "Distribution ID" label', purgeBody.includes('Distribution ID'));
    assert('Shows "ENBSM8SEZT65K" in select', purgeBody.includes('ENBSM8SEZT65K'));
    assert('Shows textarea for URLs', await page.locator('textarea#urls').isVisible());
    assert('Shows Purge button', await page.locator('button:has-text("Purge")').isVisible());
    assert('Shows Queue section', purgeBody.includes('Queue'));
    assert('Shows Update button', await page.locator('button:has-text("Update")').isVisible());

    // ===== 3. Purge ボタン → モーダル確認 =====
    console.log('\n=== 3. Click Purge → Modal ===');
    await page.fill('textarea#urls', '/*');
    await sleep(300);

    // Click Purge button
    await page.locator('button:has-text("Purge")').click();
    await sleep(800);

    // Check for modal content by looking for the warning text
    const modalText = page.locator('h3:has-text("Purge warning")');
    const modalVisible = await modalText.isVisible().catch(() => false);
    assert('Purge confirmation modal shows warning text', modalVisible);

    // Check modal has OK and Cancel buttons
    if (modalVisible) {
      const okBtn = page.locator('button:has-text("OK")');
      const cancelBtn = page.locator('button:has-text("Cancel")');
      assert('Modal has OK button', await okBtn.isVisible());
      assert('Modal has Cancel button', await cancelBtn.isVisible());

      // ===== 4. OK をクリック → Purge 実行 =====
      console.log('\n=== 4. Confirm Purge → Queue ===');
      await okBtn.click();

      // Wait for AJAX + page reload
      await sleep(3000);

      // Check page reloaded with queue entry
      const afterUrl = page.url();
      assert('Page reloaded after purge', afterUrl.includes('/cdn/cloudfront/stg-reducer'));

      const afterBody = await page.textContent('body');
      const hasQueueEntry = afterBody.includes('Processing') || afterBody.includes('Done');
      assert('Queue shows purge entry (Processing or Done)', hasQueueEntry);

      // Check for a Purge ID in the table
      const queueRows = await page.locator('table tbody tr').count();
      assert('Queue table has at least 1 entry', queueRows >= 1, `Found ${queueRows} rows`);
    }

    // ===== 5. Update ボタン =====
    console.log('\n=== 5. Update Queue Button ===');
    const updateBtn = page.locator('button:has-text("Update")');
    if (await updateBtn.isVisible()) {
      await updateBtn.click();
      await sleep(2000);
      const updatedBody = await page.textContent('body');
      // After update, the page should still have the queue table
      assert('Page reloaded after Update', updatedBody.includes('Queue'));
    }

  } catch (e) {
    console.error('\n  ❌ Test error:', e.message);
    results.failed++;
    results.errors.push({ name: 'Runtime error', detail: e.message });
  } finally {
    await browser.close();
  }

  // ===== Summary =====
  console.log(`\n${'='.repeat(40)}`);
  console.log(`Results: ${results.passed} passed, ${results.failed} failed`);
  if (results.errors.length > 0) {
    console.log('Failures:');
    results.errors.forEach(e => console.log(`  - ${e.name}: ${e.detail}`));
  }
  console.log(`${'='.repeat(40)}\n`);

  process.exit(results.failed > 0 ? 1 : 0);
}

run();