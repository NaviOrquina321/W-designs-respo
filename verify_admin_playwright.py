import asyncio
from playwright.async_api import async_playwright

async def verify_admin():
    async with async_playwright() as p:
        browser = await p.chromium.launch()
        context = await browser.new_context(viewport={'width': 1280, 'height': 900})
        page = await context.new_page()

        # Load admin portal
        await page.goto('http://localhost:3000/admin.php')
        await page.wait_for_selector('.admin-layout')
        await asyncio.sleep(1)

        # Screenshot Dashboard overview
        await page.screenshot(path='/home/jules/verification/screenshots/admin_dashboard.png', full_page=True)

        # Switch to Students tab
        await page.click('li[data-admin-nav="students"] a')
        await asyncio.sleep(0.5)
        await page.screenshot(path='/home/jules/verification/screenshots/admin_students.png', full_page=True)

        # Switch to Tutor Matching tab
        await page.click('li[data-admin-nav="matching"] a')
        await asyncio.sleep(0.5)
        await page.screenshot(path='/home/jules/verification/screenshots/admin_matching.png', full_page=True)

        # Mobile viewport test
        mobile_context = await browser.new_context(viewport={'width': 375, 'height': 812})
        mobile_page = await mobile_context.new_page()
        await mobile_page.goto('http://localhost:3000/admin.php')
        await mobile_page.wait_for_selector('.admin-layout')
        await asyncio.sleep(1)
        await mobile_page.screenshot(path='/home/jules/verification/screenshots/admin_mobile.png', full_page=True)

        await browser.close()
        print("Admin verification screenshots captured successfully.")

if __name__ == '__main__':
    asyncio.run(verify_admin())
