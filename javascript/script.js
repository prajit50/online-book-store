document.addEventListener('DOMContentLoaded', function () {
  var navButton = document.querySelector('.nav-toggle'), nav = document.querySelector('.main-nav');
  if (navButton && nav) navButton.addEventListener('click', function () { nav.classList.toggle('open'); });
  var sideButton = document.querySelector('.sidebar-toggle'), sidebar = document.querySelector('.sidebar');
  if (sideButton && sidebar) sideButton.addEventListener('click', function () { sidebar.classList.toggle('open'); });
  document.querySelectorAll('form[data-validate]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      var password = form.querySelector('[name=password]'), confirm = form.querySelector('[name=confirm_password]');
      if (password && confirm && password.value !== confirm.value) { e.preventDefault(); alert('Passwords do not match.'); }
    });
  });
});
