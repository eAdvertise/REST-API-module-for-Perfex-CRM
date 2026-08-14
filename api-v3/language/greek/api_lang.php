<?php

# Version 1.0.0
$lang['api']                                                = 'API';
$lang['api_management']                                     = 'υσερσ μαναγεμεντ';
$lang['api_permissions']                                    = 'API περμισσιονσ';
$lang['api_guide']                                          = 'δοκυμεντατιον';
$lang['new_user_api']		                                = 'Νέο υσερ';
$lang['edit_user_api']		                                = 'Επεξεργασία υσερ';
$lang['user_api']		                                    = 'υσερ';
$lang['api_staff_id_optional']                              = 'σταφφ ID (οπτιοναλ - λινκσ αυτό Token σε ένα σταφφ μεμβερ για σταφφ-λεβελ Δεδομένα βισιβιλιτυ)';
$lang['api_v3_settings']                                    = 'β3 πλατφορμ Ρυθμίσεις (MCP, Webhooks 2.0, σεκυριτυ)';
$lang['api_mcp_enabled_label']                              = 'εναβλε MCP σερβερ (AI αγεντσ)';
$lang['api_mcp_enabled_help']                               = 'εξποσεσ POST /API/μκπ με 148 περμισσιον-φιλτερεδ CRM τοολσ για κλαυδε, κηατγπτ, κυρσορ, ν8ν και ανυ MCP-κομπατιβλε κλιεντ. Απενεργοποιημένο βυ Προεπιλογή.';
$lang['api_staff_visibility_label']                         = 'σταφφ-λεβελ Δεδομένα βισιβιλιτυ';
$lang['api_staff_visibility_help']                          = 'τοκενσ λινκεδ σε ένα νον-αδμιν σταφφ μεμβερ (σταφφ ID φιελδ) ονλυ σεε Δεδομένα τηατ σταφφ μεμβερ καν ακκεσσ. υνλινκεδ/αδμιν τοκενσ κεεπ φυλλ βισιβιλιτυ.';
$lang['api_auth_throttle_limit_label']                      = 'Απέτυχε-αυτη τηροττλε λιμιτ (περ IP / 15 μιν)';
$lang['api_auth_throttle_limit_help']                       = 'ιπσ εξκεεδινγ αυτό μανυ Απέτυχε αυτηεντικατιονσ ωιτηιν 15 μινυτεσ ρεκειβε HTTP 429. σετ 0 σε δισαβλε.';
$lang['api_webhook_delivery_mode_label']                    = 'Webhook Παράδοση μοδε';
$lang['api_webhook_mode_immediate']                         = 'ιμμεδιατε (Αποστολή ωηεν το εβεντ φιρεσ)';
$lang['api_webhook_mode_queue']                             = 'κυευεδ (ασυνκ βια κρον, με ρετριεσ & βακκοφφ)';
$lang['api_webhook_delivery_mode_help']                     = 'κυευεδ μοδε δελιβερσ Webhooks ιν το βακκγρουνδ βια περφεξ κρον (με ένα αδμιν-λοαδ φαλλβακκ) και ρετριεσ φαιλυρεσ με εξπονεντιαλ βακκοφφ.';
$lang['api_webhook_ssrf_strict_label']                      = 'στρικτ SSRF προτεκτιον για Webhook υρλσ';
$lang['api_webhook_ssrf_strict_help']                       = 'κλουδ-μεταδατα και λοοπβακκ ταργετσ είναι αλωαυσ βλοκκεδ; στρικτ μοδε αδδιτιοναλλυ βλοκκσ πριβατε LAN ρανγεσ (10.ξ, 172.16.ξ, 192.168.ξ).';
$lang['api_webhook_ssl_verify_label']                       = 'βεριφυ TLS κερτιφικατεσ ον Webhook Παράδοση';
$lang['api_webhook_ssl_verify_help']                        = 'ρεκομμενδεδ ON. δισαβλε ονλυ ιφ σας Webhook ενδποιντ υσεσ ένα σελφ-σιγνεδ κερτιφικατε.';
$lang['name_api']		                                    = 'Όνομα';
$lang['password_api']		                                = 'Κωδικός εισόδου';
$lang['repeat_passwork_api']	                            = 'ρεπεατ πασσωορκ';
$lang['token_api']		                                    = 'Token';
$lang['expiration_date']		                            = 'Ημερομηνία Λήξης';

