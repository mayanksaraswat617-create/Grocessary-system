import requests

def test_userloginwithvalidcredentials():
    base_url = "http://localhost:80/Grocessary%20Website"
    login_url = f"{base_url}/pages/auth/login.php"
    home_url = f"{base_url}/pages/customer/home.php"

    session = requests.Session()
    try:
        # Step 1: Get login page to get any required cookies or tokens (if any)
        resp_get = session.get(login_url, timeout=30)
        resp_get.raise_for_status()

        # Prepare login data - using valid credentials (replace with actual valid credentials)
        login_data = {
            "email": "validuser@example.com",
            "password": "validpassword"
        }

        headers = {
            "Content-Type": "application/x-www-form-urlencoded"
        }

        # Step 2: Post login form
        resp_post = session.post(login_url, data=login_data, headers=headers, timeout=30)
        resp_post.raise_for_status()

        # We expect the post response to be either a redirect or the logged in home page
        # If redirect, follow it, else check content
        if resp_post.status_code in (302, 303):
            location = resp_post.headers.get("Location", "")
            assert "/pages/customer/home.php" in location, f"Expected redirect to customer home page but Location was {location}"
            resp_home = session.get(f"{base_url}{location}", timeout=30)
            resp_home.raise_for_status()
            final_url = resp_home.url
        else:
            final_url = resp_post.url

        assert final_url.endswith("/pages/customer/home.php") or "/pages/customer/home.php" in final_url

    except requests.RequestException as e:
        assert False, f"HTTP request failed: {e}"


test_userloginwithvalidcredentials()