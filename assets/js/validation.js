/* ============================================================
   GROCEESARY – Validation.js
   ============================================================ */

const Validate = {
  email(val) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim());
  },
  phone(val) {
    return /^[6-9]\d{9}$/.test(val.trim());
  },
  password(val) {
    // min 8 chars, 1 uppercase, 1 lowercase, 1 number
    return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/.test(val);
  },
  required(val) {
    return val !== null && val !== undefined && String(val).trim() !== '';
  },
  minLength(val, min) {
    return String(val).trim().length >= min;
  },
  maxLength(val, max) {
    return String(val).trim().length <= max;
  },
  numeric(val) {
    return !isNaN(parseFloat(val)) && isFinite(val);
  },
  positiveNumber(val) {
    return this.numeric(val) && parseFloat(val) > 0;
  },
  pincode(val) {
    return /^\d{6}$/.test(val.trim());
  },
  pan(val) {
    return /^[A-Z]{5}[0-9]{4}[A-Z]{1}$/.test(val.trim().toUpperCase());
  },
  aadhar(val) {
    return /^\d{12}$/.test(val.replace(/\s/g, ''));
  },
  ifsc(val) {
    return /^[A-Z]{4}0[A-Z0-9]{6}$/.test(val.trim().toUpperCase());
  },

  // Validate entire form: { fieldId: { rules, messages } }
  form(rules) {
    let valid = true;
    Object.entries(rules).forEach(([fieldId, config]) => {
      const field = document.getElementById(fieldId);
      if (!field) return;
      const val   = field.value;
      const errEl = document.getElementById(fieldId + '-error') || createErrorEl(fieldId);
      let errMsg  = '';

      for (const [rule, param] of Object.entries(config.rules || {})) {
        if (rule === 'required'       && !Validate.required(val))         { errMsg = config.messages?.required   || 'This field is required.'; break; }
        if (rule === 'email'          && !Validate.email(val))            { errMsg = config.messages?.email      || 'Enter a valid email.'; break; }
        if (rule === 'phone'          && !Validate.phone(val))            { errMsg = config.messages?.phone      || 'Enter a valid 10-digit phone number.'; break; }
        if (rule === 'password'       && !Validate.password(val))         { errMsg = config.messages?.password   || 'Password must be 8+ chars with uppercase, lowercase & number.'; break; }
        if (rule === 'minLength'      && !Validate.minLength(val, param)) { errMsg = config.messages?.minLength  || `Minimum ${param} characters required.`; break; }
        if (rule === 'positiveNumber' && !Validate.positiveNumber(val))   { errMsg = config.messages?.positiveNumber || 'Enter a valid positive number.'; break; }
        if (rule === 'pincode'        && !Validate.pincode(val))          { errMsg = config.messages?.pincode    || 'Enter a valid 6-digit pincode.'; break; }
        if (rule === 'match') {
          const other = document.getElementById(param);
          if (other && val !== other.value) { errMsg = config.messages?.match || 'Fields do not match.'; break; }
        }
      }

      if (errMsg) {
        valid = false;
        field.classList.add('error');
        errEl.textContent = errMsg;
        errEl.style.display = 'block';
      } else {
        field.classList.remove('error');
        errEl.textContent = '';
        errEl.style.display = 'none';
      }
    });
    return valid;
  }
};

function createErrorEl(fieldId) {
  const el = document.createElement('div');
  el.id = fieldId + '-error';
  el.className = 'form-error';
  const field = document.getElementById(fieldId);
  if (field) field.insertAdjacentElement('afterend', el);
  return el;
}

// Password strength meter
function passwordStrength(val) {
  let score = 0;
  if (val.length >= 8)         score++;
  if (/[A-Z]/.test(val))       score++;
  if (/[a-z]/.test(val))       score++;
  if (/\d/.test(val))          score++;
  if (/[^A-Za-z0-9]/.test(val)) score++;
  const labels = ['', 'Very Weak', 'Weak', 'Fair', 'Strong', 'Very Strong'];
  const colors = ['', '#e74c3c', '#e67e22', '#f1c40f', '#2ecc71', '#27ae60'];
  return { score, label: labels[score] || '', color: colors[score] || '' };
}
