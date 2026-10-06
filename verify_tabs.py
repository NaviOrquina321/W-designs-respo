import subprocess
import time
from playwright.sync_api import sync_playwright

server = subprocess.Popen(["php", "-S", "127.0.0.1:8088"])
time.sleep(1.5)

try:
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()
        page.goto("http://127.0.0.1:8088/admin.php")
        page.wait_for_selector(".admin-layout")

        # Click Students drawer button
        page.click("li[data-admin-nav='students'] a")
        page.wait_for_selector("#admin-master-drawer:not(.hidden)")
        time.sleep(0.5)
        page.screenshot(path="/tmp/admin_drawer_tab_students.png")

        # Click Manage Tutor Matching tab inside drawer
        page.click("button[data-drawer-tab='matching']")
        time.sleep(0.5)
        page.screenshot(path="/tmp/admin_drawer_tab_matching.png")

        print("Drawer tabs verification successful.")
        browser.close()
finally:
    server.terminate()
