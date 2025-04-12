<?php

require __DIR__ . '/../vendor/autoload.php';

$openapi = \OpenApi\Generator::scan([
    __DIR__ . '/../src/controllers',
    __DIR__ . '/../src/OpenAPI'
]);

// Create docs directory if it doesn't exist
$docsDir = __DIR__ . '/../public/docs';
if (!file_exists($docsDir)) {
    mkdir($docsDir, 0755, true);
}

// Save as YAML
$yamlFile = $docsDir . '/openapi.yaml';
file_put_contents($yamlFile, $openapi->save('yaml'));

// Save as JSON
$jsonFile = $docsDir . '/openapi.json';
file_put_contents($jsonFile, $openapi->save('json'));

echo "Documentation generated successfully!\n";