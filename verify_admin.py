import asyncio
from playwright.async_api import async_playwright

async def run():
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page(viewport={"width": 1440, "height": 900})
        await page.goto("http://localhost:3000/admin.php", wait_until="networkidle")
        await page.wait_for_timeout(2000)

        # Take full page screenshot of admin page
        await page.screenshot(path="/tmp/admin_dashboard_apex.png")
        print("Admin screenshot saved to /tmp/admin_dashboard_apex.png")

        # Test tab switching
        await page.click('button[data-tab="tab-matching"]')
        await page.wait_for_timeout(500)
        await page.screenshot(path="/tmp/admin_matching_tab.png")

        await browser.close()

if __name__ == "__main__":
    asyncio.run(run())
