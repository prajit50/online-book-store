document.addEventListener('DOMContentLoaded', function () 
{
  var navButton = document.querySelector('.nav-toggle'), nav = document.querySelector('.main-nav');

  if (navButton && nav) navButton.addEventListener('click', function ()
     { nav.classList.toggle('open'); });

  var sideButton = document.querySelector('.sidebar-toggle'), sidebar = document.querySelector('.sidebar');

  if (sideButton && sidebar) sideButton.addEventListener('click', function () 
    { sidebar.classList.toggle('open'); });

  document.querySelectorAll('.password-toggle').forEach(function (button) {
    button.addEventListener('click', function () {
      var field = document.getElementById(button.getAttribute('aria-controls'));
      if (!field) return;

      var showPassword = field.type === 'password';
      field.type = showPassword ? 'text' : 'password';
      button.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');
      button.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
      button.querySelector('[data-eye-open]').hidden = showPassword;
      button.querySelector('[data-eye-closed]').hidden = !showPassword;
      field.focus();
    });
  });

  document.querySelectorAll('form[data-validate]').forEach(function (form) 
  {
    var registrationFields = form.matches('[data-registration-form]') ? {
      name: form.querySelector('[name=name]'),
      email: form.querySelector('[name=email]'),
      phone: form.querySelector('[name=phone]'),
      password: form.querySelector('[name=password]'),
      confirm: form.querySelector('[name=confirm_password]')
    } : null;
    var emailTimer;
    var emailRequest;
    var emailAvailable = false;

    function showFieldError(field, message) {
      var error = document.getElementById(field.getAttribute('aria-describedby'));
      if (!error) return;

      error.textContent = message;
      error.hidden = !message;
      field.setAttribute('aria-invalid', message ? 'true' : 'false');
    }

    function validateField(field, value) {
      var trimmedValue = value.trim();
      if (!trimmedValue) return field.required ? 'This field is required.' : '';

      switch (field.name) {
        case 'name':
          return /^[\p{L}][\p{L}\p{M}\s.'-]*$/u.test(trimmedValue)
            ? ''
            : 'Name can only contain letters, spaces, and basic punctuation. Numbers are not allowed.';
        case 'email':
          return /^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/.test(trimmedValue)
            ? ''
            : 'Enter a valid email address.';
        case 'phone':
          return /^[0-9]{10}$/.test(trimmedValue) ? '' : 'Phone number must be exactly 10 digits.';
        case 'password':
          return /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(value)
            ? ''
            : 'Password must be at least 8 characters and include uppercase, lowercase, number, and special character.';
        case 'confirm_password':
          return value === registrationFields.password.value ? '' : 'Passwords do not match.';
        default:
          return '';
      }
    }

    function validateEmailAvailability() {
      var emailField = registrationFields.email;
      var formatError = validateField(emailField, emailField.value);
      if (formatError || !emailField.value.trim()) {
        emailAvailable = false;
        showFieldError(emailField, formatError);
        return Promise.resolve(!formatError);
      }

      if (emailRequest) emailRequest.abort();
      emailRequest = new AbortController();
      emailAvailable = false;
      showFieldError(emailField, 'Checking email availability…');
      var body = new URLSearchParams({ email: emailField.value.trim() });
      var endpoint = new URL(form.dataset.emailCheck, window.location.href);

      return fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: body.toString(),
        signal: emailRequest.signal
      })
        .then(function (response) {
          if (!response.ok) throw new Error('Email validation request failed.');
          return response.json();
        })
        .then(function (result) {
          if (emailField.value.trim() !== body.get('email')) return false;
          emailAvailable = result.valid;
          showFieldError(emailField, result.message);
          return result.valid;
        })
        .catch(function (error) {
          if (error.name === 'AbortError') return false;
          emailAvailable = false;
          showFieldError(emailField, 'Could not check email availability. Please try again.');
          return false;
        });
    }

    if (registrationFields) {
      Object.keys(registrationFields).forEach(function (key) {
        var field = registrationFields[key];
        if (!field) return;

        field.addEventListener('input', function () {
          if (field === registrationFields.email) {
            clearTimeout(emailTimer);
            if (emailRequest) emailRequest.abort();
            emailAvailable = false;
            showFieldError(field, validateField(field, field.value));

            if (!validateField(field, field.value) && field.value.trim()) {
              emailTimer = setTimeout(validateEmailAvailability, 350);
            }
          } else {
            showFieldError(field, validateField(field, field.value));
          }

          if (field === registrationFields.password && registrationFields.confirm.value) {
            showFieldError(registrationFields.confirm, validateField(registrationFields.confirm, registrationFields.confirm.value));
          }
        });

        field.addEventListener('blur', function () {
          if (field === registrationFields.email && !validateField(field, field.value) && field.value.trim()) {
            validateEmailAvailability();
          } else if (field !== registrationFields.email) {
            showFieldError(field, validateField(field, field.value));
          }
        });
      });
    }

    form.addEventListener('submit', function (e) 
    {
      var name = form.querySelector('[name=name]');
      var email = form.querySelector('[name=email]');
      var password = form.querySelector('[name=password]');
      var confirm = form.querySelector('[name=confirm_password]');
      var phone = form.querySelector('[name=phone]');

      function reject(message, field) {
        e.preventDefault();
        if (field && registrationFields) {
          showFieldError(field, message);
          field.focus();
        } else {
          alert(message);
          if (field) field.focus();
        }
      }

      if (name && !/^[\p{L}][\p{L}\p{M}\s.'-]*$/u.test(name.value.trim())) {
        reject('Name can only contain letters, spaces, and basic punctuation. Numbers are not allowed.', name);
        return;
      }

      if (email && !/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}$/.test(email.value.trim())) {
        reject('Enter a valid email address.', email);
        return;
      }

      if (phone && !/^[0-9]{10}$/.test(phone.value.trim())) {
        reject('Phone number must be exactly 10 digits.', phone);
        return;
      }

      if (password && confirm && !/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(password.value)) {
        reject('Password must be at least 8 characters and include uppercase, lowercase, number, and special character.', password);
        return;
      }

      if (password && confirm && password.value !== confirm.value) {
        reject('Passwords do not match.', confirm);
        return;
      }

      if (registrationFields) {
        e.preventDefault();
        clearTimeout(emailTimer);
        validateEmailAvailability().then(function (valid) {
          if (valid) form.submit();
          else registrationFields.email.focus();
        });
      }
    });
  });
});
