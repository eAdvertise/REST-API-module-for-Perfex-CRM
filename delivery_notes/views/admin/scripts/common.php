<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<link rel="stylesheet" type="text/css" href="<?= module_dir_url(DELIVERY_NOTE_MODULE_NAME, 'assets/css/admin.css'); ?>" />

<!-- Module custom admin script -->
<script src="<?= module_dir_url(DELIVERY_NOTE_MODULE_NAME, 'assets/js/admin.js'); ?>"></script>

<!-- Script to handle batch conversion to invoice -->
<script>
    function add_batch_delivery_notes_invoice(data = {}) {
        $.post(
            admin_url + "delivery_notes/batch_invoice_modal",
            data,
            function(responseHtml) {
                $("#modal-wrapper").html(responseHtml);
                if ($("#batch-invoice-modal").is(":hidden")) {
                    $("#batch-invoice-modal").modal({
                        backdrop: "static",
                        show: true,
                    });
                }
                init_selectpicker();
                init_datepicker();

                var $filterByClientSelect = $("#batch-delivery-filter");
                $filterByClientSelect.on("changed.bs.select", function() {
                    if ($filterByClientSelect.val() !== "") {
                        $(".batch_delivery_item").each(function() {
                            if ($(this).data("clientid") == $filterByClientSelect.val()) {
                                $(this).find("input, select").prop("disabled", false);
                                $(this).removeClass("hide");
                            } else {
                                $(this).addClass("hide");
                                $(this).find("input, select").prop("disabled", true);
                            }
                        });
                    } else {
                        $(".batch_delivery_item").each(function() {
                            $(this).removeClass("hide");
                            $(this).find("input, select").prop("disabled", false);
                        });
                    }
                });
                appValidateForm($("#batch-delivery-form"), {});

                $(".batch_delivery_item").each(function() {
                    var invoiceLine = $(this).find('[name^="delivery_note"]');

                    invoiceLine
                        .filter('select[name$="[mode]"]')
                        .each(function() {
                            var field = $(this);
                            field.rules("add", {
                                required: function() {
                                    var isRequired = false;
                                    var rowFields = field
                                        .closest(".batch_delivery_item")
                                        .find("input, select");
                                    rowFields
                                        .filter(
                                            'select[name$="[mode]"]'
                                        )
                                        .each(function() {
                                            if ($(this).val() != "") {
                                                isRequired = true;
                                            }

                                            if ($(this).hasClass("selectpicker") &&
                                                isRequired) {
                                                field.prop("required", true);
                                                $(this).selectpicker("refresh");
                                            }
                                        });
                                    return isRequired;
                                },
                            });
                        });
                });
            }
        );
    }
</script>
