<?php
// modules/guestinvoices/views/guest_customer_js.php
// Guest button + εμπλουτισμός του core selectpicker με email primary contact.
// Χωρίς Select2, καμία αντικατάσταση UI.
?>
(function(){
  "use strict";

  // -------- helpers ----------
  var CLIENT_SELECTORS = [
    'select[name="clientid"]',
    'select[name="customerid"]',
    '#clientid',
    '#customerid'
  ];

  function GI_ADMIN_URL(){
    if (typeof admin_url !== 'undefined' && admin_url) return admin_url;
    var $b = $('body'); var base = $b.attr('data-admin-url');
    if (base) return base.endsWith('/') ? base : (base + '/');
    var origin = location.origin || (location.protocol + '//' + location.host);
    return origin + '/admin/';
  }

  function findClientSelect(){
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

  function formatInline(company, email){
    company = (company||'').trim();
    email   = (email||'').trim();
    if (!company && !email) return '';
    if (!company) return email;
    if (!email)   return company;
    return company + ' — ' + email;
  }

  function formatDataContent(company, email){
    company = (company||'').trim();
    email   = (email||'').trim();
    if (!email) return $('<div>').text(company).html();
    return $('<div>').text(company).html() + ' <small class="text-muted">' + $('<div>').text(email).html() + '</small>';
  }

  function collectOptionIds($select){
    var ids = [];
    $select.find('option').each(function(){
      var v = $(this).attr('value');
      if (v && /^\d+$/.test(String(v))) ids.push(parseInt(v,10));
    });
    // όριο για performance
    if (ids.length > 400) ids = ids.slice(0,400);
    return ids;
  }

  function setOptionVisual($select, clientId, company, email){
    var $opt = $select.find('option[value="'+clientId+'"]');
    if (!$opt.length) return;
    // το κείμενο του option κρατά μόνο την επωνυμία, χωρίς email
    if (company && $.trim($opt.text()) !== $.trim(company)) $opt.text(company);
    $opt.attr('data-content', formatDataContent(company, email));
    if (email) $opt.attr('data-subtext', email); else $opt.removeAttr('data-subtext');
  }

  function refreshPickerKeepValue($select){
    // Διασφάλισε ότι είναι selectpicker
    if (!$select.hasClass('selectpicker')) $select.addClass('selectpicker');
    var cur = $select.selectpicker('val') || $select.val();
    $select.selectpicker('refresh');
    if (cur) $select.selectpicker('val', cur);
  }

  function updateVisibleButtonText($select){
    if (!$select.hasClass('selectpicker')) return;
    var val = $select.selectpicker('val') || $select.val();
    if (!val) return;
    var $opt = $select.find('option[value="'+val+'"]');
    var company = $.trim($opt.text());
    var email   = $.trim($opt.attr('data-subtext') || '');
    var label   = formatInline(company, email);
    var $bs     = $select.siblings('.bootstrap-select').first();
    var $target = $bs.find('.filter-option-inner-inner').first();
    if ($target.length && label) $target.text(label);
  }

  // --------- MAIN: enrich με email ----------
  function enrichCustomerSelectWithEmails(reason){
    var $select = findClientSelect();
    if (!$select.length) return;
    // καθάρισε τυχόν select2 containers από παλιές δοκιμές
    $select.next('.select2').remove();

    var ids = collectOptionIds($select);
    if (!ids.length) return;

    $.get(GI_ADMIN_URL() + 'guestinvoices/ajax_clients_primary_emails', { ids: ids.join(',') })
      .done(function(resp){
        var r;
        try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; } catch(e){ r = null; }
        if (!r || !r.success || !r.items) return;

        for (var i=0;i<r.items.length;i++){
          var it = r.items[i];
          setOptionVisual($select, it.id, it.company || '', it.email || '');
        }
        refreshPickerKeepValue($select);
        updateVisibleButtonText($select);
      });
  }

  // --------- Guest button / modal (όπως πριν) ---------
  function placeGuestUIOnce(){
    var $clientSelect = findClientSelect();
    if (!$clientSelect.length) return false;

    // Ορατό container selectpicker
    var $selectWrapper = $clientSelect.closest('.bootstrap-select');
    var $anchor = $selectWrapper.length ? $selectWrapper : $clientSelect;

    if ($anchor.parent('.gi-client-row').length) return true;

    var $row = $('<div class="gi-client-row"></div>');
    var $btn = $('#gi-open-guest-btn');
    if (!$btn.length) {
      $btn = $('<button/>', {
        id: 'gi-open-guest-btn',
        type: 'button',
        class: 'btn btn-primary',
        html: '<i class="fa fa-user-plus"></i> Guest'
      }).on('click', function(){
        $('#giGuestModal').modal('show');
        setTimeout(function(){
          var $first = $('#gi_firstname');
          if ($first.length) $first.focus();
          else $('#gi_email').focus();
        }, 200);
      });
    } else {
      $btn.detach();
    }

    $row.insertBefore($anchor);
    $row.append($anchor);
    $row.append($btn);
    return true;
  }

  function bindGuestFormSubmit(){
    $(document).off('submit', '#gi-guest-form').on('submit', '#gi-guest-form', function(e){
      e.preventDefault();
      var $form  = $(this);
      var $btn   = $('#gi-guest-submit');
      var $modal = $('#giGuestModal');

      var email = ($('#gi_email').val()||'').trim();
      if (!email) { (window.alert_float||alert)('danger','Το Email είναι υποχρεωτικό.'); return false; }

      $btn.prop('disabled', true);

      $.post($form.attr('action'), $form.serialize())
        .done(function(resp){
          var r; try { r = (typeof resp === 'string') ? JSON.parse(resp) : resp; } catch(e){ r = {success:false}; }
          if (r && r.success) {
            var $select = findClientSelect();
            if ($select.length) {
              var $opt = $select.find('option[value="'+r.client_id+'"]');
              var finalCompany = r.company || '';
              if (!$opt.length) { $opt = $('<option>', {value:r.client_id, text:finalCompany}); $select.append($opt); }
              else { $opt.text(finalCompany); }
              if ($select.hasClass('selectpicker')) {
                $select.selectpicker('refresh').selectpicker('val', r.client_id).trigger('changed.bs.select');
              } else {
                $select.val(r.client_id).trigger('change');
              }
              // άμεσο enrichment (για να μπει και το email)
              enrichCustomerSelectWithEmails('after-guest-create');
            }
            $modal.modal('hide'); if ($form[0]) $form[0].reset();
            (window.alert_float||alert)('success', r.message || 'Guest customer set.');
          } else {
            (window.alert_float||alert)('danger', (r && r.message) ? r.message : 'Error');
          }
        })
        .fail(function(){ (window.alert_float||alert)('danger','Request failed.'); })
        .always(function(){ $btn.prop('disabled', false); });

      return false;
    });
  }

  // --------- Binds ----------
  // 1) Όταν ανοίγει το dropdown -> φέρε/εμπλούτισε (ώστε να αναδομήσει τα <li>)
  $(document).on('shown.bs.select', CLIENT_SELECTORS.join(','), function(){
    var $select = $(this);
    // μισό κλικ χρόνος για να έχει ολοκληρωθεί το DOM των <li>
    setTimeout(function(){ enrichCustomerSelectWithEmails('shown.bs.select'); }, 60);
  });

  // 2) Μετά από core AJAX γέμισμα
  $(document).ajaxSuccess(function(event, xhr, settings){
    try{
      var url  = (settings.url  || '').toLowerCase();
      var data = (settings.data || '').toLowerCase();
      var combo = url + '&' + data;
      if (combo.indexOf('/admin/misc/get_relation_data') !== -1 &&
          (combo.indexOf('rel_type=customer') !== -1 ||
           combo.indexOf('relation_type=customer') !== -1 ||
           combo.indexOf('type=customer') !== -1)) {
        setTimeout(function(){ enrichCustomerSelectWithEmails('ajaxSuccess-get_relation_data'); }, 80);
      }
    }catch(e){}
  });

  // 3) Live-search μέσα στο selectpicker (όταν υπάρχει bs-searchbox)
  $(document).on('keyup', '.bootstrap-select .bs-searchbox input', function(){
    // μικρό delay για να φιλτραριστούν τα items
    setTimeout(function(){ enrichCustomerSelectWithEmails('live-search'); }, 100);
  });

  // 4) Όταν αλλάζει η επιλογή, ενημέρωσε το ορατό label
  $(document).on('changed.bs.select', CLIENT_SELECTORS.join(','), function(){
    updateVisibleButtonText($(this));
  });

  // --------- Init ----------
  $(function(){
    // Καθαρισμός τυχόν Select2 artifacts από παλιές προσπάθειες
    $(CLIENT_SELECTORS.join(',')).each(function(){
      $(this).next('.select2').remove();
    });

    placeGuestUIOnce();
    bindGuestFormSubmit();

    // αρχικό enrichment (αν υπάρχουν ήδη options)
    setTimeout(function(){ enrichCustomerSelectWithEmails('init'); }, 200);

    // μικρό retry window για αργά render
    var tries = 0, t = setInterval(function(){
      tries++;
      var ok = placeGuestUIOnce();
      if (ok && tries > 2) { clearInterval(t); return; }
      if (tries >= 20) { clearInterval(t); }
    }, 150);
  });

  $(document).on('app.content.loaded', function(){
    setTimeout(function(){
      $(CLIENT_SELECTORS.join(',')).each(function(){
        $(this).next('.select2').remove();
      });
      placeGuestUIOnce();
      bindGuestFormSubmit();
      enrichCustomerSelectWithEmails('spa-reload');
    }, 120);
  });

})();