$lang['permission_get']                                     = 'γετ';
$lang['permission_get_value']                               = 'γετ βαλυε';
$lang['permission_list']                                    = 'γετ λιστ';
$lang['permission_search']                                  = 'Αναζήτηση';
$lang['permission_create']                                  = 'Δημιουργία';
$lang['permission_delete']                                  = 'DELETE';
$lang['permission_update']                                  = 'Ενημέρωση';
$lang['permission_send']                                    = 'Αποστολή Email';
$lang['api_thirdparty_allowed_tables_label']                = 'τηιρδ-παρτυ κυστομ ταβλεσ - αλλοωεδ ταβλεσ';
$lang['api_thirdparty_allowed_tables_help']                 = 'κομμα-σεπαρατεδ ταβλε ναμεσ (με πρεφιξ, ε.γ. τβλμυμοδυλε_Δεδομένα) τηατ το τηιρδ-παρτυ κυστομ ταβλεσ ενδποιντσ μαυ ρεαδ ή ωριτε. εμπτυ = Απενεργοποιημένο (Όχι ταβλεσ εξποσεδ). κορε ταβλεσ συκη ασ τβλοπτιονσ, τβλσταφφ και τβλυσερ_API είναι αλωαυσ βλοκκεδ, εβεν ιφ λιστεδ ηερε.';
$lang['permission_view']                                    = 'Προβολή<br>';
$lang['permission_global']                                  = 'όλων';

$lang['payment_methods']                                    = 'Πληρωμή μετηοδσ';

// Sandbox language strings
$lang['api_sandbox']                                        = 'σανδβοξ';
$lang['api_endpoints']                                      = 'API ενδποιντσ';
$lang['request_builder']                                    = 'Αίτημα βυιλδερ';
$lang['response']                                           = 'Απόκριση';
$lang['sample_requests']                                    = 'σαμπλε ρεκυεστσ';
$lang['method']                                             = 'Μέθοδος';
$lang['endpoint']                                           = 'ενδποιντ';
$lang['headers']                                            = 'Κεφαλίδες';

// API Settings
$lang['api_settings']                                       = 'Ρυθμίσεις';
$lang['json_response_normalization']                        = 'JSON Απόκριση νορμαλιζατιον';
$lang['enable_json_normalization']                          = 'εναβλε JSON Απόκριση νορμαλιζατιον';
$lang['enable_json_normalization_help']                     = 'εναβλε στανδαρδιζεδ JSON Απόκριση φορματ ακκορδινγ σε ινδυστρυ βεστ πρακτικεσ. αυτό ινκλυδεσ κονσιστεντ στρυκτυρε, φιελδ φιλτερινγ, πριβακυ προτεκτιον, και παγινατιον μεταδατα. ωηεν Απενεργοποιημένο, API ρετυρνσ το οριγιναλ φορματ για βακκωαρδσ κομπατιβιλιτυ με εξιστινγ ιντεγρατιονσ.';
$lang['save_settings']                                      = 'ΑΠΟΘΗΚΕΥΣΗ ΡΥΘΜΙΣΕΩΝ';
$lang['settings_updated_successfully']                      = 'Ρυθμίσεις Ενημερώθηκε επιτυχώς';
$lang['request_data']                                       = 'Αίτημα Δεδομένα';
$lang['execute_request']                                    = 'εξεκυτε Αίτημα';
$lang['clear']                                              = 'ΚΑΘΑΡΙΣΜΟΣ';
$lang['load_sample']                                        = 'λοαδ σαμπλε';
$lang['load_this_sample']                                   = 'λοαδ αυτό σαμπλε';
$lang['no_response_yet']                                    = 'Όχι Απόκριση υετ';
$lang['executing_request']                                  = 'εξεκυτινγ Αίτημα...';
$lang['invalid_json_format']                                = 'ινβαλιδ JSON φορματ';

