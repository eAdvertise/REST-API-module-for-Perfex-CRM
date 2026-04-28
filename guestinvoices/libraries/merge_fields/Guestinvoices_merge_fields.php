<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Guestinvoices_primary_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Primary Contact Email',
                'key'       => '{primary_contact_email}',
                'available' => ['invoice','estimate','credit_note'],
            ],
        ];
    }

    /**
     * Γεμίζει το {primary_contact_email} για invoice/estimate/credit_note.
     * @param  int $rel_id
     * @return array
     */
    public function format($rel_id)
    {
        $fields = [];
        if (empty($rel_id)) {
            return $fields;
        }

        // Models
        $this->ci->load->model('clients_model');

        $clientId = null;

        switch ($this->for) {
            case 'invoice':
                $this->ci->load->model('invoices_model');
                $inv = $this->ci->invoices_model->get($rel_id);
                if ($inv && !empty($inv->clientid)) {
                    $clientId = (int)$inv->clientid;
                }
                break;

            case 'estimate':
                $this->ci->load->model('estimates_model');
                $est = $this->ci->estimates_model->get($rel_id);
                if ($est && !empty($est->clientid)) {
                    $clientId = (int)$est->clientid;
                }
                break;

            case 'credit_note':
                $this->ci->load->model('credit_notes_model');
                $cn = $this->ci->credit_notes_model->get($rel_id);
                if ($cn && !empty($cn->clientid)) {
                    $clientId = (int)$cn->clientid;
                }
                break;

            default:
                // Δεν υποστηρίζεται άλλος τύπος
                break;
        }

        $email = '';
        if ($clientId) {
            $primaryId = get_primary_contact_user_id($clientId);
            if ($primaryId) {
                $contact = $this->ci->clients_model->get_contact($primaryId);
                if ($contact && !empty($contact->email)) {
                    $email = $contact->email;
                }
            }
        }

        $fields['{primary_contact_email}'] = $email;
        return $fields;
    }
}
