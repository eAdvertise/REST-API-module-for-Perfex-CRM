<?php 
defined('BASEPATH') or exit('No direct script access allowed'); 
//modules/contactsplus/views/contacts/list.php
?>
<style>
  #cp_core_unlink_modal .radio input[type="radio"]{
    position: static; opacity: 1; margin-left: 0;
  }
</style>

<table class="table table-striped" id="contactsplus-table">
  <thead>
    <tr>
      <th><?= _l('contactsplus_col_name'); ?></th>
      <th><?= _l('contactsplus_col_phone'); ?></th>
      <th><?= _l('contactsplus_col_email'); ?></th>
      <th><?= _l('contactsplus_col_role'); ?></th>
      <th><?= _l('contactsplus_col_flags'); ?></th>
      <th><?= _l('contactsplus_col_actions'); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach(($contacts ?? []) as $row): ?>
      <tr>
        <td>
			<?php if(($row['source'] ?? '') === 'pmc' && !empty($row['contact_id'])){ ?>
				<!-- PMC contact actions -->
				<a href="javascript:void(0)" role="button" class="cp-pmc-edit" title="<?= _l('contactsplus_action_edit'); ?>">
					<?= html_escape(trim(($row['firstname'] ?? '').' '.($row['lastname'] ?? ''))); ?>
				</a>
            
			<?php }else{ ?>
				<!-- CORE contact actions -->
				<a href="javascript:void(0)" role="button" class="cp-core-edit" title="<?= _l('contactsplus_action_edit'); ?>">
					<?= html_escape(trim(($row['firstname'] ?? '').' '.($row['lastname'] ?? ''))); ?>
				</a>
			<?php } ?>
		</td>
        <td><?= html_escape($row['phone'] ?? ''); ?></td>
        <td><?= html_escape($row['email'] ?? ''); ?></td>
        <td><?= html_escape($row['display_role'] ?? ''); ?></td>
        <td>
          <?php if(!empty($row['is_primary'])): ?><span class="label label-info"><?= _l('contactsplus_flag_primary'); ?></span><?php endif; ?>
          <?php if(!empty($row['billing'])): ?><span class="label label-success"><?= _l('contactsplus_flag_billing'); ?></span><?php endif; ?>
          <?php if(!empty($row['notifications'])): ?><span class="label label-default"><?= _l('contactsplus_flag_notifications'); ?></span><?php endif; ?>
          <?php if(($row['source'] ?? '') === 'core'): ?><span class="label label-warning">core</span><?php endif; ?>
        </td>
        <td class="text-right"
            data-link-id="<?= (int)($row['link_id'] ?? 0); ?>"
            data-contact-id="<?= (int)($row['contact_id'] ?? 0); ?>"
            data-core-id="<?= (int)($row['core_id'] ?? 0); ?>"
        >
          <?php if(($row['source'] ?? '') === 'pmc' && !empty($row['contact_id'])): ?>
            <!-- PMC contact actions -->
            <a href="javascript:void(0)" role="button" class="mright10 text-info cp-pmc-edit" title="<?= _l('contactsplus_action_edit'); ?>">
              <i class="fa fa-pencil"></i>
            </a>
            <a href="javascript:void(0)" role="button" class="mright10 text-warning cp-unlink" title="<?= _l('contactsplus_action_unlink'); ?>">
              <i class="fa fa-unlink"></i>
            </a>
            <a href="javascript:void(0)" role="button" class="text-danger cp-delete" title="<?= _l('delete'); ?>">
              <i class="fa fa-trash"></i>
            </a>
          <?php else: ?>
            <!-- CORE contact actions -->
            <a href="javascript:void(0)" role="button" class="mright10 text-info cp-core-edit" title="<?= _l('contactsplus_action_edit'); ?>">
              <i class="fa fa-pencil"></i>
            </a>
            <a href="javascript:void(0)" role="button" class="text-danger cp-core-unlink" title="<?= _l('contactsplus_action_unlink'); ?>">
              <i class="fa fa-trash"></i>
            </a>
          <?php endif; ?>
        </td>
      </tr>

      <?php if(($row['source'] ?? '') === 'pmc' && !empty($row['contact_id'])): ?>
      <!-- Enable Portal Modal (μόνο για pmc επαφές) -->
      <div class="modal fade" id="contactsplus_enable_portal_<?= (int)$row['contact_id'] ?>" tabindex="-1">
        <div class="modal-dialog"><div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><?= _l('contactsplus_modal_enable_portal'); ?></h4>
          </div>
          <div class="modal-body">
            <form method="post" action="<?= admin_url('contactsplus/api/contacts/'.(int)$row['contact_id'].'/enable-portal'); ?>">
              <input type="hidden" name="client_id" value="<?= (int)$client_id ?>">
              <div class="form-group">
                <label>Email</label>
                <input type="email" class="form-control" name="email" required>
              </div>
              <div class="checkbox">
                <label><input type="checkbox" name="send_set_password_email" value="1"> <?= _l('contactsplus_send_set_password_email'); ?></label>
              </div>
              <button class="btn btn-primary" type="submit"><?= _l('contactsplus_save'); ?></button>
            </form>
          </div>
        </div></div>
      </div>
      <?php endif; ?>

    <?php endforeach; ?>
  </tbody>
