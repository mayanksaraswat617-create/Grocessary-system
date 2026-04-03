/* ============================================================
   GROCEESARY – API Client
   fetch() wrapper with CSRF + JSON handling
   ============================================================ */

const API = {
  baseUrl: '',  // determined at runtime from meta tag

  _getBaseUrl() {
    if (!this.baseUrl) {
      const meta = document.querySelector('meta[name="base-url"]');
      this.baseUrl = meta ? meta.getAttribute('content') : '';
    }
    return this.baseUrl;
  },

  _getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
  },

  async _request(method, endpoint, data = null) {
    const url = this._getBaseUrl() + endpoint;
    const headers = {
      'Content-Type': 'application/json',
      'X-CSRF-Token':  this._getCsrfToken(),
      'X-Requested-With': 'XMLHttpRequest',
    };

    const options = { method, headers };
    if (data && method !== 'GET') options.body = JSON.stringify(data);

    try {
      const res  = await fetch(url, options);
      const json = await res.json();
      if (!res.ok) throw new Error(json.message || 'Request failed');
      return json;
    } catch (err) {
      showToast(err.message || 'Network error. Please try again.', 'error');
      throw err;
    }
  },

  get(endpoint)          { return this._request('GET',    endpoint); },
  post(endpoint, data)   { return this._request('POST',   endpoint, data); },
  put(endpoint, data)    { return this._request('PUT',    endpoint, data); },
  delete(endpoint, data) { return this._request('DELETE', endpoint, data); },
};

// ---- Convenience API helpers --------------------------------
const CartAPI = {
  async add(productId, quantity = 1) {
    return API.post('/pages/api/cart.php', { action: 'add', product_id: productId, quantity });
  },
  async update(productId, quantity) {
    return API.post('/pages/api/cart.php', { action: 'update', product_id: productId, quantity });
  },
  async remove(productId) {
    return API.post('/pages/api/cart.php', { action: 'remove', product_id: productId });
  },
  async get() {
    return API.post('/pages/api/cart.php', { action: 'get' });
  },
};

const NotificationsAPI = {
  async getUnread() {
    return API.post('/pages/api/notifications.php', { action: 'get_unread' });
  },
  async markRead(id) {
    return API.post('/pages/api/notifications.php', { action: 'mark_read', id });
  },
};
