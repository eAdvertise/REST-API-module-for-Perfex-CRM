<?php

declare(strict_types=1);

$outputDirectory = dirname(__DIR__) . '/reference/images/commission';
if (!is_dir($outputDirectory)) {
    mkdir($outputDirectory, 0775, true);
}

function xml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

function textElement(string $text, int $x, int $y, string $class = ''): string
{
    $classAttribute = $class === '' ? '' : ' class="' . xml($class) . '"';
    return sprintf('<text x="%d" y="%d"%s>%s</text>', $x, $y, $classAttribute, xml($text));
}

function badge(string $method, int $x, int $y): string
{
    $class = strtolower($method);
    return sprintf(
        '<g><rect x="%d" y="%d" width="94" height="38" rx="3" class="badge %s"/>%s</g>',
        $x,
        $y,
        $class,
        textElement($method, $x + 15, $y + 25, 'badge-text')
    );
}

function frame(string $title, string $subtitle, string $content): string
{
    return '<?xml version="1.0" encoding="UTF-8"?>' . "\n" .
        '<svg xmlns="http://www.w3.org/2000/svg" width="1400" height="900" viewBox="0 0 1400 900" role="img" aria-labelledby="title description">' .
        '<title id="title">' . xml($title) . '</title>' .
        '<desc id="description">' . xml($subtitle) . '</desc>' .
        '<style>
            text { font-family: Inter, Arial, sans-serif; fill: #24364d; font-size: 16px; }
            .brand, .badge-text { fill: #fff; font-weight: 700; }
            .heading { fill: #10243e; font-size: 30px; font-weight: 700; }
            .subtitle { fill: #52637a; }
            .label { fill: #64748b; font-size: 12px; font-weight: 700; }
            .section-title { fill: #10243e; font-weight: 700; }
            .mono { font-family: "DejaVu Sans Mono", monospace; font-size: 15px; }
            .code { font-family: "DejaVu Sans Mono", monospace; fill: #cdd6f4; font-size: 15px; }
            .result-label { fill: #047857; font-size: 12px; font-weight: 700; }
            .result { font-family: "DejaVu Sans Mono", monospace; fill: #14532d; font-size: 15px; }
            .badge.get { fill: #0f766e; } .badge.post { fill: #2563eb; }
            .badge.put { fill: #b45309; } .badge.delete { fill: #be123c; }
        </style>' .
        '<rect width="1400" height="900" fill="#f4f7fb"/>' .
        '<rect width="1400" height="80" fill="#10243e"/>' .
        textElement('eAD CRM  /  REST API Documentation', 38, 50, 'brand') .
        textElement($title, 70, 145, 'heading') .
        textElement($subtitle, 70, 180, 'subtitle') .
        '<rect x="70" y="215" width="1260" height="635" fill="#fff" stroke="#dce3ec"/>' .
        $content . '</svg>' . "\n";
}

function saveSvg(string $path, string $svg): void
{
    file_put_contents($path, $svg);
}

$rows = [
    ['GET', '/api/commission', 'Discover endpoints'],
    ['GET', '/api/commission/commissions', 'List calculated commissions'],
    ['POST', '/api/commission/policies', 'Create policy'],
    ['POST', '/api/commission/applicable-staff', 'Create staff assignment'],
    ['POST', '/api/commission/receipts', 'Create receipt'],
    ['GET', '/api/commission/chart', 'Get chart data'],
    ['POST', '/api/commission/recalculate', 'Recalculate commissions'],
];
$catalog = textElement('METHOD', 105, 260, 'label') . textElement('ENDPOINT', 240, 260, 'label') . textElement('OPERATION', 865, 260, 'label');
$y = 286;
foreach ($rows as [$method, $endpoint, $operation]) {
    $catalog .= sprintf('<line x1="95" y1="%d" x2="1305" y2="%d" stroke="#e6ebf1"/>', $y + 55, $y + 55);
    $catalog .= badge($method, 105, $y + 7);
    $catalog .= textElement($endpoint, 240, $y + 33, 'mono');
    $catalog .= textElement($operation, 865, $y + 33);
    $y += 72;
}
saveSvg(
    $outputDirectory . '/endpoint-catalog.svg',
    frame('Sales Commission endpoints', 'API v3 - authenticated with the authtoken header', $catalog)
);

function endpointContent(string $method, string $endpoint, array $lines, string $result): string
{
    $content = badge($method, 105, 250) . textElement($endpoint, 225, 278, 'mono');
    $content .= textElement('Example request', 105, 350, 'section-title');
    $content .= '<rect x="105" y="375" width="1190" height="310" rx="3" fill="#1e1e2e"/>';
    $lineY = 412;
    foreach ($lines as $line) {
        $content .= textElement($line, 130, $lineY, 'code');
        $lineY += 33;
    }
    $content .= '<rect x="105" y="715" width="1190" height="110" rx="3" fill="#ecfdf5"/>';
    $content .= textElement('RESULT', 130, 750, 'result-label');
    $content .= textElement($result, 130, 790, 'result');
    return $content;
}

$policyLines = [
    'curl -X POST "https://yoursite.com/api/commission/policies" \\',
    '  -H "authtoken: YOUR_API_TOKEN" \\',
    '  -H "Content-Type: application/json" \\',
    "  -d '{",
    '    "name": "Standard sales commission",',
    '    "from_date": "2026-01-01",',
    '    "commission_policy_type": "percentage",',
    '    "percent_enjoyed": "10"',
    "  }'",
];
saveSvg(
    $outputDirectory . '/policy-endpoints.svg',
    frame(
        'Create policy',
        'Define how commissions are calculated from the selected start date.',
        endpointContent('POST', '/api/commission/policies', $policyLines, '{ "status": true, "message": "Commission policy created." }')
    )
);

$receiptLines = [
    'curl -X POST "https://yoursite.com/api/commission/receipts/42/email" \\',
    '  -H "authtoken: YOUR_API_TOKEN" \\',
    '  -H "Content-Type: application/json" \\',
    "  -d '{",
    '    "sent_to": ["sales@example.com"],',
    '    "message": "Your commission receipt"',
    "  }'",
];
saveSvg(
    $outputDirectory . '/receipt-reporting-endpoints.svg',
    frame(
        'Email commission receipt',
        'Send an existing receipt to one or more valid email addresses.',
        endpointContent('POST', '/api/commission/receipts/42/email', $receiptLines, '{ "status": true, "sent": 1 }')
    )
);

echo "Generated Sales Commission documentation screenshots as SVG.\n";