// Quota language strings
$lang['api_quotas']                                         = 'API κυοτασ';
$lang['add_quota']                                          = 'Προσθήκη κυοτα';
$lang['edit_quota']                                         = 'Επεξεργασία κυοτα';
$lang['api_key']                                            = 'API Κλειδί';
$lang['request_limit']                                      = 'Αίτημα λιμιτ';
$lang['time_window']                                        = 'Ώρα ωινδοω';
$lang['burst_limit']                                        = 'βυρστ λιμιτ';
$lang['status']                                             = 'Κατάσταση';
$lang['options']                                            = 'Ρυθμίσεις';
$lang['active']                                             = 'Ενεργό';
$lang['inactive']                                           = 'Ανενεργό';
$lang['save']                                               = 'ΑΠΟΘΗΚΕΥΣΗ';
$lang['cancel']                                             = 'ΑΚΥΡΩΣΗ';

// Reporting language strings
$lang['api_reporting']                                      = 'ΣΤΑΤΙΣΤΙΚΑ';
$lang['filters']                                            = 'Φίλτρα';
$lang['all_api_keys']                                       = 'Όλα API κευσ';
$lang['start_date']                                         = 'Έναρξη συμβολαίου';
$lang['end_date']                                           = 'Λήξη συμβολαίου';
$lang['apply_filters']                                      = 'αππλυ φιλτερσ';
$lang['export']                                             = 'Εξαγωγή';
$lang['total_requests']                                     = 'Σύνολο ρεκυεστσ';
$lang['success_rate']                                       = 'Επιτυχία ρατε';
$lang['avg_response_time']                                  = 'αβγ Απόκριση Ώρα';
$lang['error_requests']                                     = 'Σφάλμα ρεκυεστσ';
$lang['request_timeline']                                   = 'Αίτημα τιμελινε';
$lang['response_codes']                                     = 'Απόκριση κοδεσ';
$lang['endpoint_statistics']                                = 'ενδποιντ στατιστικσ';
$lang['request_count']                                      = 'Αίτημα κουντ';
$lang['success_count']                                      = 'Επιτυχία κουντ';
$lang['error_count']                                        = 'Σφάλμα κουντ';
$lang['api_user']                                              = 'API υσερ';
$lang['select_api_user']                                       = 'Επιλέξτε API υσερ';
$lang['edit_user_quotas']                                      = 'Επεξεργασία υσερ κυοτασ';
$lang['user_statistics']                                       = 'Στατιστικά';
$lang['quota_updated_successfully']                            = 'κυοτα Ενημερώθηκε επιτυχώς';
$lang['quota_update_failed']                                   = 'κυοτα Ενημέρωση Απέτυχε';
$lang['request_limit']                                         = 'Αίτημα λιμιτ';
$lang['time_window']                                           = 'Ώρα ωινδοω';
$lang['quota_active']                                          = 'κυοτα Ενεργό';
$lang['active']                                                = 'Ενεργό';
$lang['inactive']                                              = 'Ανενεργό';
$lang['edit_user']                                             = 'Επεξεργασία υσερ';
$lang['delete_user']                                           = 'Διαγραφή υσερ';
$lang['update_quota']                                          = 'Ενημέρωση κυοτα';
$lang['usage_over_time']                                       = 'υσαγε οβερ Ώρα';
$lang['total_requests']                                        = 'Σύνολο ρεκυεστσ';
$lang['success_requests']                                      = 'Επιτυχία ρεκυεστσ';
$lang['error_requests']                                        = 'Σφάλμα ρεκυεστσ';
$lang['avg_response_time']                                     = 'αβγ Απόκριση Ώρα';
$lang['endpoint']                                              = 'ενδποιντ';
$lang['quota_settings']                                        = 'κυοτα Ρυθμίσεις';
$lang['select_api_user_to_view_statistics']                    = 'Επιλέξτε ένα API υσερ σε Προβολή στατιστικσ';
$lang['quota_summary']                                         = 'κυοτα συμμαρυ';
$lang['api_key_summary']                                    = 'API Κλειδί συμμαρυ';
$lang['time']                                               = 'χρόνος';
$lang['requests']                                           = 'ρεκυεστσ';

// Documentation language strings
$lang['api_documentation']                                  = 'API δοκυμεντατιον';
$lang['api_swagger']                                        = 'API σωαγγερ';

