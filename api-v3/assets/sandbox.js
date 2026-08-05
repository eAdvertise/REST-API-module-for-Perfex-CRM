/**
 * Sandbox JavaScript functionality
 */

// Define initSandbox function first
function initSandbox() {
    // Method change handler
    $('#request-method').on('change', function() {
        var method = $(this).val();
        toggleDataGroup(method);
        updateCurlCommand();
    });
    
    // Endpoint link click handler
    $('.endpoint-link').on('click', function(e) {
        e.preventDefault();
        loadEndpoint($(this));
    });
    
    // Load sample button handler
    $('.load-sample-btn').on('click', function() {
        loadSample($(this));
    });
    
    // Clear form handler
    $('#clear-form').on('click', function() {
        clearForm();
    });
    
    // Form submit handler
    $('#api-request-form').on('submit', function(e) {
        e.preventDefault();
        executeRequest();
    });
    
    // Auto-resize textareas
    $('textarea').on('input', function() {
        autoResizeTextarea($(this));
        updateCurlCommand();
    });
    
    // Input change handlers for cURL generation
    $('#request-endpoint, #request-headers, #request-data').on('input', function() {
        updateCurlCommand();
    });
    
    // Environment switcher
    $('#environmentSwitch').on('change', function() {
        toggleEnvironment($(this).is(':checked'));
    });
    
    // Load endpoints dynamically
    loadEndpoints();
}

function toggleDataGroup(method) {
    if (method === 'GET') {
        $('#data-group').hide();
    } else {
        $('#data-group').show();
    }
}

function loadEndpoint(link) {
    var endpoint = link.data('endpoint');
    var parts = endpoint.split(' ');
    var method = parts[0];
    var path = parts[1];
    
    $('#request-method').val(method);
    $('#request-endpoint').val(path);
    
    toggleDataGroup(method);
    
    // Highlight the selected endpoint
    $('.endpoint-link').removeClass('active');
    link.addClass('active');
}

function loadSample(button) {
    var sample = button.data('sample');
    
    $('#request-method').val(sample.method);
    $('#request-endpoint').val(sample.endpoint);
    
    if (sample.sample_data) {
        $('#request-data').val(JSON.stringify(sample.sample_data, null, 2));
    } else {
        $('#request-data').val('');
    }
    
    if (sample.sample_headers) {
        $('#request-headers').val(JSON.stringify(sample.sample_headers, null, 2));
    }
    
    toggleDataGroup(sample.method);
    
    // Scroll to form
    $('html, body').animate({
        scrollTop: $('#api-request-form').offset().top - 100
    }, 500);
}

function clearForm() {
    $('#api-request-form')[0].reset();
    $('#data-group').hide();
    $('#response-content').html('<p class="text-muted">No response yet</p>');
    $('.endpoint-link').removeClass('active');
}

function executeRequest() {
    var formData = collectFormData();
    
    if (!validateFormData(formData)) {
        return;
    }
    
    showLoading();
    
    var config = window.sandboxConfig || { playgroundUrl: '' };
    $.ajax({
        url: config.playgroundUrl + 'execute_request',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            showResponse(response, true);
        },
        error: function(xhr) {
            showResponse(xhr.responseJSON || {error: xhr.responseText, status_code: xhr.status}, false);
        }
    });
}

function collectFormData() {
    return {
        method: $('#request-method').val(),
        endpoint: $('#request-endpoint').val(),
        headers: $('#request-headers').val(),
        data: $('#request-data').val()
    };
}

function validateFormData(formData) {
    // Validate JSON in headers and data
    try {
        if (formData.headers && formData.headers.trim()) {
            JSON.parse(formData.headers);
        }
        if (formData.data && formData.data.trim()) {
            JSON.parse(formData.data);
        }
    } catch (error) {
        showAlert('Invalid JSON format in headers or data', 'error');
        return false;
    }
    
    // Validate required fields
    if (!formData.endpoint) {
        showAlert('Endpoint is required', 'error');
        return false;
    }
    
    return true;
}

function showLoading() {
    var loadingHtml = '<div class="text-center">';
    loadingHtml += '<i class="fa fa-spinner fa-spin fa-2x"></i>';
    loadingHtml += '<p>Executing request...</p>';
    loadingHtml += '</div>';
    $('#response-content').html(loadingHtml);
}

