<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Guest_checkout_service
{
    /** @var CI_Controller */
    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    public function findOrCreateGuest(array $payload, array $options = [])
    {
        $this->CI->load->model('clients_model');

        $email = trim((string)($payload['email'] ?? ''));
        if ($email === '') {
            return [0, 0, 'Missing guest email'];
        }

        $name = trim((string)($payload['name'] ?? $payload['full_name'] ?? ''));
        $updateExistingName = isset($options['update_existing_name']) ? (bool)$options['update_existing_name'] : true;

        $contact = $this->CI->db->where('email', $email)->get(db_prefix() . 'contacts')->row();
        if ($contact) {
            $client_id  = (int)$contact->userid;
            $contact_id = (int)$contact->id;

            if ($updateExistingName && $name !== '') {
                $parts = preg_split('/\s+/', $name, 2);
                $firstname = trim($parts[0] ?? $name);
                $lastname  = trim($parts[1] ?? '');

                $this->CI->db->where('userid', $client_id)->update(db_prefix() . 'clients', [
                    'company' => $name,
                ]);

                $this->CI->db->where('id', $contact_id)->update(db_prefix() . 'contacts', [
                    'firstname' => $firstname,
                    'lastname'  => $lastname,
                ]);
            }

            return [$client_id, $contact_id, ''];
        }

        $company   = trim((string)($payload['company'] ?? $payload['company_name'] ?? ''));
        $firstname = trim((string)($payload['firstname'] ?? ''));
        $lastname  = trim((string)($payload['lastname'] ?? ''));

        if ($name !== '' && $company === '' && $firstname === '' && $lastname === '') {
            $company = $name;
            $parts = preg_split('/\s+/', $name, 2);
            $firstname = trim($parts[0] ?? $name);
            $lastname  = trim($parts[1] ?? '');
        }

        if ($company === '') {
            $company = trim($firstname . ' ' . $lastname);
            if ($company === '') {
                $company = $email;
            }
        }

        $clientData = [
            'company'         => $company,
            'phonenumber'     => (string)($payload['phonenumber'] ?? ''),
            'website'         => (string)($payload['website'] ?? ''),
            'billing_street'  => (string)($payload['address'] ?? ''),
            'billing_city'    => (string)($payload['city'] ?? ''),
            'billing_state'   => (string)($payload['state'] ?? ''),
            'billing_zip'     => (string)($payload['zip'] ?? ''),
            'billing_country' => (string)($payload['country'] ?? ''),
            'active'          => 1,
        ];

        $client_id = (int)$this->CI->clients_model->add($clientData);
        if ($client_id <= 0) {
            return [0, 0, 'Failed to create client'];
        }

        $contactData = [
            'firstname' => $firstname !== '' ? $firstname : $company,
            'lastname'  => $lastname,
            'email'     => $email,
            'phonenumber' => (string)($payload['phonenumber'] ?? ''),
            'title'     => 'Guest',
            'is_primary'=> 1,
            'donotsendwelcomeemail' => 1,
        ];

        $contact_id = (int)$this->CI->clients_model->add_contact($contactData, $client_id);
        if ($contact_id <= 0) {
            return [$client_id, 0, 'Failed to create contact'];
        }

        if ($name !== '') {
            $parts = preg_split('/\s+/', $name, 2);
            $fn = trim($parts[0] ?? $name);
            $ln = trim($parts[1] ?? '');

            $this->CI->db->where('userid', $client_id)->update(db_prefix() . 'clients', [
                'company' => $name,
            ]);

            $this->CI->db->where('id', $contact_id)->update(db_prefix() . 'contacts', [
                'firstname' => $fn,
                'lastname'  => $ln,
            ]);
        } else {
            $needsGuestName = (trim((string)($payload['company'] ?? '')) === ''
                && trim((string)($payload['firstname'] ?? '')) === ''
                && trim((string)($payload['lastname'] ?? '')) === '');

            if ($needsGuestName) {
                $guestName = 'Guest' . $client_id;

                $this->CI->db->where('userid', $client_id)->update(db_prefix() . 'clients', [
                    'company' => $guestName,
                ]);

                $this->CI->db->where('id', $contact_id)->update(db_prefix() . 'contacts', [
                    'firstname' => $guestName,
                    'lastname'  => '',
                ]);
            }
        }

        return [$client_id, $contact_id, ''];
    }

    public function taxnameFromIds($taxes_id)
    {
        if (empty($taxes_id)) {
            return [];
        }

        if (!is_array($taxes_id)) {
            $taxes_id = array_filter(array_map('trim', explode(',', (string)$taxes_id)));
        }

        $out = [];
        foreach ($taxes_id as $tid) {
            $tid = (int)$tid;
            if ($tid <= 0) {
                continue;
            }

            $tax = $this->CI->db->where('id', $tid)->get(db_prefix() . 'taxes')->row();
            if ($tax) {
                $out[] = $tax->name . '|' . number_format((float)$tax->taxrate, 2, '.', '');
            }
        }

        return $out;
    }

    public function buildNewItemsFromMixed(array $items)
    {
        $newitems = [];
        $order = 1;

        foreach ($items as $it) {
            if (!is_array($it)) {
                continue;
            }

            $qty = isset($it['qty']) ? (float)$it['qty'] : 1.0;
            if ($qty <= 0) {
                $qty = 1.0;
            }

            $itemId = (int)($it['item_id'] ?? $it['id'] ?? $it['itemid'] ?? 0);
            if ($itemId > 0) {
                $row = $this->CI->db->where('id', $itemId)->get(db_prefix() . 'items')->row();
                if (!$row) {
                    continue;
                }

                $newitems[] = [
                    'itemid'           => (string)$itemId,
                    'description'      => (string)$row->description,
                    'long_description' => (string)$row->long_description,
                    'qty'              => (string)$qty,
                    'rate'             => (string)$row->rate,
                    'unit'             => isset($row->unit) ? (string)$row->unit : '',
                    'order'            => (string)$order,
                    'taxname'          => $this->taxnameFromIds($it['taxes_id'] ?? []),
                ];
                $order++;
                continue;
            }

            $desc = trim((string)($it['description'] ?? ''));
            if ($desc === '') {
                continue;
            }

            $rate = isset($it['rate']) ? (float)$it['rate'] : 0.0;

            $newitems[] = [
                'description'      => $desc,
                'long_description' => (string)($it['long_description'] ?? ''),
                'qty'              => (string)$qty,
                'rate'             => (string)$rate,
                'unit'             => (string)($it['unit'] ?? ''),
                'order'            => (string)$order,
                'taxname'          => $this->taxnameFromIds($it['taxes_id'] ?? []),
            ];
            $order++;
        }

        return $newitems;
    }

    public function computeTotalsWithTaxes(array $newitems)
    {
        $subtotal = 0.0;
        $tax_total = 0.0;

        foreach ($newitems as $it) {
            $qty  = (float)($it['qty'] ?? 0);
            $rate = (float)($it['rate'] ?? 0);
            $line = $qty * $rate;
            $subtotal += $line;

            if (!empty($it['taxname']) && is_array($it['taxname'])) {
                foreach ($it['taxname'] as $tx) {
                    $parts = explode('|', (string)$tx);
                    $pct = isset($parts[1]) ? (float)$parts[1] : 0.0;
                    if ($pct > 0) {
                        $tax_total += ($line * $pct / 100.0);
                    }
                }
            }
        }

        $subtotal = round($subtotal, 2);
        $tax_total = round($tax_total, 2);
        $total = round($subtotal + $tax_total, 2);

        return [
            'subtotal'  => number_format($subtotal, 2, '.', ''),
            'total'     => number_format($total, 2, '.', ''),
            'tax_total' => number_format($tax_total, 2, '.', ''),
        ];
    }

    public function applyAutoNumber(array &$invoice_data, &$didAutoNumber)
    {
        $didAutoNumber = false;

        if (!empty($invoice_data['number'])) {
            return;
        }

        $next = (int)get_option('next_invoice_number');
        if ($next <= 0) {
            $next = 1;
        }

        $invoice_data['number'] = $next;
        $didAutoNumber = true;
    }

    public function bumpNextInvoiceNumberIfNeeded($didAutoNumber, $usedNumber)
    {
        if (!$didAutoNumber) {
            return;
        }

        $usedNumber = (int)$usedNumber;
        if ($usedNumber <= 0) {
            return;
        }

        $current = (int)get_option('next_invoice_number');
        if ($current <= $usedNumber) {
            update_option('next_invoice_number', $usedNumber + 1);
        }
    }
}
