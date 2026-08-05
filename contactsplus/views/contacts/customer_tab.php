<?php 
//modules/contactsplus/views/contacts/customer_tab.php
defined('BASEPATH') or exit('No direct script access allowed'); 

$CI = &get_instance();

// resolve client id όπως ήδη κάνεις...
$client_id = (int)(
  $CI->uri->segment(4)
  ?: $CI->input->get('userid')
  ?: $CI->input->get('customer_id')
  ?: $CI->input->get('clientid')
);

// φόρτωσε δεδομένα...
$contacts = [];
if ($client_id) {
  $CI->load->model('contactsplus/pmc_contact_company_model');
  $contacts = $CI->pmc_contact_company_model->get_by_client($client_id);
}
?>

<div class="clearfix mbottom20">
  <h4 class="pull-left">
    <?= _l('module_contactsplus'); ?> — <?= _l('contactsplus_contacts_for_client'); ?> #<?= (int)$client_id; ?>
  </h4>
  <div class="pull-right">
    <!-- NEW: core-like modal trigger -->
    <a href="#" class="btn btn-primary mright5" data-toggle="modal" data-target="#contactsplus_core_contact">
      <i class="fa fa-plus"></i> <?= _l('new_contact'); ?>
    </a>

    <!-- Link Existing -->
    <a href="#" class="btn btn-default" data-toggle="modal" data-target="#contactsplus_link_modal">
      <i class="fa fa-link"></i> <?= _l('contactsplus_btn_link_existing'); ?>
    </a>
  </div>
</div>

<?php
// Λίστα
echo $CI->load->view('contactsplus/contacts/list', [
  'client_id' => $client_id,
  'contacts'  => $contacts,
], true);

// ΕΝΣΩΜΑΤΩΣΗ ΤΟΥ CORE-LIKE MODAL (που φτιάξαμε πιο πάνω)
echo $CI->load->view('contactsplus/contacts/core_contact_modal', [
  'customer_id' => (int)$client_id,
], true);
?>
