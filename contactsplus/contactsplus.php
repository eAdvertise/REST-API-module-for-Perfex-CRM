<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Contacts Plus
Description: Connect Contact with multiple Companies and create contact without email.
Version: 2.0.0
Requires at least: 3.0.*
Author: eAdvertise
Author URI: https://www.eadvertise.eu/
*/

define('CONTACTSPLUS_MODULE_NAME', 'contactsplus');
define('CONTACTSPLUS_MODULE_VERSION', '2.0.0');

// --- Hooks registration ---
register_activation_hook(CONTACTSPLUS_MODULE_NAME, 'contactsplus_module_activate');
register_uninstall_hook(CONTACTSPLUS_MODULE_NAME, 'contactsplus_module_uninstall');
register_language_files(CONTACTSPLUS_MODULE_NAME, [CONTACTSPLUS_MODULE_NAME]);

function contactsplus_module_activate()
{
    // Fresh install (creates base tables)
    require_once __DIR__ . '/install.php';

    // 1) Τρέξε sanity πάντα (διορθώνει σχήμα idempotently)
    $sanity = __DIR__ . '/migrations/schema_sanity.php';
    if (file_exists($sanity)) {
        require_once $sanity;
        if (function_exists('contactsplus_schema_sanity')) {
            contactsplus_schema_sanity();
        }
    }

    // 2) Τρέξε τυχόν migrations που λείπουν
    contactsplus_maybe_run_migrations();

    // 3) Στο τέλος γράψε την τρέχουσα έκδοση
    update_option('contactsplus_module_version', CONTACTSPLUS_MODULE_VERSION);
}

function contactsplus_module_uninstall()
{
    require_once __DIR__ . '/uninstall.php';
}

// --- MIGRATIONS RUNNER (τρέχει σε κάθε admin load, αλλά εφαρμόζει μόνο όταν χρειάζεται) ---
hooks()->add_action('admin_init', 'contactsplus_maybe_run_migrations');

function contactsplus_maybe_run_migrations()
{
    // 0) Πάντα-ασφάλεια: schema sanity
    $sanity = __DIR__ . '/migrations/schema_sanity.php';
    if (file_exists($sanity)) {
        require_once $sanity;
        if (function_exists('contactsplus_schema_sanity')) {
            contactsplus_schema_sanity();
        }
    }

    // 1) Installed version
    $installed = get_option('contactsplus_module_version');
    if (!$installed) {
        $installed = '0.0.0';
    }

    // 2) Migrations
    $migrations = [
        '1.0.1' => [
            'file' => __DIR__ . '/migrations/101_add_link_json_columns.php',
            'func' => 'contactsplus_migration_101',
        ],
        '2.0.0' => [
            'file' => __DIR__ . '/migrations/200_remote_search_link_existing.php',
            'func' => 'contactsplus_migration_200',
        ],
    ];

    // 3) Run pending migrations
    foreach ($migrations as $ver => $mig) {
        if (version_compare($installed, $ver, '<')) {
            if (file_exists($mig['file'])) {
                require_once $mig['file'];
                if (function_exists($mig['func'])) {
                    call_user_func($mig['func']);
                }
            }

            update_option('contactsplus_module_version', $ver);
            $installed = $ver;
        }
    }

    // 4) Always sync final version with module version
    if ($installed !== CONTACTSPLUS_MODULE_VERSION) {
        update_option('contactsplus_module_version', CONTACTSPLUS_MODULE_VERSION);
    }
}

// ----------------------------------------------------------
// Permissions (staff capability)
hooks()->add_action('admin_init', function () {
    if (function_exists('register_staff_capability')) {
        register_staff_capability('contactsplus', 'contactsplus_manage', _l('contactsplus_perm_manage'));
    }
});

// ----------------------------------------------------------
// Tab στο sidebar της καρτέλας Πελάτη
hooks()->add_filter('customer_profile_tabs', function ($tabs) {
    $CI = &get_instance();
    $customer_id = (int) (
        $CI->uri->segment(4)
        ?: $CI->input->get('userid')
        ?: $CI->input->get('customer_id')
    );

    $tabs['contactsplus'] = [
        'slug'     => 'contactsplus',
        'name'     => _l('module_contactsplus'),
        'icon'     => 'fa fa-address-book',
        'href'     => admin_url('clients/client/' . $customer_id . '?group=contactsplus'),
        'view'     => 'contactsplus/contacts/customer_tab', // proxy view
        'position' => 16,
        'badge'    => ['class' => 'CustomerProfileBadges', 'method' => 'contactsplus_badge_none'],
    ];
    return $tabs;
}, 10, 1);