function showResponse(response, isSuccess) {
    var html = '';
    
    if (isSuccess) {
        html += '<div class="response-success">';
        html += '<h6><span class="label label-success">' + (response.status_code || 200) + '</span> Success</h6>';
        html += '<pre><code>' + JSON.stringify(response.data, null, 2) + '</code></pre>';
        html += '</div>';
    } else {
        html += '<div class="response-error">';
        html += '<h6><span class="label label-danger">' + (response.status_code || 500) + '</span> Error</h6>';
        html += '<pre><code>' + (response.error || 'Unknown error occurred') + '</code></pre>';
        html += '</div>';
    }
    
    $('#response-content').html(html);
    
    // Scroll to response
    $('html, body').animate({
        scrollTop: $('#response-content').offset().top - 100
    }, 500);
}

function showAlert(message, type) {
    var alertClass = type === 'error' ? 'alert-danger' : 'alert-info';
    var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible fade in" role="alert">';
    alertHtml += '<button type="button" class="close" data-dismiss="alert" aria-label="Close">';
    alertHtml += '<span aria-hidden="true">&times;</span>';
    alertHtml += '</button>';
    alertHtml += message;
    alertHtml += '</div>';
    
    // Remove existing alerts
    $('.alert').remove();
    
    // Add new alert
    $('#api-request-form').before(alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
}

function autoResizeTextarea(textarea) {
    textarea.css('height', 'auto');
    textarea.css('height', textarea[0].scrollHeight + 'px');
}

// Utility function to format JSON
function formatJSON(jsonString) {
    try {
        var obj = JSON.parse(jsonString);
        return JSON.stringify(obj, null, 2);
    } catch (e) {
        return jsonString;
    }
}

// Add syntax highlighting for JSON
function highlightJSON(element) {
    var jsonString = element.text();
    var formatted = formatJSON(jsonString);
    element.text(formatted);
}

// Copy to clipboard functionality
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            showAlert('Copied to clipboard!', 'success');
        });
    } else {
        // Fallback for older browsers
        var textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showAlert('Copied to clipboard!', 'success');
    }
}

// Add copy button to response
function addCopyButton() {
    var responseContent = $('#response-content');
    if (responseContent.find('.copy-btn').length === 0) {
        var copyBtn = '<button class="btn btn-sm btn-outline-secondary copy-btn" style="position: absolute; top: 10px; right: 10px;">';
        copyBtn += '<i class="fa fa-copy"></i> Copy';
        copyBtn += '</button>';
        responseContent.css('position', 'relative').append(copyBtn);
        
        $('.copy-btn').on('click', function() {
            var responseText = responseContent.find('pre code').text();
            copyToClipboard(responseText);
        });
    }
}

// Initialize copy functionality when response is shown
$(document).on('DOMNodeInserted', '#response-content', function() {
    setTimeout(addCopyButton, 100);
});

// Keyboard shortcuts
$(document).on('keydown', function(e) {
    // Ctrl+Enter to execute request
    if (e.ctrlKey && e.which === 13) {
        e.preventDefault();
        executeRequest();
    }
    
    // Escape to clear form
    if (e.which === 27) {
        clearForm();
    }
});

// Add tooltips
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
});

// Export functionality
function exportRequest() {
    var formData = collectFormData();
    var exportData = {
        method: formData.method,
        endpoint: formData.endpoint,
        headers: formData.headers ? JSON.parse(formData.headers) : {},
        data: formData.data ? JSON.parse(formData.data) : null,
        timestamp: new Date().toISOString()
    };
    
    var dataStr = JSON.stringify(exportData, null, 2);
    var dataBlob = new Blob([dataStr], {type: 'application/json'});
    var url = URL.createObjectURL(dataBlob);
    var link = document.createElement('a');
    link.href = url;
    link.download = 'api-request-' + Date.now() + '.json';
    link.click();
    URL.revokeObjectURL(url);
}

// Import functionality
function importRequest(file) {
    var reader = new FileReader();
    reader.onload = function(e) {
        try {
            var data = JSON.parse(e.target.result);
            $('#request-method').val(data.method);
            $('#request-endpoint').val(data.endpoint);
            $('#request-headers').val(JSON.stringify(data.headers, null, 2));
            if (data.data) {
                $('#request-data').val(JSON.stringify(data.data, null, 2));
            }
            toggleDataGroup(data.method);
            updateCurlCommand();
        } catch (error) {
            showAlert('Invalid JSON file', 'error');
        }
    };
    reader.readAsText(file);
}

// Load endpoints dynamically
function loadEndpoints() {
    var config = window.sandboxConfig || { adminUrl: '' };
    $.get(config.adminUrl + 'api/playground/get_endpoints')
        .done(function(endpoints) {
            window.apiEndpoints = endpoints;
            populateEndpointsList(endpoints);
        })
        .fail(function() {
            console.error('Failed to load endpoints');
        });
}

