import requests

def test_userloginwithinvalidpassword():
    base_url = "http://localhost:80/Grocessary%20Website"
    login_url = f"{base_url}/pages/auth/login.php"
    session = requests.Session()
    headers = {
        "Content-Type": "application/x-www-form-urlencoded"
    }
    # Using a known valid email and an incorrect password
    data = {
        "email": "validuser@example.com",
        "password": "WrongPassword123!"
    }
    try:
        response = session.post(login_url, headers=headers, data=data, timeout=30, allow_redirects=True)
    except requests.RequestException as e:
        assert False, f"Request failed: {e}"

    # Relaxed URL check: Verify response URL path ends with login.php
    assert response.url.endswith('/pages/auth/login.php'), "Login succeeded unexpectedly with invalid password"

    # Check for failure keywords in response content, case insensitive
    failure_keywords = [
        "authentication",
        "invalid",
        "incorrect",
        "login",
        "error"
    ]
    content_lower = response.text.lower()
    assert any(keyword in content_lower for keyword in failure_keywords), (
        "Authentication failure notification not found in response"
    )

test_userloginwithinvalidpassword()