// ---- Helper: render tab (δέχεται προαιρετικά $client_id από το hook) ----
if (!function_exists('contactsplus_render_tab')) {
    function contactsplus_render_tab($client_id = null)
    {
        $CI = &get_instance();

        if (!$client_id) {
            $client_id = (int) (
                $CI->uri->segment(4)
                ?: $CI->input->get('userid')
                ?: $CI->input->get('customer_id')
                ?: $CI->input->get('clientid')
            );
        }

        $CI->load->model('contactsplus/pmc_contact_company_model');
        $data = [
            'client_id' => $client_id,
            'contacts'  => $client_id ? $CI->pmc_contact_company_model->get_by_client($client_id) : [],
        ];

        echo $CI->load->view('contactsplus/contacts/customer_tab', $data, true);
    }
}

// Render περιεχομένου του tab — υποστήριξη για πολλαπλές εκδόσεις hooks
hooks()->add_action('customers_profile_tab', function ($group) {
    if ($group === 'contactsplus') {
        contactsplus_render_tab();
    }
}, 10, 1);

hooks()->add_action('customers_profile_tab_content', function ($group) {
    if ($group === 'contactsplus') {
        contactsplus_render_tab();
    }
}, 10, 1);

hooks()->add_action('after_client_profile_tab_content', function ($client_id = null) {
    $CI = &get_instance();
    if ($CI->input->get('group') === 'contactsplus') {
        contactsplus_render_tab((int) $client_id);
    }
}, 10, 1);

// ----------------------------------------------------------
// Email ΜΗ υποχρεωτικό στο core modal (front-end μόνο)
hooks()->add_action('app_admin_footer', function () {
    ?>
    <script>
    (function(){
      function relaxContactEmailRequired(){
        var $modal = $('#contact');
        if(!$modal.length) return;
        var $email = $modal.find('input[name="email"]');
        $email.prop('required', false).attr('required', false);
        $modal.find('label[for="email"] .text-danger').remove();
        try {
          var $form = $modal.find('form');
          if ($form.length && $form.data('validator') && typeof $email.rules === 'function') {
            $email.rules('remove', 'required');
            $email.rules('add', { email: true });
          }
        } catch(e){}
      }
      $(document).on('show.bs.modal', '#contact', function(){ setTimeout(relaxContactEmailRequired, 50); });
      $(function(){ setTimeout(relaxContactEmailRequired, 300); });
    })();
    </script>
    <?php
});

// Μικρό style
hooks()->add_action('app_admin_head', function () {
    echo '<style>#contactsplus-table td{vertical-align:middle}</style>';
});

// Cleanup bridge όταν διαγραφεί core contact
hooks()->add_action('customers_contact_deleted', function ($contact_id, $client_id) {
    $CI = &get_instance();
    $CI->db->where('tblcontact_id', $contact_id)
           ->where('client_id', $client_id)
           ->delete(db_prefix() . 'pmc_contacts_bridge');
}, 10, 2);

