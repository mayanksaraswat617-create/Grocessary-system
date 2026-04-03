import requests
from bs4 import BeautifulSoup

BASE_URL = "http://localhost:80/Grocessary%20Website"
HOME_PAGE = "/pages/customer/home.php"
CART_ENDPOINT = "/cart/api.php"
TIMEOUT = 30

def test_customer_homepage_browse_and_add_to_cart():
    session = requests.Session()
    try:
        # Step 1: Browse featured products by loading the customer home page
        resp = session.get(BASE_URL + HOME_PAGE, timeout=TIMEOUT)
        assert resp.status_code == 200, "Failed to load customer home page"
        soup = BeautifulSoup(resp.text, "html.parser")

        # Assuming featured products are listed in elements with class "featured-product" and have data-product-id attributes
        featured_products = soup.select(".featured-product[data-product-id]")
        assert len(featured_products) > 0, "No featured products found on home page"

        # Pick first featured product ID
        product_id = featured_products[0]["data-product-id"]

        # Step 2: Use search/filter - assuming there's a search endpoint or filter parameters on home.php
        # Try a search with empty or minimal term to simulate refining results, e.g. search for first few chars of product name
        product_name = featured_products[0].get_text(strip=True)
        search_term = product_name[:3]  # first 3 chars of product name
        search_resp = session.get(BASE_URL+HOME_PAGE, params={"search": search_term}, timeout=TIMEOUT)
        assert search_resp.status_code == 200, "Search request failed"
        search_soup = BeautifulSoup(search_resp.text, "html.parser")

        # Validate that at least one product returned matches/refines results
        search_results = search_soup.select(".product-item")
        assert len(search_results) > 0, "No products found with search filter"

        # Step 3: Add an item to the cart
        # Assuming adding to cart is done via POST to /cart/api.php with data: product_id and quantity
        add_cart_payload = {"action": "add", "product_id": product_id, "quantity": "1"}
        add_cart_resp = session.post(BASE_URL + CART_ENDPOINT, data=add_cart_payload, timeout=TIMEOUT)
        assert add_cart_resp.status_code == 200, "Add to cart request failed"

        # Response should contain a json indicating success and updated cart info
        json_resp = None
        try:
            json_resp = add_cart_resp.json()
        except Exception:
            assert False, "Add to cart response is not valid JSON"

        assert json_resp.get("success") is True, "Add to cart failed in response"

        # Step 4: Verify cart updates accordingly
        # Assuming there's a GET to /cart/api.php?action=view to get current cart details
        cart_view_resp = session.get(BASE_URL + CART_ENDPOINT, params={"action": "view"}, timeout=TIMEOUT)
        assert cart_view_resp.status_code == 200, "View cart request failed"
        try:
            cart_data = cart_view_resp.json()
        except Exception:
            assert False, "View cart response is not valid JSON"

        # Verify product_id is in cart items with quantity >= 1
        items = cart_data.get("items")
        assert items is not None and isinstance(items, list) and len(items) > 0, "Cart is empty after adding product"
        product_in_cart = False
        for item in items:
            if str(item.get("product_id")) == str(product_id) and int(item.get("quantity", 0)) >= 1:
                product_in_cart = True
                break
        assert product_in_cart, "Added product is not found in cart or quantity incorrect"

    finally:
        # Cleanup: Remove the product from cart to restore state
        # Assuming delete is done by POST with action remove and product_id
        try:
            session.post(BASE_URL + CART_ENDPOINT, data={"action": "remove", "product_id": product_id}, timeout=TIMEOUT)
        except Exception:
            pass

test_customer_homepage_browse_and_add_to_cart()