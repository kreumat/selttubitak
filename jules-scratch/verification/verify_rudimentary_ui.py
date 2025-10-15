from playwright.sync_api import Page, expect
import os

def test_rudimentary_ui(page: Page):
    """
    This test verifies that the index.php page has a rudimentary, unstyled look.
    """
    # 1. Arrange: Go to the index.php page.
    # The base URL will be the current working directory.
    base_url = "file://" + os.getcwd()
    page.goto(f"{base_url}/index.php")

    # 2. Assert: Check for the presence of some key unstyled elements.
    # We expect to see a simple "Board Game Generator" heading.
    expect(page.get_by_role("heading", name="Board Game Generator")).to_be_visible()

    # We expect to see a button to go to the next step.
    expect(page.get_by_role("button", name="Go to the Next Step: Add Text")).to_be_visible()

    # 3. Screenshot: Capture the final result for visual verification.
    page.screenshot(path="jules-scratch/verification/verification.png")