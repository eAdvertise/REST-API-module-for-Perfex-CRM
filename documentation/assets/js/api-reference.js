/* Progressive enhancements for the static API reference. */
(function () {
  'use strict';

  var filter = document.querySelector('[data-endpoint-filter]');
  var endpoints = Array.from(document.querySelectorAll('.api-endpoint'));
  var groups = Array.from(document.querySelectorAll('.api-group'));

  if (filter) {
    filter.addEventListener('input', function () {
      var query = filter.value.trim().toLowerCase();
      endpoints.forEach(function (endpoint) {
        endpoint.hidden = query && !endpoint.textContent.toLowerCase().includes(query);
      });
      groups.forEach(function (group) {
        group.hidden = !Array.from(group.querySelectorAll('.api-endpoint')).some(function (endpoint) {
          return !endpoint.hidden;
        });
      });
    });
  }

  document.addEventListener('click', function (event) {
    var copy = event.target.closest('[data-copy]');
    if (!copy) return;
    navigator.clipboard.writeText(copy.dataset.copy).then(function () {
      var original = copy.textContent;
      copy.textContent = 'Copied';
      setTimeout(function () { copy.textContent = original; }, 1200);
    });
  });
}());
