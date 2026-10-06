import subprocess
import time
from playwright.sync_api import sync_playwright

# Start PHP server
server = subprocess.Popen(["php", "-S", "127.0.0.1:8088"])
time.sleep(1.5)

try:
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()
        page.goto("http://127.0.0.1:8088/admin.php")
        page.wait_for_selector(".admin-layout")

        # Capture Dashboard View
        page.screenshot(path="/tmp/admin_inline_dashboard.png")

        # Click Students menu
        page.click("li[data-admin-nav='students'] a")
        page.wait_for_selector("#admin-view-students")
        time.sleep(0.5)
        page.screenshot(path="/tmp/admin_inline_students.png")

        # Click Tutor Matching menu
        page.click("li[data-admin-nav='matching'] a")
        page.wait_for_selector("#admin-view-matching")
        time.sleep(0.5)
        page.screenshot(path="/tmp/admin_inline_matching.png")

        print("Inline navigation verification successfully completed.")
        browser.close()
finally:
    server.terminate()
