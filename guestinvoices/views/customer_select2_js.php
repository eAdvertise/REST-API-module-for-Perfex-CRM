<?php
// modules/guestinvoices/views/customer_select2_js.php
// Αντικατάσταση του Customer select με Select2 + τοπικά assets ή CDN fallback.
?>
(function(){
  "use strict";

  // ---------- Base URLs ----------
  var GI_ADMIN_URL = (typeof admin_url !== 'undefined' && admin_url)
      ? admin_url
      : (function(){
          var $b = $('body'); var base = $b.attr('data-admin-url');
          if (base) return base.endsWith('/') ? base : (base + '/');
          var origin = location.origin || (location.protocol + '//' + location.host);
          return origin + '/admin/';
        })();

  var GI_SITE_BASE   = GI_ADMIN_URL.replace(/\/admin\/?$/,'/');
  var GI_MODULES_BASE = GI_SITE_BASE + 'modules/guestinvoices/';

  // Τοπικά μονοπάτια
  var LOCAL_CSS = GI_MODULES_BASE + 'assets/select2/select2.min.css';
  var LOCAL_JS  = GI_MODULES_BASE + 'assets/select2/select2.full.min.js';

  // CDN fallback (Select2 v4.1.0-rc.0 είναι η πιο σταθερή στα CDNs)
  var CDN_CSS = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css';
  var CDN_JS  = 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js';

  var CLIENT_SELECTORS = [
    'select[name="clientid"]',
    'select[name="customerid"]',
    '#clientid',
    '#customerid'
  ];

  function findCustomerSelect(){
    for (var i=0;i<CLIENT_SELECTORS.length;i++){
      var $s = $(CLIENT_SELECTORS[i]+':visible').first();
      if ($s.length) return $s;
    }
    for (var j=0;j<CLIENT_SELECTORS.length;j++){
      var $h = $(CLIENT_SELECTORS[j]).first();
      if ($h.length) return $h;
    }
    return $();
  }

  // Απενεργοποίηση bootstrap-select & core bindings
  function neutralizePerfexFor($sel){
    if (!$sel || !$sel.length) return;
    if ($sel.hasClass('selectpicker') && typeof $sel.selectpicker === 'function') {
      try { $sel.selectpicker('destroy'); } catch(e){}
      $sel.removeClass('selectpicker');
      $sel.siblings('.bootstrap-select').remove();
    }
    $sel.removeClass('ajax-search');
    $sel.removeAttr('data-live-search data-width data-none-selected-text data-actions-box data-title data-content data-subtext');
    $sel.addClass('gi-select2-customer').attr('data-gi-select2','1');
    $sel.off('.bs.select change');
  }

  // -------- Asset loaders με fallback ----------
  function loadCssWithFallback(localHref, cdnHref, cb){
    var l = document.createElement('link');
    l.rel = 'stylesheet'; l.href = localHref;
    var done = false;
    l.onload = function(){ if (!done){ done=true; cb(true); } };
    l.onerror = function(){
      // Προσπάθησε CDN
      var l2 = document.createElement('link');
      l2.rel='stylesheet'; l2.href = cdnHref;
      l2.onload = function(){ if (!done){ done=true; cb(true); } };
      l2.onerror= function(){ if (!done){ done=true; cb(false); } };
      document.head.appendChild(l2);
    };
    document.head.appendChild(l);
  }

  function loadScriptWithFallback(localSrc, cdnSrc, cb){
    var s = document.createElement('script');
    s.src = localSrc;
    var done = false;
    s.onload = function(){ if (!done){ done=true; cb(true); } };
    s.onerror = function(){
      // Προσπάθησε CDN
      var s2 = document.createElement('script');
      s2.src = cdnSrc;
      s2.onload = function(){ if (!done){ done=true; cb(true); } };
      s2.onerror= function(){ if (!done){ done=true; cb(false); } };
      document.head.appendChild(s2);
    };
    document.head.appendChild(s);
  }

  function ensureSelect2Assets(cb){
    if ($.fn.select2) { cb(); return; }
    var needed = 2, ok = 0, done = 0;
    function next(success){ done++; if (success) ok++; if (done>=needed) cb(ok===needed); }
    loadCssWithFallback(LOCAL_CSS, CDN_CSS, next);
    loadScriptWithFallback(LOCAL_JS, CDN_JS, next);
  }

  function waitForSelect2(fn, tries){
    tries = tries || 30;
    if ($.fn.select2) { fn(); return; }
    if (tries <= 0) return;
    setTimeout(function(){ waitForSelect2(fn, tries-1); }, 120);
  }

  function initCustomerSelect2(){
    var $sel = findCustomerSelect();
    if (!$sel.length) return;
    if ($sel.data('select2')) return;

    neutralizePerfexFor($sel);

    var current = $sel.val();
    if ($sel.find('option[value=""]').length === 0) {
      $sel.prepend($('<option value=""></option>'));
    }
    $sel.find('option:not([value=""])').remove();

    $sel.select2({
      width: '100%',
      placeholder: 'Select customer',
      allowClear: true,
      ajax: {
        url: GI_ADMIN_URL + 'guestinvoices/rel_customers_with_email',
        dataType: 'json',
        delay: 150,
        data: function (params) { return { q: params.term || '', page: params.page || 1 }; },
        processResults: function (data, params) {
          params.page = params.page || 1;
          return {
            results: (data && data.results) ? data.results : [],
            pagination: { more: data && data.pagination ? !!data.pagination.more : false }
          };
        },
        cache: true
      },
      templateResult: function (item) {
        if (!item.id) return item.text;
        var company = (item.company || item.text || '').toString();
        var email   = (item.email || '').toString();
        if (!email) return company;
        return $('<span>'+ $('<div>').text(company).html() +' <small class="text-muted">'+ $('<div>').text(email).html() +'</small></span>');
      },
      templateSelection: function (item) {
        if (!item.id) return item.text || '';
        var company = (item.company || item.text || '').toString();
        var email   = (item.email || '').toString();
        return email ? (company + ' — ' + email) : company;
      },
      escapeMarkup: function (m) { return m; }
    });

    if (current && String(current).length) {
      $.get(GI_ADMIN_URL + 'guestinvoices/rel_customers_with_email', { q:'', page:1 }, function(data){
        var item = null;
        if (data && data.results) {
          for (var i=0;i<data.results.length;i++){
            if (String(data.results[i].id) === String(current)) { item = data.results[i]; break; }
          }
        }
        if (!item) {
          $sel.val(current).trigger('change');
        } else {
          var opt = new Option(item.text, item.id, true, true);
          $sel.append(opt).trigger('change');
        }
      }, 'json');
    }

    // Προστασία ενάντια σε re-wrap από bootstrap-select
    var mo = new MutationObserver(function(muts){
      var need = false;
      muts.forEach(function(m){
        if (m.addedNodes) {
          for (var i=0;i<m.addedNodes.length;i++){
            var n = m.addedNodes[i];
            if (!(n instanceof HTMLElement)) continue;
            if (n.matches && n.matches('.bootstrap-select')) need = true;
            if (!need && n.querySelector && n.querySelector('.bootstrap-select')) need = true;
          }
        }
      });
      if (need) neutralizePerfexFor($sel);
    });
    mo.observe(document.body, { childList:true, subtree:true });
  }

  // Όταν δημιουργείται νέος guest (από το guest_customer_js): πέρασέ τον στο Select2
  $(document).on('gi.client.created', function(e, payload){
    var $sel = findCustomerSelect();
    if (!$sel.length) return;
    if ($sel.data('select2')) {
      var text = (payload.company || '') + (payload.email ? (' — ' + payload.email) : '');
      var opt = new Option(text, payload.client_id, true, true);
      $sel.append(opt).trigger('change');
    }
  });

  // Boot
  function boot(){
    ensureSelect2Assets(function(){
      waitForSelect2(initCustomerSelect2, 40);
    });
  }
  $(boot);
  $(document).on('app.content.loaded', function(){ setTimeout(boot,80); });

})();
