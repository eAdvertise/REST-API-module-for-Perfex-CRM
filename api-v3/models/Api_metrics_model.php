<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Api_metrics_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Get API usage statistics
     */
    public function get_usage_stats($api_key = null, $start_date = null, $end_date = null)
    {
        $this->db->select('
            COUNT(*) as total_requests,
            AVG(response_time) as avg_response_time,
            MAX(response_time) as max_response_time,
            MIN(response_time) as min_response_time,
            COUNT(CASE WHEN response_code >= 200 AND response_code < 300 THEN 1 END) as success_requests,
            COUNT(CASE WHEN response_code >= 400 THEN 1 END) as error_requests,
            COUNT(CASE WHEN response_code >= 500 THEN 1 END) as server_errors
        ');
        
        $this->db->from(db_prefix() . 'api_usage_logs');
        
        if ($api_key) {
            $this->db->where('api_key', $api_key);
        }
        
        if ($start_date) {
            $this->db->where('timestamp >=', strtotime(date('Y-m-d 00:00:00', strtotime($start_date))));
        }
        
        if ($end_date) {
            $this->db->where('timestamp <=', strtotime(date('Y-m-d 23:59:59', strtotime($end_date))));
        }
        
        return $this->db->get()->row();
    }
    
    /**
     * Get endpoint usage statistics
     */
    public function get_endpoint_stats($api_key = null, $start_date = null, $end_date = null, $limit = 10)
    {
        $this->db->select('
            endpoint,
            COUNT(*) as request_count,
            AVG(response_time) as avg_response_time,
            COUNT(CASE WHEN response_code >= 200 AND response_code < 300 THEN 1 END) as success_count,
            COUNT(CASE WHEN response_code >= 400 THEN 1 END) as error_count
        ');
        
        $this->db->from(db_prefix() . 'api_usage_logs');
        
        if ($api_key) {
            $this->db->where('api_key', $api_key);
        }
        
        if ($start_date) {
            $this->db->where('timestamp >=', strtotime(date('Y-m-d 00:00:00', strtotime($start_date))));
        }
        
        if ($end_date) {
            $this->db->where('timestamp <=', strtotime(date('Y-m-d 23:59:59', strtotime($end_date))));
        }
        
        $this->db->group_by('endpoint');
        $this->db->order_by('request_count', 'DESC');
        $this->db->limit($limit);
        
        return $this->db->get()->result();
    }
    
    /**
     * Get hourly usage data for charts
     */
    public function get_hourly_usage($api_key = null, $start_date = null, $end_date = null)
    {
        $this->db->select('
            FROM_UNIXTIME(timestamp, "%Y-%m-%d %H:00:00") as hour,
            COUNT(*) as request_count,
            AVG(response_time) as avg_response_time
        ');
        
        $this->db->from(db_prefix() . 'api_usage_logs');
        
        if ($api_key) {
            $this->db->where('api_key', $api_key);
        }
        
        if ($start_date) {
            $this->db->where('timestamp >=', strtotime(date('Y-m-d 00:00:00', strtotime($start_date))));
        }
        
        if ($end_date) {
            $this->db->where('timestamp <=', strtotime(date('Y-m-d 23:59:59', strtotime($end_date))));
        }
        
        $this->db->group_by('hour');
        $this->db->order_by('hour', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Get daily usage data for charts
     */
    public function get_daily_usage($api_key = null, $start_date = null, $end_date = null)
    {
        $this->db->select('
            FROM_UNIXTIME(timestamp, "%Y-%m-%d") as day,
            COUNT(*) as request_count,
            AVG(response_time) as avg_response_time
        ');
        
        $this->db->from(db_prefix() . 'api_usage_logs');
        
        if ($api_key) {
            $this->db->where('api_key', $api_key);
        }
        
        if ($start_date) {
            $this->db->where('timestamp >=', strtotime(date('Y-m-d 00:00:00', strtotime($start_date))));
        }
        
        if ($end_date) {
            $this->db->where('timestamp <=', strtotime(date('Y-m-d 23:59:59', strtotime($end_date))));
        }
        
        $this->db->group_by('day');
        $this->db->order_by('day', 'ASC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Get response code distribution
     */
    public function get_response_code_distribution($api_key = null, $start_date = null, $end_date = null)
    {
        $this->db->select('
            response_code,
            COUNT(*) as count
        ');
        
        $this->db->from(db_prefix() . 'api_usage_logs');
        
        if ($api_key) {
            $this->db->where('api_key', $api_key);
        }
        
        if ($start_date) {
            $this->db->where('timestamp >=', strtotime(date('Y-m-d 00:00:00', strtotime($start_date))));
        }
        
        if ($end_date) {
            $this->db->where('timestamp <=', strtotime(date('Y-m-d 23:59:59', strtotime($end_date))));
        }
        
        $this->db->group_by('response_code');
        $this->db->order_by('count', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Get API key usage summary
     */
    public function get_api_key_summary($start_date = null, $end_date = null)
    {
        $this->db->select('
            api_key,
            COUNT(*) as total_requests,
            AVG(response_time) as avg_response_time,
            COUNT(CASE WHEN response_code >= 200 AND response_code < 300 THEN 1 END) as success_requests,
            COUNT(CASE WHEN response_code >= 400 THEN 1 END) as error_requests
        ');
        
        $this->db->from(db_prefix() . 'api_usage_logs');
        
        if ($start_date) {
            $this->db->where('timestamp >=', strtotime(date('Y-m-d 00:00:00', strtotime($start_date))));
        }
        
        if ($end_date) {
            $this->db->where('timestamp <=', strtotime(date('Y-m-d 23:59:59', strtotime($end_date))));
        }
        
        $this->db->group_by('api_key');
        $this->db->order_by('total_requests', 'DESC');
        
        return $this->db->get()->result();
    }
    
    /**
     * Clean old logs (older than specified days)
     */
    public function clean_old_logs($days = 30)
    {
        $cutoff_timestamp = time() - ($days * 24 * 60 * 60);
        $this->db->where('timestamp <', $cutoff_timestamp);
        $this->db->delete(db_prefix() . 'api_usage_logs');
        
        return $this->db->affected_rows();
    }
}