</table>

<div id="cp_core_edit_holder"></div>

<!-- Core Unlink/Move modal -->
<div class="modal fade" id="cp_core_unlink_modal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">&times;</button>
				<h4 class="modal-title"><?= _l('contactsplus_core_unlink_title'); ?></h4>
			</div>
			<div class="modal-body">
				<p class="text-muted"><?= _l('contactsplus_core_unlink_desc'); ?></p>

				<div class="radio radio-primary mtop10">
					<input type="radio"
						id="cp_core_unlink_delete"
						name="cp_core_unlink_action"
						value="delete"
						checked>
					<label for="cp_core_unlink_delete"><?= _l('delete'); ?></label>
				</div>

				<div class="radio radio-primary mtop10">
					<input type="radio"
						 id="cp_core_unlink_move"
						 name="cp_core_unlink_action"
						 value="move">
					<label for="cp_core_unlink_move"><?= _l('contactsplus_move_to_another_customer'); ?></label>
				</div>


				<div id="cp_move_target_wrap" class="mtop15" style="display:none">
					<label><?= _l('client'); ?></label>
					<select id="cp_move_target" class="selectpicker" data-live-search="true" data-width="100%" title="<?= _l('search'); ?>"></select>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
				<button id="cp_core_unlink_confirm" type="button" class="btn btn-primary"><?= _l('confirm'); ?></button>
			</div>
		</div>
	</div>
</div>


