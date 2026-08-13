<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>



    <div class="row">
      <div class="col-md-6">
        <?php
        // Prefix για τον αριθμό απόδειξης
        echo render_input(
          'settings[receipt_number_prefix]',
          _l('receipt_number_prefix') ?: 'Receipt Number Prefix',
          get_option('receipt_number_prefix')
        );
        ?>
      </div>

      <div class="col-md-6">
        <?php
        // Επόμενος αύξων αριθμός (μόνο νούμερα)
        echo render_input(
          'settings[next_receipt_number]',
          _l('next_receipt_number') ?: 'Next Receipt Number',
          get_option('next_receipt_number'),
          'number',
          ['min' => 1]
        );
        ?>
      </div>
    </div>

    <div class="row mtop15">
      <div class="col-md-6">
        <?php
        // Auto-send email (ναι/όχι) — χρησιμοποιεί το core helper αν υπάρχει,
        // αλλιώς πέφτει σε απλό checkbox.
        if (function_exists('render_yes_no_option')) {
            echo render_yes_no_option(
              'receipt_auto_send_email',
              _l('receipt_auto_send_email') ?: 'Auto-send receipt email'
            );
        } else {
            $checked = get_option('receipt_auto_send_email') == '1' ? 'checked' : '';
            ?>
            <div class="checkbox">
              <input type="hidden" name="settings[receipt_auto_send_email]" value="0">
              <label>
                <input type="checkbox" name="settings[receipt_auto_send_email]" value="1" <?php echo $checked; ?>>
                <?php echo _l('receipt_auto_send_email') ?: 'Auto-send receipt email'; ?>
              </label>
            </div>
            <?php
        }
        ?>
      </div>
    </div>