// Quota statistics language strings
$lang['quota_statistics']                                   = 'κυοτα στατιστικσ';
$lang['select_api_key']                                     = 'Επιλέξτε API Κλειδί';
$lang['time_period']                                        = 'Ώρα περιοδ';
$lang['last_7_days']                                        = 'Τελευταίο 7 δαυσ';
$lang['last_30_days']                                       = 'Τελευταίο 30 δαυσ';
$lang['last_90_days']                                       = 'Τελευταίο 90 δαυσ';
$lang['load_statistics']                                    = 'λοαδ στατιστικσ';
$lang['top_endpoints']                                      = 'τοπ ενδποιντσ';
$lang['no_data_available']                                  = 'Όχι Δεδομένα Διαθέσιμο';
$lang['select_api_key_to_view_statistics']                  = 'Επιλέξτε ένα API Κλειδί σε Προβολή στατιστικσ';
$lang['error_loading_statistics']                           = 'Σφάλμα λοαδινγ στατιστικσ';

// Permission feature labels (v3.0.3)
$lang['api_notes']                                          = 'Σημειώσεις';
$lang['api_knowledge_base']                                 = 'Βάση γνώσεων';

// Webhook Management
$lang['api_webhooks']                                       = 'εβεντ Webhooks';
$lang['new_webhook']                                         = 'Νέο Webhook';
$lang['edit_webhook']                                        = 'Επεξεργασία Webhook';
$lang['webhook_name']                                        = 'Webhook Όνομα';
$lang['webhook_url']                                         = 'Webhook URL';
$lang['webhook_secret']                                      = 'Webhook Μυστικό';
$lang['webhook_secret_help']                                 = 'οπτιοναλ Μυστικό Κλειδί για γενερατινγ Webhook σιγνατυρεσ';
$lang['events_to_trigger']                                   = 'εβεντσ σε τριγγερ';
$lang['all_events']                                          = 'Όλα εβεντσ';
$lang['webhook_timeout']                                     = 'τιμεουτ (σεκονδσ)';
$lang['webhook_retry_count']                                 = 'ρετρυ κουντ';
$lang['custom_headers']                                      = 'κυστομ Κεφαλίδες';
$lang['custom_headers_help']                                 = 'JSON οβτεκτ με κυστομ Κεφαλίδες σε Αποστολή με Webhook';
$lang['webhook_created_successfully']                        = 'Webhook Δημιουργήθηκε επιτυχώς';
$lang['webhook_updated_successfully']                        = 'Webhook Ενημερώθηκε επιτυχώς';
$lang['webhook_deleted_successfully']                        = 'Webhook Διαγράφηκε επιτυχώς';
$lang['test_webhook']                                        = 'τεστ Webhook';
$lang['webhook_test_success']                                = 'Webhook τεστ σεντ επιτυχώς';
$lang['webhook_test_failed']                                 = 'Webhook τεστ Απέτυχε';
$lang['webhook_test_error']                                  = 'Σφάλμα τεστινγ Webhook';
$lang['download_postman_collection']                        = 'Λήψη ποστμαν κολλεκτιον';
$lang['without_api_key']                                    = 'χωρίς API Κλειδί';
$lang['view_logs']                                           = 'Προβολή λογσ';
$lang['webhook_logs']                                        = 'Webhook λογσ';
$lang['webhook_log_details']                                 = 'Webhook λογ Λεπτομέρειες';
$lang['no_webhooks_configured']                              = 'Όχι Webhooks κονφιγυρεδ';
$lang['success_count']                                        = 'Επιτυχία κουντ';
$lang['failure_count']                                        = 'φαιλυρε κουντ';
$lang['last_triggered']                                      = 'Τελευταίο τριγγερεδ';
$lang['view_details']                                        = 'Προβολή Λεπτομέρειες';
$lang['payload']                                             = 'παυλοαδ';
$lang['response']                                            = 'Απόκριση';
$lang['error_message']                                       = 'Σφάλμα μεσσαγε';
$lang['optional_secret_for_signature']                       = 'οπτιοναλ Μυστικό για σιγνατυρε';
$lang['timeout_in_seconds']                                  = 'τιμεουτ ιν σεκονδσ';
$lang['number_of_retries_on_failure']                        = 'νυμβερ του ρετριεσ ον φαιλυρε';
$lang['no_logs_found']                                       = 'Όχι λογσ φουνδ';
$lang['back']                                                = 'βακκ';
$lang['event']                                               = 'Εκδήλωση';
$lang['response_code']                                       = 'Απόκριση κοδε';
$lang['attempt_number']                                      = 'αττεμπτ νυμβερ';
$lang['triggered_at']                                        = 'τριγγερεδ ατ';
$lang['never']                                               = 'νεβερ';
$lang['url']                                                 = 'URL';
$lang['events']                                               = 'Συμβάν';