<?php $this->load->view('contactsplus/contacts/create_modal', ['client_id' => $client_id]); ?>
<?php $this->load->view('contactsplus/contacts/link_existing_modal', ['client_id' => $client_id]); ?>
<?php init_tail(); ?>
<script>
(function($){
  "use strict";
  if(!$) return; // safety
  // -------------------------------
  // Globals / Endpoints
  // -------------------------------
  var CLIENT_ID     = <?= (int)$client_id ?>;
  var SEARCH_URL    = '<?= admin_url('contactsplus/search_available'); ?>'; // dropdown για Link Existing
  var LINK_URL_BASE = '<?= admin_url('contactsplus/api/link_core_contact'); ?>'; // POST link core -> pmc

  // Endpoints χωρίς reliance σε custom routes (χτυπάμε απευθείας methods):
  var CORE_EDIT_GET_URL      = '<?= admin_url('clients/form_contact'); ?>'; // /{client}/{coreId}
  var CORE_DELETE_POST_URL   = '<?= admin_url('clients/delete_contact'); ?>'; // /{coreId}/{client}
  var CORE_MOVE_POST_URL     = '<?= admin_url('contactsplus/api/move_core'); ?>'; // /{coreId}
  var SEARCH_CUSTOMERS_URL   = '<?= admin_url('contactsplus/search_customers'); ?>';

  var PMC_GET_URL            = '<?= admin_url('contactsplus/api/get_pmc_contact'); ?>';     // /{pmcId}?client_id=...
  var PMC_UPDATE_POST_URL    = '<?= admin_url('contactsplus/api/update_pmc_contact'); ?>';  // /{pmcId}

  // NEW: PMC unlink / delete endpoints
  var PMC_UNLINK_POST_URL    = '<?= admin_url('contactsplus/api/unlink'); ?>';              // /{link_id}
  var PMC_DELETE_POST_URL    = '<?= admin_url('contactsplus/api/delete_contact'); ?>';      // /{pmc_contact_id}

  // -------------------------------
  // Helpers
  // -------------------------------
  function csrfAppendToQS(qs){
    if (window.csrfData && csrfData.token_name && csrfData.hash) {
      qs += (qs.indexOf('?') === -1 ? '?' : '&')
          + encodeURIComponent(csrfData.token_name) + '=' + encodeURIComponent(csrfData.hash);
    }
    return qs;
  }
  function csrfAppendToFormData(data){
    if (window.csrfData && csrfData.token_name && csrfData.hash) {
      if (typeof data === 'string') {
        data += '&' + encodeURIComponent(csrfData.token_name) + '=' + encodeURIComponent(csrfData.hash);
      } else {
        data[csrfData.token_name] = csrfData.hash;
      }
    }
    return data;
  }
  function disableLeaveWarning(){
    try {
      $(window).off('beforeunload');
      window.onbeforeunload = null;
      if (window.App && App.options) { App.options.show_confirm_before_leaving = 0; }
    } catch(e){}
  }

  // -------------------------------
  // Link Existing: remote load dropdown
  // -------------------------------
  var $select = $('#contactsplus_contact_select');
  var selectedId = null;
  var cpSearchTimer = null;

  function buildOptionLabel(it){
    var label = it.text || '';
    if (it.email) label += ' — ' + it.email;
    if (it.phone) label += ' — ' + it.phone;
    return label;
  }

  function loadContacts(q, preserveSelected){
    $.getJSON(SEARCH_URL, { client_id: CLIENT_ID, q: q || '' })
      .done(function(resp){
        var list = (resp && resp.results) ? resp.results : [];
        var prev = preserveSelected ? ($select.val() || selectedId) : null;

        $select.empty();

        list.forEach(function(it){
          var opt = $('<option>', {
            value: it.id,
            text: buildOptionLabel(it)
          });

          if (prev && String(prev) === String(it.id)) {
            opt.prop('selected', true);
          }

          $select.append(opt);
        });

        $select.selectpicker('refresh');

        if (prev) {
          $select.selectpicker('val', String(prev));
          selectedId = String(prev);
        }
      })
      .fail(function(){
        $select.empty();
        $select.selectpicker('refresh');
      });
  }

  $('#contactsplus_link_modal').on('shown.bs.modal', function(){
    loadContacts('', true);

    setTimeout(function(){
      var $searchInput = $('#contactsplus_link_modal .bs-searchbox input');

      $searchInput.off('.contactsplusRemoteSearch');
      $searchInput.on('input.contactsplusRemoteSearch keyup.contactsplusRemoteSearch', function(){
        var q = $(this).val() || '';
        clearTimeout(cpSearchTimer);
        cpSearchTimer = setTimeout(function(){
          loadContacts(q, true);
        }, 250);
      });
    }, 150);
  });

  $('#contactsplus_link_modal').on('hidden.bs.modal', function(){
    clearTimeout(cpSearchTimer);
  });

  $select.on('changed.bs.select', function(){
    selectedId = $(this).val();
  });

  // -------------------------------
  // Link Existing: form submit
  // -------------------------------
  $('#contactsplus_link_form').on('submit', function(e){
    e.preventDefault();
    if (!selectedId) { alert('Επίλεξε επαφή.'); return; }

    var url  = LINK_URL_BASE + '/' + encodeURIComponent(selectedId);
    var data = $(this).serialize();
    data = csrfAppendToFormData(data);

    $.ajax({ url: url, type: 'POST', data: data })
      .done(function(){ disableLeaveWarning(); window.location.reload(); })
      .fail(function(xhr){
        if (xhr.status === 419 || xhr.status === 403) {
          alert('Η συνεδρία έληξε. Κάνε refresh και ξαναπροσπάθησε.');
        } else {
          alert('Σφάλμα: ' + (xhr.responseText || xhr.statusText));
        }
      });
  });

  // ------------------------------------------------
  // CORE: EDIT (open modal) + AJAX submit του core modal
  // ------------------------------------------------
  $(document).on('click', '.cp-core-edit', function(e){
    e.preventDefault(); e.stopPropagation();
    var $td   = $(this).closest('td');
    var coreId = parseInt($td.data('core-id'),10) || 0;
    if (!coreId) return false;

    var url = CORE_EDIT_GET_URL + '/' + CLIENT_ID + '/' + coreId;

    $.get(url).done(function(html){
      $('#cp_core_edit_holder').html(html);
      // χαλάρωσε το required στο email
      try {
        var $modal = $('#cp_core_edit_holder').find('#contact');
        var $email = $modal.find('input[name="email"]');
        $email.prop('required', false).attr('required', false);
        $modal.find('label[for="email"] .text-danger').remove();
      } catch(e){}
      $('#cp_core_edit_holder').find('#contact').modal('show');
      disableLeaveWarning();
    }).fail(function(xhr){
      alert('Error loading editor: ' + (xhr.responseText || xhr.statusText));
    });

    return false;
  });

  // Delegated AJAX submit για το core modal (να μην εμφανίζεται ωμό JSON)
  $(document).on('submit', '#cp_core_edit_holder #contact-form', function(e){
    e.preventDefault();
    var $form = $(this);
    var data  = $form.serialize();
    data = csrfAppendToFormData(data);

    // χαλάρωσε τυχόν re-added required
    try {
      var $email = $form.find('input[name="email"]');
      $email.prop('required', false).attr('required', false);
    } catch(e){}

    $.post($form.attr('action'), data)
      .done(function(){
        try { $('#cp_core_edit_holder').find('#contact').modal('hide'); } catch(e){}
        disableLeaveWarning();
        window.location.reload();
      })
      .fail(function(xhr){ alert('Error: ' + (xhr.responseText || xhr.statusText)); });
  });

  // ------------------------------------------------
  // PMC: EDIT (φορτώνει τον core-like κλώνο modal με existing data)
  // ------------------------------------------------
  $(document).on('click', '.cp-pmc-edit', function(e){
    e.preventDefault(); e.stopPropagation();
    var $td = $(this).closest('td');
    var pmcId = parseInt($td.data('contact-id'),10) || 0;
    if (!pmcId) return false;

    $.getJSON(PMC_GET_URL + '/' + pmcId, { client_id: CLIENT_ID })
      .done(function(res){
        if (!res || !res.ok) { alert('Load failed'); return; }
        var c = res.contact || {};
        var l = res.link || {};

        var $modal = $('#contactsplus_core_contact');
        var $form  = $('#contactsplus-core-contact-form');

        $form.attr('data-pmc-edit','1')
             .attr('data-pmc-id', pmcId)
             .attr('data-action', PMC_UPDATE_POST_URL + '/' + pmcId);

        // βασικά πεδία
        $form.find('input[name="firstname"]').val(c.firstname || '');
        $form.find('input[name="lastname"]').val(c.lastname || '');
        $form.find('input[name="title"]').val(c.position || '');
        $form.find('input[name="email"]').val(c.email || '');
        $form.find('input[name="phonenumber"]').val(c.phone || '');

        // email not required
        try {
          var $email = $form.find('input[name="email"]');
          $email.prop('required', false).attr('required', false);
          $form.find('label[for="email"] .text-danger').remove();
        } catch(e){}

        // flags
        $form.find('#contact_primary').prop('checked', !!parseInt(l && l.is_primary ? l.is_primary : 0, 10));

        // EMAIL NOTIFICATIONS
        var notifKeys = res.email_notif_keys || [];
        if ((!notifKeys || !notifKeys.length) && l && l.email_notif_json) {
          try {
            var parsed = JSON.parse(l.email_notif_json);
            if (Array.isArray(parsed)) notifKeys = parsed;
          } catch(e){}
        }
        ['invoice_emails','estimate_emails','credit_note_emails','project_emails','ticket_emails','task_emails','contract_emails']
          .forEach(function(key){
            $form.find('#'+key).prop('checked', notifKeys.indexOf(key) !== -1);
          });

        // PERMISSIONS (numeric ids)
        $form.find('input[name="permissions[]"]').prop('checked', false);
        if (res.perms_ids && res.perms_ids.length) {
          res.perms_ids.forEach(function(pid){
            $form.find('input[name="permissions[]"][value="'+pid+'"]').prop('checked', true);
          });
        }

        $modal.modal('show');
        disableLeaveWarning();
      })
      .fail(function(xhr){
        alert('Error: ' + (xhr.responseText || xhr.statusText));
      });

    return false;
  });

  // ------------------------------------------------
  // Submit του core-like modal (create ή pmc-edit) μέσω AJAX
  // ------------------------------------------------
  $(document).on('submit', '#contactsplus-core-contact-form', function(e){
    e.preventDefault();
    var $form = $(this);

    // pmc-edit mode?
    var isPmcEdit = $form.attr('data-pmc-edit') === '1' || $form.attr('data-pmc-edit') === 1;
    var actionOverride = $form.attr('data-action');

    var data  = $form.serializeArray();
    // >>> ΜΟΝΗ ΑΛΛΑΓΗ: σωστό πεδίο πελάτη
    if (isPmcEdit) {
	  // Για το δικό μας API
	  data.push({name: 'client_id', value: CLIENT_ID});
	} else {
	  // Για το core endpoint του Perfex
	  data.push({name: 'userid', value: CLIENT_ID});
	}

    if (window.csrfData && csrfData.token_name && csrfData.hash) {
      data.push({name: csrfData.token_name, value: csrfData.hash});
    }

    if (isPmcEdit && actionOverride) {
      // Update PMC
      $.post(actionOverride, $.param(data))
        .done(function(){ $('#contactsplus_core_contact').modal('hide'); disableLeaveWarning(); window.location.reload(); })
        .fail(function(xhr){ alert('Error: ' + (xhr.responseText || xhr.statusText)); });
    } else {
      // Create (core endpoint)
      $.post($(this).attr('action'), $.param(data))
        .done(function(){ $('#contactsplus_core_contact').modal('hide'); disableLeaveWarning(); window.location.reload(); })
        .fail(function(xhr){ alert('Error: ' + (xhr.responseText || xhr.statusText)); });
    }
  });

  // ------------------------------------------------
  // PMC: UNLINK
  // ------------------------------------------------
  $(document).off('click', '.cp-unlink').on('click', '.cp-unlink', function(e){
    e.preventDefault(); e.stopPropagation();
    var $td = $(this).closest('td');
    var linkId = parseInt($td.data('link-id'),10) || 0;
    if (!linkId) { alert('Missing link id'); return false; }

    if (!confirm('<?= _l('Are you sure'); ?>')) return false;

    var data = {};
    data = csrfAppendToFormData(data);

    $.post(PMC_UNLINK_POST_URL + '/' + linkId, data)
      .done(function(res){
        disableLeaveWarning();
        window.location.reload();
      })
      .fail(function(xhr){
        alert('Unlink failed: ' + (xhr.responseText || xhr.statusText));
      });

    return false;
  });

  // ------------------------------------------------
  // PMC: DELETE
  // ------------------------------------------------
  $(document).off('click', '.cp-delete').on('click', '.cp-delete', function(e){
    e.preventDefault(); e.stopPropagation();
    var $td = $(this).closest('td');
    var pmcId = parseInt($td.data('contact-id'),10) || 0;
    if (!pmcId) { alert('Missing contact id'); return false; }

    if (!confirm('<?= _l('Are you sure'); ?>')) return false;

    var data = {};
    data = csrfAppendToFormData(data);

    $.post(PMC_DELETE_POST_URL + '/' + pmcId, data)
      .done(function(res){
        disableLeaveWarning();
        window.location.reload();
      })
      .fail(function(xhr){
        alert('Delete failed: ' + (xhr.responseText || xhr.statusText));
      });

    return false;
  });

  // ------------------------------------------------
  // CORE: UNLINK (Delete | Move)  (μένει ως έχει)
  // ------------------------------------------------
  var CORE_ID_PENDING = 0;

  $(document).on('click', '.cp-core-unlink', function(e){
    e.preventDefault(); e.stopPropagation();
    var $td = $(this).closest('td');
    CORE_ID_PENDING = parseInt($td.data('core-id'),10) || 0;
    if (!CORE_ID_PENDING) return false;

    // reset modal radios + hide move target
    $('#cp_core_unlink_modal input[name="cp_core_unlink_action"][value="delete"]').prop('checked', true).trigger('change');
    $('#cp_move_target_wrap').hide();
    $('#cp_move_target').empty().selectpicker('refresh');

    $('#cp_core_unlink_modal').modal('show');
    disableLeaveWarning();
    return false;
  });

  // toggle move target
  $('#cp_core_unlink_modal').on('change','input[name="cp_core_unlink_action"]', function(){
    var val = $('input[name="cp_core_unlink_action"]:checked').val();
    $('#cp_move_target_wrap').toggle(val === 'move');
  });

	// lazy-load πελατών (μία φορά)
	var _cpCustomersLoaded = false;
	$('#cp_core_unlink_modal').on('show.bs.modal', function () {
		$('#cp_core_unlink_delete').prop('checked', true).trigger('change');
		$('#cp_move_target_wrap').hide();
		$('#cp_move_target').empty().selectpicker('refresh');
		_cpCustomersLoaded = false;
	});

	$('#cp_core_unlink_modal').on('change','input[name="cp_core_unlink_action"]', function(){
		var val = $('input[name="cp_core_unlink_action"]:checked').val();
		$('#cp_move_target_wrap').toggle(val === 'move');

		if (val === 'move' && !_cpCustomersLoaded) {
			_cpCustomersLoaded = true;
			var $sel = $('#cp_move_target');
			$.getJSON(<?= json_encode(admin_url('contactsplus/search_customers')); ?>)
				.done(function(res){
					var list = (res && res.results) ? res.results : [];
					$sel.empty();
					list.forEach(function(it){ $sel.append($('<option>',{value:it.id, text: it.text})); });
					$sel.selectpicker('refresh');
				});
		}
	});
	$('#cp_core_unlink_confirm').on('click', function(e){
		e.preventDefault(); e.stopPropagation();
		if (!CORE_ID_PENDING) return false;

		var action = $('input[name="cp_core_unlink_action"]:checked').val();
		var data   = {};
		if (window.csrfData && csrfData.token_name && csrfData.hash) {
			data[csrfData.token_name] = csrfData.hash;
		}

		if (action === 'delete') {
			data['client_id'] = CLIENT_ID;
			data['delete']    = 1;
			$.post(<?= json_encode(admin_url('contactsplus/api/unlink_core/')); ?> + CORE_ID_PENDING, data)
				.done(function(){ window.location.reload(); })
				.fail(function(xhr){ alert('Error: ' + (xhr.responseText || xhr.statusText)); });
		} else {
			var target = $('#cp_move_target').val();
			if (!target) { alert('<?= _l('please_select_customer'); ?>'); return false; }
			data['from_client_id'] = CLIENT_ID;
			data['to_client_id']   = target;

			$.post(<?= json_encode(admin_url('contactsplus/api/move_core/')); ?> + CORE_ID_PENDING, data)
				.done(function(){ window.location.reload(); })
				.fail(function(xhr){ alert('Error: ' + (xhr.responseText || xhr.statusText)); });
		}

	  return false;
	});
	
	// ------------------------------------------------
	// CORE: DELETE (direct, σε AJAX, χωρίς redirect/404)
	// ------------------------------------------------
	$(document).off('click.cpCoreDelete').on('click.cpCoreDelete', '.cp-core-delete', function (e) {
	  e.preventDefault(); e.stopPropagation();

	  var $td    = $(this).closest('td');
	  var coreId = parseInt($td.data('core-id'), 10) || 0;
	  if (!coreId) return false;

	  if (!confirm('<?= _l('confirm_action_prompt'); ?>')) return false;

	  var url  = '<?= admin_url('clients/delete_contact'); ?>' + '/' + coreId + '/' + CLIENT_ID;

	  var data = {};
	  if (window.csrfData && csrfData.token_name && csrfData.hash) {
		data[csrfData.token_name] = csrfData.hash;
	  }
	  data.request_ajax = 1;
	  data.redirect     = 0;
	  data.do_not_redirect = 1;

	  try { $(window).off('beforeunload'); window.onbeforeunload = null; if (window.App && App.options) { App.options.show_confirm_before_leaving = 0; } } catch(e){}

	  $.ajax({
		url: url,
		type: 'POST',
		data: data,
		headers: {
		  'X-Requested-With': 'XMLHttpRequest',
		  'Accept': 'application/json'
		}
	  })
	  .done(function (res, textStatus, xhr) {
		try {
		  var j = (typeof res === 'string') ? JSON.parse(res) : res;
		  if (j && j.success) {
			window.location = '<?= admin_url('clients/client'); ?>' + '/' + CLIENT_ID + '?group=contactsplus';
			return;
		  }
		  if (j && j.url) {
			window.location = '<?= admin_url('clients/client'); ?>' + '/' + CLIENT_ID + '?group=contactsplus';
			return;
		  }
		} catch(_) {}

		window.location = '<?= admin_url('clients/client'); ?>' + '/' + CLIENT_ID + '?group=contactsplus';
	  })
	  .fail(function (xhr) {
		alert('Error: ' + (xhr.responseText || xhr.statusText));
	  });

	  return false;
	});

})(window.jQuery);
</script>