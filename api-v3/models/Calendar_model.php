<?php

use app\services\utilities\Arr;

defined('BASEPATH') or exit('No direct script access allowed');

class Calendar_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function get_events($id = '', $playground = false)
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . ($playground ? 'playground_' : '') . 'events');
        if ($id >0) {
            $this->db->where('eventid', $id);
        }
        return $this->db->get()->result_array();
    }

    /**
     * Add new event
     * @param array $data event $_POST data
     */
    public function event($data, $playground = false)
    {
        $data['start']  = to_sql_date($data['start'], true);
        if ($data['end'] == '') {
            unset($data['end']);
        } else {
            $data['end'] = to_sql_date($data['end'], true);
        }

        $data['description'] = nl2br($data['description']);
        if (isset($data['eventid'])) {
            $this->db->where('eventid', $data['eventid']);
            $event = $this->db->get(db_prefix() . ($playground ? 'playground_' : '') . 'events')->row();
            if (!$event) {
                return false;
            }
            if ($event->isstartnotified == 1) {
                if ($data['start'] > $event->start) {
                    $data['isstartnotified'] = 0;
                }
            }

            $data = hooks()->apply_filters('event_update_data', $data, $data['eventid']);

            $this->db->where('eventid', $data['eventid']);
            $this->db->update(db_prefix() . ($playground ? 'playground_' : '') . 'events', $data);
            if ($this->db->affected_rows() > 0) {
                return true;
            }

            return false;
        }

        $data = hooks()->apply_filters('event_create_data', $data);

        $this->db->insert(db_prefix() . ($playground ? 'playground_' : '') . 'events', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            return true;
        }

        return false;
    }
}