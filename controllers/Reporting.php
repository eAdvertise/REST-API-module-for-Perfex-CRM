<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Reporting extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('api_metrics_model');
        $this->load->model('api_model');
    }
    
    /**
     * Main reporting dashboard
     */
    public function index()
    {
        $data['title'] = _l('api_reporting');
        $data['api_keys'] = $this->api_model->get_all_api_keys();
        
        // Get default date range (last 30 days)
        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', strtotime('-30 days'));
        
        $data['start_date'] = $this->input->get('start_date') ?: $start_date;
        $data['end_date'] = $this->input->get('end_date') ?: $end_date;
        $data['api_key'] = $this->input->get('api_key') ?: '';
        
        // Get usage statistics
        $data['usage_stats'] = $this->api_metrics_model->get_usage_stats(
            $data['api_key'] ?: null,
            $data['start_date'],
            $data['end_date']
        );
        
        // Get endpoint statistics
        $data['endpoint_stats'] = $this->api_metrics_model->get_endpoint_stats(
            $data['api_key'] ?: null,
            $data['start_date'],
            $data['end_date']
        );
        
        // Get hourly usage for charts
        $data['hourly_usage'] = $this->api_metrics_model->get_hourly_usage(
            $data['api_key'] ?: null,
            $data['start_date'],
            $data['end_date']
        );
        
        // Get response code distribution
        $data['response_codes'] = $this->api_metrics_model->get_response_code_distribution(
            $data['api_key'] ?: null,
            $data['start_date'],
            $data['end_date']
        );
        
        // Get API key summary
        $data['api_key_summary'] = $this->api_metrics_model->get_api_key_summary(
            $data['start_date'],
            $data['end_date']
        );
        
        $this->load->view('api_reporting', $data);
    }
    
    /**
     * Get chart data via AJAX
     */
    public function get_chart_data()
    {
        $chart_type = $this->input->get('chart_type');
        $api_key = $this->input->get('api_key') ?: null;
        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');
        
        $data = [];
        
        switch ($chart_type) {
            case 'hourly_usage':
                $data = $this->api_metrics_model->get_hourly_usage($api_key, $start_date, $end_date);
                break;
            case 'daily_usage':
                $data = $this->api_metrics_model->get_daily_usage($api_key, $start_date, $end_date);
                break;
            case 'response_codes':
                $data = $this->api_metrics_model->get_response_code_distribution($api_key, $start_date, $end_date);
                break;
            case 'endpoint_stats':
                $data = $this->api_metrics_model->get_endpoint_stats($api_key, $start_date, $end_date);
                break;
        }
        
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
    
    /**
     * Export usage data
     */
    public function export()
    {
        $api_key = $this->input->get('api_key') ?: null;
        $start_date = $this->input->get('start_date');
        $end_date = $this->input->get('end_date');
        $format = $this->input->get('format') ?: 'csv';
        
        $this->load->library('excel');
        
        $data = $this->api_metrics_model->get_api_key_summary($start_date, $end_date);
        
        $excel = new PHPExcel();
        $excel->getProperties()->setTitle('API Usage Report');
        
        $sheet = $excel->getActiveSheet();
        $sheet->setTitle('API Usage Summary');
        
        // Headers
        $headers = ['API Key', 'Total Requests', 'Avg Response Time', 'Success Requests', 'Error Requests'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header);
            $col++;
        }
        
        // Data
        $row = 2;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item->api_key);
            $sheet->setCellValue('B' . $row, $item->total_requests);
            $sheet->setCellValue('C' . $row, round($item->avg_response_time, 4));
            $sheet->setCellValue('D' . $row, $item->success_requests);
            $sheet->setCellValue('E' . $row, $item->error_requests);
            $row++;
        }
        
        $filename = 'api_usage_report_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        $writer->save('php://output');
    }
}
