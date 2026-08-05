<!DOCTYPE html>
<html>
<head>
    <title><?php echo $title; ?></title>
    <link rel="stylesheet" type="text/css" href="<?php echo base_url('/modules/api/assets/swagger/swagger-ui.css') ?>">
    <script src="<?php echo base_url('/modules/api/assets/swagger/swagger-ui-bundle.js') ?>"></script>
    <script src="<?php echo base_url('/modules/api/assets/swagger/swagger-ui-standalone-preset.js') ?>"></script>
</head>
<body>
    <div id="swagger-ui"></div>
    <style>
        [data-param-name="check_api"] {
            display: none;
        }
        .schemes-server-container {
            opacity: 0;
        }
        .swagger-ui > div > .wrapper:nth-child(5) {
            display: none;
        }
    </style>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "<?php echo site_url('api/openapi.json'); ?>",
                dom_id: '#swagger-ui',
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIBundle.SwaggerUIStandalonePreset
                ],
                plugins: [ ],
                requestInterceptor: (req) => {
                    req.headers['X-Playground'] = 'enabled';
                    return req;
                }
            });
        }
        function checkTryOutBtn() {
            if (document.querySelectorAll('.try-out__btn').length) {
                document.querySelectorAll('.try-out__btn').forEach(btyOutBtnEl => {
                    btyOutBtnEl.removeEventListener('click',  function() {});
                    btyOutBtnEl.addEventListener('click', function() {
                        setTimeout(function() {
                            document.querySelectorAll('input').forEach(decimalEl => {
                                if (decimalEl.closest('.parameters').querySelector('.prop-format') && decimalEl.closest('.parameters').querySelector('.prop-format').innerText === "($decimal)") {
                                    decimalEl.value = parseFloat(decimalEl.value).toFixed(2);
                                    decimalEl.setAttribute("value", parseFloat(decimalEl.value).toFixed(2));
                                    decimalEl.removeEventListener('change',  function() {});
                                    decimalEl.addEventListener('change', function() {
                                        decimalEl.value = parseFloat(decimalEl.value).toFixed(2);
                                        decimalEl.setAttribute("value", parseFloat(decimalEl.value).toFixed(2));
                                    });
                                }
                            });
                        }, 1000);
                    });
                });
            }
            setTimeout(function() {
                checkTryOutBtn();
            }, 1000);
        }
        checkTryOutBtn();
    </script>
</body>
</html>