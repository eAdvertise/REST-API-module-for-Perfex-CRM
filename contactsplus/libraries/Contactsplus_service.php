<?php
// modules/contactsplus/libraries/Contactsplus_service.php
defined('BASEPATH') or exit('No direct script access allowed');

class Contactsplus_service
{
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('contactsplus/pmc_contacts_model');
        $this->CI->load->model('contactsplus/pmc_contacts_bridge_model');
    }

    /**
     * Δημιουργεί core contact (tblcontacts) για PMC επαφή + bridge,
     * και option για αποστολή set-password email.
     */
    public function enable_portal_access($pmc_contact_id, $client_id, $email, $send_set_password_email = 0)
    {
        $contact = $this->CI->pmc_contacts_model->find($pmc_contact_id);
        if (!$contact) { throw new Exception(_l('contactsplus_err_contact_not_found')); }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception(_l('contactsplus_err_invalid_email'));
        }

        $this->CI->load->model('clients_model');
        $data = [
            'clientid'    => (int)$client_id, // <-- σημαντικό: clientid (όχι client_id)
            'firstname'   => (string)$contact->firstname,
            'lastname'    => (string)$contact->lastname,
            'email'       => (string)$email,
            'phonenumber' => (string)$contact->phone,
            'is_primary'  => 0,
            'active'      => 1,
        ];

        $tblcontact_id = $this->CI->clients_model->add_contact($data, $send_set_password_email ? true : false);
        if (!$tblcontact_id) { throw new Exception('Failed to create core contact'); }

        $this->CI->pmc_contacts_bridge_model->upsert($pmc_contact_id, $client_id, $tblcontact_id);
        $this->CI->pmc_contacts_model->set_portal_access($pmc_contact_id, 1);

        return $tblcontact_id;
    }
}
