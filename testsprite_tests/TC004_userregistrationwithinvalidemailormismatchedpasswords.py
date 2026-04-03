import requests

def test_user_registration_with_invalid_email_or_mismatched_passwords():
    base_url = "http://localhost:80/Grocessary%20Website/pages/auth/register.php"
    headers = {
        "Content-Type": "application/x-www-form-urlencoded"
    }
    timeout = 30

    test_data_cases = [
        # Invalid email formats
        {
            "name": "Test User",
            "email": "invalid-email-format",
            "password": "Password123!",
            "confirm_password": "Password123!"
        },
        {
            "name": "Test User",
            "email": "invalid@.com",
            "password": "Password123!",
            "confirm_password": "Password123!"
        },
        {
            "name": "Test User",
            "email": "user@invalid-domain",
            "password": "Password123!",
            "confirm_password": "Password123!"
        },
        # Mismatched passwords
        {
            "name": "Test User",
            "email": "validemail@example.com",
            "password": "Password123!",
            "confirm_password": "Password1234!"
        },
        {
            "name": "Test User",
            "email": "validemail@example.com",
            "password": "Pass123!",
            "confirm_password": "Pass321!"
        }
    ]

    invalid_email_msgs = [
        "invalid email",
        "enter a valid email",
        "email format",
        "email is not valid",
        "invalid email address",
        "email invalid",
        "email error"
    ]
    password_mismatch_msgs = [
        "passwords do not match",
        "password confirmation does not match",
        "confirm password does not match",
        "password mismatch"
    ]

    for data in test_data_cases:
        payload = {
            "name": data["name"],
            "email": data["email"],
            "password": data["password"],
            "confirm_password": data["confirm_password"]
        }
        try:
            response = requests.post(base_url, headers=headers, data=payload, timeout=timeout)
        except requests.RequestException as e:
            assert False, f"Request failed: {e}"

        assert response.status_code == 200, f"Unexpected status code: {response.status_code}"

        content = response.text.lower()

        if data["password"] != data["confirm_password"]:
            # Expect password mismatch error message
            assert any(msg in content for msg in password_mismatch_msgs), \
                f"Expected password mismatch error message for passwords '{data['password']}' and '{data['confirm_password']}'"
        else:
            # Expect invalid email error message
            assert any(msg in content for msg in invalid_email_msgs), \
                f"Expected invalid email error message for email '{data['email']}'"

test_user_registration_with_invalid_email_or_mismatched_passwords()
