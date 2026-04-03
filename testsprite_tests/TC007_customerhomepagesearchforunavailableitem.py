import requests

def test_customerhomepagesearchforunavailableitem():
    base_url = "http://localhost:80/Grocessary%20Website"
    search_url = f"{base_url}/pages/customer/home.php"
    search_term = "nonexistentitem12345"
    timeout = 30

    try:
        # Simulate a GET request with search query parameter, assuming 'search' is the query parameter name
        params = {'search': search_term}
        response = requests.get(search_url, params=params, timeout=timeout)

        # Check that the request was successful
        assert response.status_code == 200, f"Expected status code 200, got {response.status_code}"

        # Validate that 'no results found' indication is present in the response content
        # Checking commonly used phrases for no results
        content = response.text.lower()
        no_results_indicators = [
            "no results found",
            "no items found",
            "no matching products",
            "nothing found",
            "no results match your search"
        ]
        assert any(indicator in content for indicator in no_results_indicators), \
            "No results indication not found in the response content"

    except requests.RequestException as e:
        assert False, f"HTTP request failed: {e}"

test_customerhomepagesearchforunavailableitem()