// ----------------------------------------------------------
// Core "Send to client" modals (στοχευμένα IDs): πρόσθεσε optgroup "Contacts+"
// και μετέφερε τις επιλογές Contacts+ στο additional_emails κατά το submit.
hooks()->add_action('app_admin_footer', function () {
    ?>
    <script>
    (function(){
      "use strict";

      var CP_API = <?= json_encode(admin_url('contactsplus/api/emails_for_client')); ?>;

      // Τα ακριβή modals που ανέφερες
      var MODALS = [
        '#invoice_send_to_client_modal',
        '#credit_note_send_to_client_modal',
        '#proposal_send_to_customer',
        '#estimate_send_to_client_modal',
        '#poa_statement_send_to_client',
        '#delivery_note_send_to_client_modal',
        '#payment_send_to_client'
      ].join(',');

      var CONTACT_SELECTORS = [
        'select[name="sent_to[]"]', // το δικό σου modal
        'select[name="sent_to"]',
        'select[name="contact[]"]',
        'select[name="contact"]'
      ];

      var EXTRA_EMAILS = [
        'input[name="additional_emails"]',
        'input[name="additional_email"]',
        '#additional_emails',
        '#additional_email'
      ];

      var CP_GROUP_LABEL  = 'Contacts+';
      var CP_VALUE_PREFIX = 'cpemail:';

      function qSelIn($root, list){
        for (var i=0;i<list.length;i++){
          var $el = $root.find(list[i]);
          if ($el.length) return $el;
        }
        return $();
      }
      function toInt(v){ v=parseInt(v,10); return isFinite(v)&&v>0?v:0; }

      function detectClientId($m){
        var names = ['clientid','userid','customer_id','client_id','customer'];
        for (var i=0;i<names.length;i++){
          var n = toInt($m.find('input[name="'+names[i]+'"]').val());
          if (n) return n;
        }
        return 0;
      }

      function detectContext($m){
        var id = ($m.attr('id')||'').toLowerCase();
        if (id.indexOf('invoice')>-1)       return 'invoice';
        if (id.indexOf('credit_note')>-1)   return 'credit_note';
        if (id.indexOf('proposal')>-1)      return 'proposal';
        if (id.indexOf('estimate')>-1)      return 'estimate';
        if (id.indexOf('poa_statement')>-1) return 'poa_statement';
        if (id.indexOf('delivery_note')>-1) return 'delivery_note';
        if (id.indexOf('payment')>-1)       return 'payment';
        return 'generic';
      }

      function ensureGroup($sel){
        var $g = $sel.find('optgroup[data-cp="1"]');
        if (!$g.length){
          $g = $('<optgroup>', { label: CP_GROUP_LABEL, 'data-cp':'1' });
          $sel.append($g);
        }
        return $g;
      }

      function optionExists($sel, value){
        return $sel.find('option[value="'+value.replace(/"/g,'&quot;')+'"]').length>0;
      }

      function addCpOptions($sel, emails){
        if (!emails || !emails.length) return;
        var $g = ensureGroup($sel);
        emails.forEach(function(row){
          var email = (row && row.email) ? String(row.email).trim() : '';
          if (!email) return;
          var value = CP_VALUE_PREFIX + email.toLowerCase();
          if (optionExists($sel, value)) return;
          var label = row.label || email;
          var $opt = $('<option>', { value:value, text:label })
                        .attr('data-cp','1')
                        .attr('data-email', email);
          $g.append($opt);
        });
        if (typeof $sel.selectpicker === 'function'){ $sel.selectpicker('refresh'); }
      }

      function fetchCpEmails(args, cb){
        $.getJSON(CP_API, args)
          .done(function(r){
            if (r && r.ok && Array.isArray(r.emails)){
              cb(r.emails.filter(function(e){ return e && e.source==='contactsplus'; }));
            } else { cb([]); }
          })
          .fail(function(){ cb([]); });
      }

      function mergeToAdditional($m, addEmails){
        if (!addEmails || !addEmails.length) return;
        var $extra = qSelIn($m, EXTRA_EMAILS);
        if (!$extra.length){
          $extra = $('<input>', { type:'hidden', name:'additional_emails' })
                     .appendTo($m.find('form').first());
        }
        var cur = $extra.val() || '';
        var parts = cur ? cur.split(',') : [];
        var set = {};
        parts = parts.map(function(x){ return (x||'').trim(); }).filter(function(x){ return x!==''; });
        parts.forEach(function(e){ set[(e||'').toLowerCase()] = true; });
        addEmails.forEach(function(e){
          var k = String(e).trim().toLowerCase();
          if (k && !set[k]) { parts.push(e); set[k]=true; }
        });
        $extra.val(parts.join(', '));
      }

      function bindSubmit($m, $sel){
        var $form = $m.find('form').first();
        if (!$form.length || $form.data('cpBound')) return;
        $form.data('cpBound', 1);

        function injectAllEmailFields(emails){
          if (!emails || !emails.length) return;

          var $single = qSelIn($m, [
            'input[name="additional_emails"]',
            '#additional_emails',
            'input[name="additional_email"]',
            '#additional_email'
          ]);
          if (!$single.length){
            $single = $('<input>', { type:'hidden', name:'additional_emails' }).appendTo($form);
          }
          var cur = ($single.val() || '').trim();
          var parts = cur ? cur.split(',') : [];
          var set = {};
          parts = parts.map(function(x){ return (x||'').trim(); }).filter(Boolean);
          parts.forEach(function(e){ set[e.toLowerCase()] = 1; });
          emails.forEach(function(e){ var k=e.toLowerCase(); if(!set[k]){ parts.push(e); set[k]=1; } });
          $single.val(parts.join(', '));

          var arrayNames = ['additional_emails[]','cc[]','bcc[]','emails[]'];
          arrayNames.forEach(function(n){
            emails.forEach(function(e){
              $('<input>', { type:'hidden', name:n, value:e }).appendTo($form);
            });
          });

          var singleNames = ['cc','bcc'];
          singleNames.forEach(function(n){
            if (!$form.find('input[name="'+n+'"]').length){
              $('<input>', { type:'hidden', name:n, value:emails.join(', ') }).appendTo($form);
            } else {
              var $f = $form.find('input[name="'+n+'"]').first();
              var curv = ($f.val() || '').trim();
              var p = curv ? curv.split(',') : [];
              var s = {};
              p = p.map(function(x){ return (x||'').trim(); }).filter(Boolean);
              p.forEach(function(e){ s[e.toLowerCase()] = 1; });
              emails.forEach(function(e){ var k=e.toLowerCase(); if(!s[k]){ p.push(e); s[k]=1; } });
              $f.val(p.join(', '));
            }
          });
        }

        function getSelectValue($sel){
          try{
            if (typeof $sel.selectpicker === 'function'){
              return $sel.selectpicker('val');
            }
          }catch(e){}
          return $sel.val();
        }

        $form.on('submit', function(){
          var sel = getSelectValue($sel) || [];
          if (!Array.isArray(sel)) sel = [sel];

          var cpEmails = [];
          var keep = [];

          sel.forEach(function(v){
            v = String(v);
            if (v.indexOf(CP_VALUE_PREFIX) === 0){
              var $opt = $sel.find('option[value="'+v.replace(/"/g,'&quot;')+'"]');
              var e = ($opt.data('email') || v.substring(CP_VALUE_PREFIX.length) || '').trim();
              if (e) cpEmails.push(e);
            } else {
              keep.push(v);
            }
          });

          if (typeof $sel.selectpicker === 'function'){
            $sel.selectpicker('val', keep);
          } else {
            if ($sel.prop('multiple')) { $sel.val(keep); } else { $sel.val(keep.length ? keep[0] : ''); }
          }

          injectAllEmailFields(cpEmails);
        });
      }

      function enhance($m){
        var $sel = qSelIn($m, CONTACT_SELECTORS);
        if (!$sel.length) return;

        var ctx = detectContext($m);
        var clientId = detectClientId($m);
        var args = { context: ctx };

        if (clientId > 0) {
          args.client_id = clientId;
        } else {
          var val = $sel.val();
          var coreCid = null;
          if (val && (Array.isArray(val) ? val.length>0 : true)) {
            coreCid = Array.isArray(val) ? val[0] : val;
          } else {
            var $first = $sel.find('option[value]').first();
            if ($first.length) coreCid = $first.val();
          }
          var intCid = parseInt(coreCid, 10);
          if (isFinite(intCid) && intCid>0) {
            args.contact_id = intCid;
          } else {
            return;
          }
        }

        fetchCpEmails(args, function(list){
          addCpOptions($sel, list);
        });
        bindSubmit($m, $sel);
      }

      $(document).on('shown.bs.modal', MODALS, function(){
        var $m = $(this);
        try {
          enhance($m);
          setTimeout(function(){ try{ enhance($m); }catch(e){} }, 350);
        } catch(e){}
      });

      setTimeout(function(){
        $(MODALS).filter('.in, .show').each(function(){ try{ enhance($(this)); }catch(e){} });
      }, 600);

    })();
    </script>
    <?php
});