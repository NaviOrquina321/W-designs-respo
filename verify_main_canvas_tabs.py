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

        # Capture initial main canvas tab view
        page.screenshot(path="/tmp/admin_main_canvas_tab.png")

        # Click Manage Tutor Matching tab on main canvas
        page.click("button[data-main-tab='matching']")
        time.sleep(0.5)
        page.screenshot(path="/tmp/admin_main_canvas_matching.png")

        print("Main canvas tabs verification successful.")
        browser.close()
finally:
    server.terminate()
