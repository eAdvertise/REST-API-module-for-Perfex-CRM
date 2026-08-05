let wh_tribute;
$(function () {
	"use strict";
	$(document.body).on('change', '#webhook_for', function (event) {
		var selectedValue = $(this).val()
		var fields = _.filter(merge_fields, function (num) {
			return typeof num[selectedValue] != "undefined" || typeof num["other"] != "undefined";
		});		

		var other_index = _.findIndex(fields, function (data) {
			return _.allKeys(data)[0] == "other";
		});
		var selected_index = _.findIndex(fields, function (data) {
			return _.allKeys(data)[0] == selectedValue;
		});

		var options = [];

		if (fields[selected_index]) {
			if (selectedValue == "tasks") {
				options.push({ key: "Task Link (Staff)", value: "{staff_task_link}" });
				options.push({ key: "Task Link (Client)", value: "{client_task_link}" });
			}
			if (selectedValue == "projects") {
				options.push({ key: "Project Link (Staff)", value: "{staff_project_link}" });
				options.push({ key: "Project Link (Client)", value: "{client_project_link}" });
			}
			if (selectedValue == "ticket") {
				options.push({ key: "Ticket URL (Staff)", value: "{staff_ticket_link}" });
				options.push({ key: "Ticket URL (Client)", value: "{client_ticket_link}" });
			}
			fields[selected_index][selectedValue].forEach(field => {
				if (field.name != "" && field.key != "{task_link}" && field.key != "{project_link}" && field.key != "{ticket_url}") {
					options.push({ key: field.name, value: field.key });
				}
			})
		}
		if (fields[other_index]) {
			fields[other_index]["other"].forEach(field => {
				if (field.name != "") {
					options.push({ key: field.name, value: field.key });
				}
			})
		}
		wh_tribute = new Tribute({
			values: options,
			selectClass: "highlights"
		});
		wh_tribute.detach(document.querySelectorAll(".mentionable"));
		wh_tribute.attach(document.querySelectorAll(".mentionable"));

		$('input[name="webhook_action[]"]').removeAttr('disabled');
		$('.status_change_checkbox, .accept_checkbox, .decline_checkbox, .sent_checkbox, .recurring_create, .checklist_item_created, .comment_added, .timer_deleted, .timer_started, .refund_created, .credit_applied, .converted_to_invoice, .converted_to_estimate, .converted_to_customer, .marked_as_lost, .marked_as_junk, .invoice_copied, .marked_as_cancelled, .invoice_copied').addClass('hide');
		if (selectedValue == 'leads') {
			$('#webhook_action_edit').attr('disabled', true);
			$('#webhook_action_edit').removeAttr('checked');
			$('.status_change_checkbox, .converted_to_customer, .marked_as_lost, .marked_as_junk').removeClass('hide');
			$('#webhook_action_status_change').attr('disabled', false);
		} else if (selectedValue == 'ticket') {
			$('.status_change_checkbox').removeClass('hide');
			$('#webhook_action_status_change').attr('disabled', false);
		} else if (selectedValue == 'tasks') {
			$('#webhook_action_delete').attr('disabled', true);
			$('.checklist_item_created, .comment_added, .timer_deleted, .timer_started, .status_change_checkbox').removeClass('hide');
			$('#webhook_action_delete').removeAttr('checked');
		} else if (selectedValue == 'proposals') {
			$('.accept_checkbox, .decline_checkbox, .sent_checkbox, .converted_to_invoice, .converted_to_estimate').removeClass('hide');
			$('#webhook_action_accept, #webhook_action_decline, #webhook_action_sent').attr('disabled', false);
		} else if (selectedValue == 'estimate') {
			$('.accept_checkbox, .decline_checkbox, .sent_checkbox, .converted_to_invoice').removeClass('hide');
		} else if (selectedValue == 'event') {
			$('#webhook_action_add, #webhook_action_delete').attr('disabled', true);
			$('#webhook_action_add, #webhook_action_delete').removeAttr('checked');
		} else if (selectedValue == 'expenses') {
			$('#webhook_action_delete').attr('disabled', true);
			$('.recurring_create').removeClass('hide');
			$('#webhook_action_recurring_create').attr('disabled', false);
		} else if (selectedValue == 'invoice') {
			$('.recurring_create, .invoice_copied, .sent_checkbox, .marked_as_cancelled, .status_change_checkbox').removeClass('hide');
			$('#webhook_action_recurring_create').attr('disabled', false);
		} else if (selectedValue == 'invoice_items') {
			$('#webhook_action_delete').attr('disabled', true);
		} else if (selectedValue == 'credit_note') {
			$('.sent_checkbox, .refund_created, .credit_applied').removeClass('hide');
			$('#webhook_action_recurring_create').attr('disabled', false);
		} else {
			$('#webhook_action_status_change').attr('disabled', true);
			$('#webhook_action_accept, #webhook_action_decline, #webhook_action_sent').attr('disabled', true);
		}
	});
	$("#webhook_for").trigger('change');

	appValidateForm($("#webhook-form"), {
		webhook_name: 'required',
		webhook_for: 'required',
		request_url: 'required',
		"webhook_action[]": 'required',
	});

	$(document.body).on("click", "#test_webhook_btn", function (event) {
		event.preventDefault();

		var requestUrl = $('input[name="request_url"]').val();
		var requestMethod = ($("#request_method").val() || "").toUpperCase();
		var requestFormat = ($("#request_format").val() || "").toUpperCase();

		if (!requestUrl) {
			alert_float("danger", "Please enter a valid request URL.");
			return;
		}

		if ((requestMethod === "GET" || requestMethod === "DELETE") && requestFormat === "JSON") {
			alert_float("warning", "GET/DELETE methods do not support JSON format.");
			return;
		}
		$.ajax({
			type: "post",
			url: `${admin_url}webhooks/test_webhook`,
			data: $("#webhook-form").serialize(),
			dataType: "json",
			success: function (response) {
				alert_float(response.success, response.message);
			}
		});
	});

	initDataTable('.table-webhooks_cron_job_queue_table', `${admin_url}webhooks/webhooks_cron_job_queue_table`);
});

function refreshTribute() {
	"use strict";
	if ($("#webhook_for").val() != "") {
		wh_tribute.attach(document.querySelectorAll(".mentionable"));
	}
}
