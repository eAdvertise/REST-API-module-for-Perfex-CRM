<?php
// modules/guestinvoices/views/gi_save_record_js.php
// Προσθέτει:
// 1) Dropdown item "Save & Record Payment" ΜΕΣΑ στο υπάρχον button-group (Save caret menu)
// 2) Αυτόνομο κουμπί "Save & Record Payment" ΕΞΩ από το group, δεξιά του.
// Δεν αλλάζει στοίχιση ή υπάρχον DOM πέρα από τις 2 προσθήκες.
// Το click flow: AJAX flag -> trigger primary Save (ή submit fallback).

?>
(function(){
  "use strict";

  // Μικρό CSS για απόσταση από το group
  var css = '\
  #gi-save-record-payment-btn { margin-left:8px; white-space:nowrap; }\
  ';
  var styleTag = document.createElement('style');
  styleTag.type = 'text/css';
  styleTag.appendChild(document.createTextNode(css));
  document.head.appendChild(styleTag);

  function findInvoiceForm(){
    var $f = $('form#invoice-form, form[action*="invoices/invoice"]');
    return $f.length ? $f.first() : $();
  }

  function findSaveButtonGroup(){
    // Βρες το group που περιέχει το primary Save (btn-primary .invoice-form-submit.transaction-submit)
    var $group = $('.btn-group.dropup').filter(function(){
      return $(this).find('button.invoice-form-submit.transaction-submit.btn-primary').length > 0;
    }).first();
    return $group.length ? $group : $();
  }

  function ensureDropdownItem(){
    var $group = findSaveButtonGroup();
    if (!$group.length) return false;

    var $menu = $group.find('ul.dropdown-menu').first();
    if (!$menu.length) return false;

    // Αν υπάρχει ήδη, μην το ξαναπροσθέτεις
    if ($menu.find('a.gi-save-record-payment').length) return true;

    var $item = $('<li><a href="#" class="gi-save-record-payment transaction-submit">Save &amp; Record Payment</a></li>');

    // Τοποθέτηση: μετά το "Save and Send Later" αν υπάρχει, αλλιώς στο τέλος
    var $sendLater = $menu.find('a.save-and-send-later').first();
    if ($sendLater.length) {
      $sendLater.closest('li').after($item);
    } else {
      $menu.append($item);
    }
    return true;
  }

  function ensureStandaloneButton(){
    var $group = findSaveButtonGroup();
    if (!$group.length) return false;

    // Αν υπάρχει ήδη standalone κουμπί, τέλος
    if ($('#gi-save-record-payment-btn').length) return true;

    // Δημιουργία αυτόνομου κουμπιού (ίδιο action με dropdown)
    var $btn = $('<button/>', {
      id: 'gi-save-record-payment-btn',
      type: 'button',
      class: 'btn btn-success',
      html: '<i class="fa fa-credit-card"></i> Save &amp; Record Payment',
      'data-toggle': 'tooltip',
      title: 'Αποθήκευση τιμολογίου και άμεση καταχώριση πληρωμής'
    });

    // Τοποθέτηση: αμέσως μετά το group, για να μείνει η στοίχιση του group ανέγγιχτη
    $group.after($btn);

    // Tooltip refresh αν υπάρχει
    if (typeof init_tooltip !== 'undefined') { init_tooltip(); }

    return true;
  }

  function triggerPrimarySave(){
    var $group = findSaveButtonGroup();
    var $primarySave = $group.find('button.invoice-form-submit.transaction-submit').first();
    if ($primarySave.length) {
      $primarySave.trigger('click');
      return true;
    } else {
      var $form = findInvoiceForm();
      if ($form.length) { $form.trigger('submit'); return true; }
    }
    return false;
  }

  function setComboFlagAndSave(){
    $.post(admin_url + 'guestinvoices/flag_combo_flow', {})
      .always(function(){
        triggerPrimarySave();
      });
  }

  function bindHandlers(){
    // Dropdown item μέσα στο group
    $(document).off('click.gi', 'a.gi-save-record-payment')
      .on('click.gi', 'a.gi-save-record-payment', function(e){
        e.preventDefault();
        setComboFlagAndSave();
      });

    // Αυτόνομο κουμπί δεξιά του group
    $(document).off('click.gi', '#gi-save-record-payment-btn')
      .on('click.gi', '#gi-save-record-payment-btn', function(e){
        e.preventDefault();
        setComboFlagAndSave();
      });
  }

  function init(){
    var changed = false;
    if (ensureDropdownItem()) changed = true;
    if (ensureStandaloneButton()) changed = true;
    if (changed) bindHandlers();
  }

  $(function(){
    init();
    // Για δυναμικό περιεχόμενο / SPA reloads
    $(document).on('app.content.loaded', function(){
      setTimeout(init, 50);
    });
  });

})();
