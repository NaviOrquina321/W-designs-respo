import asyncio
from playwright.async_api import async_playwright

async def main():
    async with async_playwright() as p:
        browser = await p.chromium.launch(headless=True)
        page = await browser.new_page(viewport={"width": 1280, "height": 800})

        # 1. Test admin.php dedicated drawer
        print("Navigating to admin.php...")
        await page.goto("http://localhost:3000/admin.php")
        await page.wait_for_timeout(1000)

        # Click Students Drawer item in sidebar
        print("Opening dedicated students drawer...")
        await page.click("li[data-admin-nav='students'] a")
        await page.wait_for_timeout(800)
        await page.screenshot(path="/tmp/admin_dedicated_students_drawer.png")

        # 2. Test index.php
        print("Navigating to index.php...")
        await page.goto("http://localhost:3000/index.php")
        await page.wait_for_timeout(1000)

        await browser.close()
        print("Verification completed successfully!")

if __name__ == "__main__":
    asyncio.run(main())
