<?php
// Read the original MySQL schema
$mysqlSchema = file_get_contents(__DIR__ . '/../database/schema.sql');

// Define conversion patterns
$conversions = [
    // AUTO_INCREMENT to SERIAL
    '/AUTO_INCREMENT/' => '',
    '/INT\s+NOT\s+NULL\s+PRIMARY\s+KEY/' => 'SERIAL PRIMARY KEY',
    
    // TIMESTAMP with default values
    '/TIMESTAMP\s+DEFAULT\s+CURRENT_TIMESTAMP/' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
    
    // VARCHAR to TEXT for large values
    '/VARCHAR\((\d+)\)/' => function($matches) {
        $size = (int)$matches[1];
        return $size > 1000 ? 'TEXT' : "VARCHAR($size)";
    },
    
    // LONGTEXT to TEXT
    '/LONGTEXT/' => 'TEXT',
    
    // ENGINE and charset settings removal
    '/ENGINE=InnoDB.*?;/' => ';',
    
    // JSON to JSONB
    '/JSON/' => 'JSONB'
];

// Apply conversions
$pgSchema = $mysqlSchema;
foreach ($conversions as $pattern => $replacement) {
    if (is_callable($replacement)) {
        $pgSchema = preg_replace_callback($pattern, $replacement, $pgSchema);
    } else {
        $pgSchema = preg_replace($pattern, $replacement, $pgSchema);
    }
}

// Output the PostgreSQL schema
file_put_contents(__DIR__ . '/../database/schema_pg.sql', $pgSchema);
echo "PostgreSQL schema created successfully!\n";