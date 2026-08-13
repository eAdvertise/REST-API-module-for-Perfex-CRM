<?php defined('BASEPATH') or exit('No direct script access allowed');

function send_mail_template_custom($to, $subject, $message, $cc = [], $attachments = [])
{
    $CI =& get_instance();
    $CI->load->library('email');

    $CI->email->clear(true);
    $CI->email->initialize(['mailtype' => 'html']);
    $CI->email->from(get_option('smtp_email'), get_option('companyname'));
    $CI->email->to($to);

    if (!empty($cc)) {
        $CI->email->cc($cc);
    }

    foreach ($attachments as $att) {
        $attachment = $att['attachment'] ?? ($att['content'] ?? null);
        if ($attachment === null) {
            continue;
        }

        $CI->email->attach($attachment, 'attachment', $att['filename'] ?? 'attachment.pdf', $att['type'] ?? 'application/pdf');
    }

    $CI->email->subject($subject);
    $CI->email->message(paymentsonaccount_wrap_core_email_html($message));

    return $CI->email->send();
}

function paymentsonaccount_wrap_core_email_html($message)
{
    $message = (string)$message;

    if (stripos($message, '<html') !== false) {
        return $message;
    }

    $header = function_exists('get_option') ? (string)get_option('email_header') : '';
    $footer = function_exists('get_option') ? (string)get_option('email_footer') : '';

    if (trim($header) === '') {
        $company = function_exists('get_option') ? (get_option('invoice_company_name') ?: get_option('companyname')) : '';
        $header = '<!doctype html><html><head><meta charset="utf-8"></head>'
            . '<body style="margin:0;padding:0;background:#f5f7fa;font-family:Arial,Helvetica,sans-serif;color:#444;">'
            . '<table width="100%" cellpadding="0" cellspacing="0" style="background:#f5f7fa;padding:24px 0;"><tr><td align="center">'
            . '<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border:1px solid #e5e7eb;border-radius:4px;overflow:hidden;">'
            . '<tr><td style="padding:20px 24px;background:#323a45;color:#ffffff;font-size:18px;font-weight:bold;">'
            . htmlspecialchars((string)$company, ENT_QUOTES, 'UTF-8')
            . '</td></tr><tr><td style="padding:24px;font-size:14px;line-height:1.6;">';
    }

    if (trim($footer) === '') {
        $footer = '</td></tr></table></td></tr></table></body></html>';
    }

    $merge = [
        '{email_content}' => $message,
        '{message}'       => $message,
        '{companyname}'   => function_exists('get_option') ? (get_option('invoice_company_name') ?: get_option('companyname')) : '',
    ];

    if (strpos($header . $footer, '{email_content}') !== false || strpos($header . $footer, '{message}') !== false) {
        return strtr($header . $footer, $merge);
    }

    return strtr($header, $merge) . $message . strtr($footer, $merge);
}