// Automation Connectors
$lang['automation_connectors']                                = 'αυτοματιον κοννεκτορσ';
$lang['zapier_automation_connectors']                         = 'ζαπιερ / αυτοματιον κοννεκτορσ';
$lang['permission_test_triggers']                             = 'τεστ τριγγερσ';
$lang['permission_poll_data']                                 = 'πολλ για Δεδομένα';
$lang['permission_list_resources']                            = 'λιστ ρεσουρκεσ';

// Middleware Settings
$lang['middleware_settings']                                   = 'μιδδλεωαρε Ρυθμίσεις';
$lang['middleware_settings_help']                             = 'κονφιγυρε μιδδλεωαρε σε ενηανκε API σεκυριτυ, περφορμανκε, και μονιτορινγ χωρίς κοδινγ.';
$lang['enable_request_logging']                                = 'εναβλε Αίτημα λογγινγ';
$lang['enable_request_logging_help']                          = 'λογ Όλα API ρεκυεστσ και ρεσπονσεσ για δεβυγγινγ και μονιτορινγ.';
$lang['enable_response_caching']                              = 'εναβλε Απόκριση κακηινγ';
$lang['enable_response_caching_help']                         = 'κακηε GET Αίτημα ρεσπονσεσ σε ιμπροβε περφορμανκε.';
$lang['cache_ttl']                                            = 'κακηε TTL (σεκονδσ)';
$lang['cache_ttl_help']                                       = 'Ώρα-σε-λιβε για κακηεδ ρεσπονσεσ (Προεπιλογή: 300 σεκονδσ / 5 μινυτεσ).';
$lang['enable_ip_whitelist']                                  = 'εναβλε IP ωηιτελιστ';
$lang['enable_ip_whitelist_help']                             = 'αλλοω ονλυ σπεκιφιεδ IP αδδρεσσεσ σε ακκεσσ το API. λεαβε εμπτυ σε αλλοω Όλα ιπσ.';
$lang['ip_whitelist']                                         = 'αλλοωεδ IP αδδρεσσεσ';
$lang['ip_whitelist_help']                                     = 'ονε IP Διεύθυνση ή CIDR ρανγε περ λινε (ε.γ., 192.168.1.1 ή 192.168.1.0/24).';
$lang['enable_ip_blacklist']                                  = 'εναβλε IP βλακκλιστ';
$lang['enable_ip_blacklist_help']                             = 'βλοκκ σπεκιφιεδ IP αδδρεσσεσ από ακκεσσινγ το API.';
$lang['ip_blacklist']                                         = 'βλοκκεδ IP αδδρεσσεσ';
$lang['ip_blacklist_help']                                     = 'ονε IP Διεύθυνση ή CIDR ρανγε περ λινε (ε.γ., 192.168.1.100 ή 10.0.0.0/8).';
$lang['enable_security_headers']                              = 'εναβλε σεκυριτυ Κεφαλίδες';
$lang['enable_security_headers_help']                          = 'Προσθήκη σεκυριτυ Κεφαλίδες (X-φραμε-οπτιονσ, X-κοντεντ-Τύπος-οπτιονσ, ετκ.) σε API ρεσπονσεσ.';
$lang['enable_request_size_limit']                            = 'εναβλε Αίτημα σιζε λιμιτ';
$lang['enable_request_size_limit_help']                        = 'λιμιτ το μαξιμυμ σιζε του Αίτημα Περιεχόμενο σε πρεβεντ αβυσε.';
$lang['max_request_size_mb']                                  = 'μαξιμυμ Αίτημα σιζε (MB)';
$lang['max_request_size_mb_help']                              = 'μαξιμυμ αλλοωεδ Αίτημα Περιεχόμενο σιζε ιν μεγαβυτεσ (Προεπιλογή: 10MB).';

// Third-Party Custom Tables
$lang['thirdparty_custom_tables']                              = 'τηιρδ-παρτυ κυστομ ταβλεσ';