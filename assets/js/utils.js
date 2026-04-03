/* ============================================================
   GROCEESARY – Utils.js
   Toast, debounce, formatters
   ============================================================ */

// ---- Currency Formatter ------------------------------------
function formatCurrency(amount) {
  return '₹' + parseFloat(amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

// ---- Date Formatter ----------------------------------------
function formatDate(dateStr, options = {}) {
  const defaults = { day: '2-digit', month: 'short', year: 'numeric' };
  const opts = Object.assign(defaults, options);
  return new Date(dateStr).toLocaleDateString('en-IN', opts);
}

function timeAgo(dateStr) {
  const diff = (Date.now() - new Date(dateStr)) / 1000;
  if (diff < 60)    return 'just now';
  if (diff < 3600)  return Math.floor(diff / 60) + 'm ago';
  if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
  return Math.floor(diff / 86400) + 'd ago';
}

// ---- Debounce ----------------------------------------------
function debounce(fn, delay = 300) {
  let timer;
  return function(...args) {
    clearTimeout(timer);
    timer = setTimeout(() => fn.apply(this, args), delay);
  };
}

// ---- Toast Notifications -----------------------------------
(function () {
  function ensureContainer() {
    let c = document.getElementById('toast-container');
    if (!c) { c = document.createElement('div'); c.id = 'toast-container'; document.body.appendChild(c); }
    return c;
  }

  window.showToast = function(message, type = 'info', title = '', duration = 4000) {
    const container = ensureContainer();
    const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    const t = document.createElement('div');
    t.className = `toast toast-${type}`;
    t.innerHTML = `
      <span class="toast-icon">${icons[type] || icons.info}</span>
      <div class="toast-content">
        ${title ? `<div class="toast-title">${title}</div>` : ''}
        <div class="toast-msg">${message}</div>
      </div>`;
    container.appendChild(t);
    setTimeout(() => {
      t.style.animation = 'fadeOut 0.3s ease forwards';
      setTimeout(() => t.remove(), 300);
    }, duration);
  };
})();

// ---- Confirm Dialog ----------------------------------------
window.confirmAction = function(message, onConfirm) {
  if (window.confirm(message)) onConfirm();
};

// ---- Truncate Text -----------------------------------------
function truncate(str, maxLen = 60) {
  return str.length > maxLen ? str.slice(0, maxLen) + '…' : str;
}

// ---- Capitalize --------------------------------------------
function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

// ---- Get URL Param -----------------------------------------
function getParam(name) {
  return new URLSearchParams(window.location.search).get(name);
}

// ---- Smooth Scroll (fallback) ------------------------------
document.addEventListener('DOMContentLoaded', () => {
  // Close dropdowns on outside click
  document.addEventListener('click', (e) => {
    document.querySelectorAll('.dropdown.open').forEach(d => {
      if (!d.contains(e.target)) d.classList.remove('open');
    });
  });

  // Mobile sidebar
  const sidebarToggle = document.getElementById('sidebar-toggle');
  const sidebar       = document.getElementById('sidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('open'));
  }

  // Navbar user dropdown
  const avatarBtn     = document.getElementById('user-avatar-btn');
  const userDropdown  = document.getElementById('user-dropdown');
  if (avatarBtn && userDropdown) {
    avatarBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      userDropdown.closest('.dropdown').classList.toggle('open');
    });
  }
});