// Populate endpoints list
function populateEndpointsList(endpoints) {
    var container = $('.endpoint-category:eq(1) .category-body');
    var html = '';
    
    for (var category in endpoints) {
        var categoryData = endpoints[category];
        html += '<h6 class="text-muted mb-2">' + categoryData.name + '</h6>';
        html += '<p class="text-muted small mb-2">' + categoryData.description + '</p>';
        
        categoryData.endpoints.forEach(function(endpoint) {
            html += '<div class="endpoint-item endpoint-link" data-endpoint="' + endpoint.method + ' ' + endpoint.path + '">';
            html += '<span class="method-badge method-' + endpoint.method.toLowerCase() + '">' + endpoint.method + '</span>';
            html += '<code>' + endpoint.path + '</code>';
            html += '<small class="d-block text-muted">' + endpoint.description + '</small>';
            html += '</div>';
        });
        
        html += '<hr class="my-3">';
    }
    
    container.html(html);
    
    // Re-bind click handlers
    $('.endpoint-link').on('click', function(e) {
        e.preventDefault();
        loadEndpoint($(this));
    });
}

// Toggle environment mode
function toggleEnvironment(isProduction) {
    var environmentSwitcher = $('.environment-switcher');
    var badge = environmentSwitcher.find('.badge');
    var description = environmentSwitcher.find('small');
    
    if (isProduction) {
        badge.removeClass('bg-success').addClass('bg-danger').text('Production');
        description.text('WARNING: This will affect live production data!');
        environmentSwitcher.removeClass('bg-warning').addClass('bg-danger text-white');
        
        // Show confirmation
        if (!confirm('Are you sure you want to switch to Production mode? This will affect live data!')) {
            $('#environmentSwitch').prop('checked', false);
            return;
        }
    } else {
        badge.removeClass('bg-danger').addClass('bg-success').text('Sandbox');
        description.text('All requests are made to test environment - no production data affected');
        environmentSwitcher.removeClass('bg-danger text-white').addClass('bg-warning');
    }
}

// Update cURL command
function updateCurlCommand() {
    var method = $('#request-method').val();
    var endpoint = $('#request-endpoint').val();
    var headers = $('#request-headers').val();
    var data = $('#request-data').val();
    var config = window.sandboxConfig || { adminUrl: '' };
    
    if (!endpoint) {
        $('#curl-command').val('');
        return;
    }
    
    var curlCommand = 'curl -X ' + method + ' "' + config.adminUrl + 'api/' + endpoint.replace(/^\//, '') + '"';
    
    // Add headers
    if (headers && headers.trim()) {
        var headerLines = headers.split('\n');
        headerLines.forEach(function(line) {
            line = line.trim();
            if (line && line.includes(':')) {
                curlCommand += ' \\\n  -H "' + line + '"';
            }
        });
    }
    
    // Add data
    if (data && data.trim() && ['POST', 'PUT', 'PATCH'].includes(method)) {
        curlCommand += ' \\\n  -d \'' + data + '\'';
    }
    
    $('#curl-command').val(curlCommand);
}

// Enhanced load sample function
function loadSample(button) {
    var sampleKey = button.data('sample');
    
    if (window.sampleRequests && window.sampleRequests[sampleKey]) {
        var sample = window.sampleRequests[sampleKey];
        
        $('#request-method').val(sample.method);
        $('#request-endpoint').val(sample.endpoint);
        $('#request-headers').val(sample.headers);
        
        if (sample.data) {
            $('#request-data').val(sample.data);
        } else {
            $('#request-data').val('');
        }
        
        toggleDataGroup(sample.method);
        updateCurlCommand();
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $('#api-request-form').offset().top - 100
        }, 500);
        
        // Show success message
        showAlert('Sample loaded successfully!', 'success');
    } else {
        showAlert('Sample not found. Please try again.', 'error');
    }
}

// Enhanced load endpoint function
function loadEndpoint(link) {
    var endpoint = link.data('endpoint');
    var parts = endpoint.split(' ');
    var method = parts[0];
    var path = parts[1];
    
    $('#request-method').val(method);
    $('#request-endpoint').val(path);
    
    toggleDataGroup(method);
    updateCurlCommand();
    
    // Highlight the selected endpoint
    $('.endpoint-link').removeClass('active');
    link.addClass('active');
    
    // Scroll to form
    $('html, body').animate({
        scrollTop: $('#api-request-form').offset().top - 100
    }, 500);
}

// Enhanced clear form function
function clearForm() {
    $('#api-request-form')[0].reset();
    $('#data-group').hide();
    $('#response-content').html('<div class="text-center text-muted py-5"><i class="fas fa-arrow-up fa-2x mb-3"></i><p>Execute a request to see the response here</p></div>');
    $('.endpoint-link').removeClass('active');
    $('#curl-command').val('');
    updateCurlCommand();
}

// Functions are now initialized from sandbox.php
