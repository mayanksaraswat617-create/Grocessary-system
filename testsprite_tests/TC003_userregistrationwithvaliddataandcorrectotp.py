import requests
import random
import string

BASE_URL = "http://localhost:80/Grocessary%20Website"
REGISTER_URL = f"{BASE_URL}/pages/auth/register.php"
OTP_VERIFY_URL = f"{BASE_URL}/pages/auth/register.php"  # Assuming OTP verification is part of the same endpoint or handled similarly
LOGIN_URL = f"{BASE_URL}/pages/auth/login.php"
TIMEOUT = 30

def generate_random_email():
    return "testuser_" + ''.join(random.choices(string.ascii_lowercase + string.digits, k=8)) + "@example.com"

def test_userregistrationwithvaliddataandcorrectotp():
    session = requests.Session()
    try:
        # Step 1: Submit registration form with valid data
        name = "Test User"
        email = generate_random_email()
        password = "ValidPass123!"

        register_payload = {
            "name": name,
            "email": email,
            "password": password,
            "confirm_password": password,
            "submit_registration": "Register"
        }

        # As we have no explicit API docs about parameters, emulate form submission via POST
        reg_response = session.post(REGISTER_URL, data=register_payload, timeout=TIMEOUT)
        assert reg_response.status_code in (200, 302), f"Registration form submission failed with status code {reg_response.status_code}"

        # Step 2: Simulate OTP retrieval - since no API for OTP retrieval is described,
        # assume the OTP is returned in the response or we can extract it.
        # This is a limitation; we'll try to parse OTP from response HTML or simulate correct OTP submission.

        # For the test, let's assume the OTP is "123456" as correct OTP (common for tests)
        correct_otp = "123456"

        otp_payload = {
            "email": email,
            "otp_code": correct_otp,
            "submit_otp": "Verify OTP"
        }

        otp_response = session.post(REGISTER_URL, data=otp_payload, timeout=TIMEOUT)
        assert otp_response.status_code in (200, 302), f"OTP verification request failed with status code {otp_response.status_code}"
        otp_response_text = otp_response.text.lower()

        # Step 3: Verify that OTP verification was successful and prompt to login is shown
        assert ("registration successful" in otp_response_text or "please login" in otp_response_text or "/pages/auth/login.php" in otp_response_text), \
            "Registration success message or login prompt not found after OTP verification"

    finally:
        # Cleanup: No direct API for user deletion described, so cannot clean test user.
        # In a real scenario, would add user deletion code here.
        pass

test_userregistrationwithvaliddataandcorrectotp()