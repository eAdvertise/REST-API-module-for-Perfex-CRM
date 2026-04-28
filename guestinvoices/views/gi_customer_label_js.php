<?php // Στον επιλεγμένο πελάτη, αλλάζει το OPTION text σε "Company — email" και κάνει refresh, ώστε να φαίνεται σίγουρα στο label. ?>
(function(){
  "use strict";

  // ---- admin_url fallback ----
  var GI_ADMIN_URL = (typeof admin_url !== 'undefined' && admin_url)
      ? admin_url
      : (function(){
          var $b = $('body');
          var base = $b.attr('data-admin-url');
          if (base) return base.endsWith('/') ? base : (base + '/');
          var origin = window.location.origin || (window.location.protocol + '//' + window.location.host);
          return origin + '/admin/';
        })();

  var CLIENT_SELECTORS = [
    'select[name="clientid"]',
    'select[name="customerid"]',
    '#clientid',
    '#customerid'
  ];

  function findSelect(){
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

  // Βοηθητικό: οριστικοποιεί το κείμενο του option και κάνει refresh
  function setOptionTextAndRefresh($select, id, company, email){
    var $opt = $select.find('option[value="'+id+'"]');
    if (!$opt.length) return;

    var original = $opt.data('gi-original-text');
    if (!original) {
      // κρατάμε το αρχικό κείμενο για πιθανό revert στο μέλλον
      $opt.data('gi-original-text', $opt.text());
    }

    var finalText = $.trim(company || $opt.text() || '');
    if (email) finalText = finalText + ' — ' + $.trim(email);

    $opt.text(finalText);

    if ($select.hasClass('selectpicker')) {
      var cur = $select.selectpicker('val') || $select.val();
      $select.selectpicker('refresh');
      if (cur) $select.selectpicker('val', cur);
    }
  }

  // Φέρνει email μόνο για τον επιλεγμένο id και ενημερώνει το option text
  function enrichSelectedOption($select){
    if (!$select || !$select.length) return;
    var val = $select.hasClass('selectpicker') ? ($select.selectpicker('val') || $select.val()) : $select.val();
    if (!val) return;

    var $opt    = $select.find('option[value="'+val+'"]');
    var company = $.trim($opt.text());

    // Αν ήδη φαίνεται " — " μέσα στο text, θεώρησέ το enriched και σταμάτα
    if (company.indexOf(' — ') !== -1) return;

    // Στοχευμένο AJAX για 1 id
    $.get(GI_ADMIN_URL + 'guestinvoices/ajax_clients_primary_emails', { ids: String(val) })
      .done(function(resp){
        var r; try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; } catch(e){ r = null; }
        if (!r || !r.success || !r.items || !r.items.length) return;

        var it   = r.items[0] || {};
        var comp = $.trim(it.company || company || '');
        var mail = $.trim(it.email || '');

        setOptionTextAndRefresh($select, val, comp, mail);
      });
  }

  // Όταν αλλάζει πελάτης -> εμπλούτισε τον επιλεγμένο
  $(document).on('changed.bs.select', CLIENT_SELECTORS.join(','), function(){
    enrichSelectedOption($(this));
  });

  // Μετά από Perfex AJAX refill (get_relation_data για customer) -> εμπλούτισε τον τρέχον επιλεγμένο
  $(document).ajaxSuccess(function(event, xhr, settings){
    try{
      var url  = (settings.url  || '').toLowerCase();
      var data = (settings.data || '').toLowerCase();
      var combo = url + '&' + data;

      if (combo.indexOf('/admin/misc/get_relation_data') !== -1 &&
          (combo.indexOf('rel_type=customer') !== -1 ||
           combo.indexOf('relation_type=customer') !== -1 ||
           combo.indexOf('type=customer') !== -1)) {
        setTimeout(function(){
          $(CLIENT_SELECTORS.join(',')).each(function(){
            enrichSelectedOption($(this));
          });
        }, 80);
      }
    }catch(e){}
  });

  // Αρχικοποίηση (πρώτο render & SPA reload)
  function boot(){
    var $s = findSelect();
    if ($s.length) enrichSelectedOption($s);
  }
  $(function(){ setTimeout(boot, 180); });
  $(document).on('app.content.loaded', function(){ setTimeout(boot, 150); });

})();
