import requests
import random
import string

BASE_URL = "http://localhost:80/Grocessary%20Website"
REGISTER_URL = f"{BASE_URL}/pages/auth/register.php"
OTP_VERIFY_URL = f"{BASE_URL}/pages/auth/otp_verify.php"  # assumed endpoint for OTP verification
TIMEOUT = 30

def generate_random_email():
    return f"testuser_{''.join(random.choices(string.ascii_lowercase + string.digits, k=8))}@example.com"

def generate_random_name():
    return "Test User"

def generate_random_password():
    return "TestPass123!"

def test_user_registration_with_incorrect_otp():
    session = requests.Session()
    try:
        # Step 1: Submit registration form with valid data
        registration_data = {
            "name": generate_random_name(),
            "email": generate_random_email(),
            "password": generate_random_password(),
            "confirm_password": generate_random_password(), # to match password, we will fix this below
        }
        # Must match password and confirm_password
        registration_data["confirm_password"] = registration_data["password"]
        
        registration_headers = {
            "Content-Type": "application/x-www-form-urlencoded"
        }
        
        reg_resp = session.post(REGISTER_URL, data=registration_data, headers=registration_headers, timeout=TIMEOUT)
        # Check registration response - Assume registration redirects or gives success message or OTP challenge
        assert reg_resp.status_code == 200 or reg_resp.status_code == 302, "Registration POST failed or unexpected status code"
        
        # OTP challenge expected - we need to simulate OTP verification with incorrect OTP
        # Since no API schema is given for OTP verification endpoint or parameters,
        # we assume an OTP verify endpoint: /pages/auth/otp_verify.php that accepts POST with email and otp.
        # We submit an incorrect OTP and expect failure notification.
        
        # Use an obviously incorrect OTP
        incorrect_otp_payload = {
            "email": registration_data["email"],
            "otp": "000000"
        }
        otp_headers = {
            "Content-Type": "application/x-www-form-urlencoded"
        }
        
        otp_resp = session.post(OTP_VERIFY_URL, data=incorrect_otp_payload, headers=otp_headers, timeout=TIMEOUT)
        assert otp_resp.status_code == 200, "OTP verification request failed"
        
        # Check response content for OTP failure message
        # We expect something like "OTP verification failure" message in response body
        # The exact message is not specified, so we check case insensitive partial match
        content = otp_resp.text.lower()
        assert "otp verification failure" in content or "invalid otp" in content or "otp failed" in content, \
            "OTP verification failure message not found in response"
        
    finally:
        # Cleanup: delete user if possible
        # No API schema for user deletion provided; skip cleanup to avoid errors
        pass

test_user_registration_with_incorrect_otp()