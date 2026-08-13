<?php defined('BASEPATH') or exit('No direct script access allowed');

class PoA_reports_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Επιστρέφει πίνακα με πιστωτικά υπόλοιπα ανά πελάτη.
     * - Αν υπάρχει πίνακας εφαρμογών με ποσό (π.χ. tblreceipt_applications), τότε:
     *      unapplied = receipt.amount - SUM(applied_amount)
     * - Αλλιώς:
     *      αν invoices_applied είναι κενό -> όλο το amount είναι credit
     *      αν ΔΕΝ είναι κενό αλλά δεν έχουμε ποσά -> θεωρούμε 0 (συντηρητικά)
     *
     * @param array $opts ['min_credit'=>float,'only_positive'=>bool]
     * @return array rows: clientid, company, currency_id, credit, receipts, last_receipt_date
     */
    public function get_client_credit_balances(array $opts = [])
    {
        $onlyPositive = array_key_exists('only_positive', $opts) ? (bool)$opts['only_positive'] : true;
        $minCredit    = isset($opts['min_credit']) ? (float)$opts['min_credit'] : 0.00;

        $hasAppsTbl = $this->db->table_exists('tblreceipt_applications'); // <— αν έχεις άλλο όνομα, άλλαξέ το εδώ

        if ($hasAppsTbl) {
            // 1) Με πίνακα applications που έχει applied_amount
            $sql = "
                SELECT
                    c.userid                   AS clientid,
                    " . get_sql_select_client_company() . " AS company,
                    COALESCE(r.currency, 0)    AS currency_id,
                    ROUND(SUM(GREATEST(r.amount - COALESCE(app.applied,0), 0)), 2) AS credit,
                    COUNT(r.id)                AS receipts,
                    MAX(r.datecreated)         AS last_receipt_date
                FROM tblreceipts r
                JOIN tblclients c ON c.userid = r.client_id
                LEFT JOIN (
                    SELECT receipt_id, SUM(applied_amount) AS applied
                    FROM tblreceipt_applications
                    GROUP BY receipt_id
                ) app ON app.receipt_id = r.id
                GROUP BY c.userid, company, currency_id
            ";
            if ($onlyPositive || $minCredit > 0) {
                $sql .= " HAVING credit > " . ($minCredit > 0 ? $this->db->escape_str($minCredit) : "0");
            }
            $sql .= " ORDER BY credit DESC, company ASC";
            return $this->db->query($sql)->result_array();
        }

        // 2) Χωρίς πίνακα applications με ποσό – fallback λογική
        // Θεωρούμε credit = SUM(amount) μόνο για αποδείξεις που δεν έχουν αντιστοιχίσεις
        // (invoices_applied IS NULL ή '' ή '[]'). Αν έχει τιμές, το μετράμε ως fully applied.
        $sql = "
            SELECT
                c.userid                   AS clientid,
                " . get_sql_select_client_company() . " AS company,
                COALESCE(r.currency, 0)    AS currency_id,
                ROUND(SUM(
                    CASE
                        WHEN r.invoices_applied IS NULL
                             OR TRIM(r.invoices_applied) = ''
                             OR TRIM(r.invoices_applied) = '[]'
                        THEN r.amount
                        ELSE 0
                    END
                ), 2) AS credit,
                COUNT(r.id)                AS receipts,
                MAX(r.datecreated)         AS last_receipt_date
            FROM tblreceipts r
            JOIN tblclients c ON c.userid = r.client_id
            GROUP BY c.userid, company, currency_id
        ";
        if ($onlyPositive || $minCredit > 0) {
            $sql .= " HAVING credit > " . ($minCredit > 0 ? $this->db->escape_str($minCredit) : "0");
        }
        $sql .= " ORDER BY credit DESC, company ASC";

        return $this->db->query($sql)->result_array();
    }
}
