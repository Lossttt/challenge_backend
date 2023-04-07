<?php

// Connect to SQLite database
$pdo = new PDO('sqlite:parcels.db');

// Create table if it doesn't exist
$pdo->exec('CREATE TABLE IF NOT EXISTS parcels (
    tracking_number TEXT PRIMARY KEY NOT NULL UNIQUE,
    name TEXT NOT NULL,
    delivery_date TEXT NOT NULL,
    delivery_address_street TEXT NOT NULL,
    delivery_address_house_number INTEGER NOT NULL,
    delivery_address_house_number_extension TEXT,
    delivery_address_city TEXT NOT NULL,
    delivery_address_country TEXT NOT NULL,
    delivery_address_postcode TEXT NOT NULL,
    weight INTEGER NOT NULL,
    dimension_height INTEGER NOT NULL,
    dimension_length INTEGER NOT NULL,
    dimension_width INTEGER NOT NULL
)');

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        if ($_SERVER['REQUEST_URI'] === '/parcels') {

            // Get request body
            $body = json_decode(file_get_contents('php://input'), true);

            // Validate request body
            if (
                !isset($body['name'], $body['deliveryDate'], $body['deliveryAddress'], $body['weight'], $body['dimension'])
                || !is_string($body['name']) || !is_string($body['deliveryDate'])
                || !is_array($body['deliveryAddress']) || !is_int($body['weight']) || !is_array($body['dimension'])
                || !is_string($body['deliveryAddress']['street']) || !is_int($body['deliveryAddress']['houseNumber'])
                || !is_string($body['deliveryAddress']['city']) || !is_string($body['deliveryAddress']['country'])
                || !is_string($body['deliveryAddress']['postcode'])
                || !is_int($body['dimension']['height']) || !is_int($body['dimension']['length']) || !is_int($body['dimension']['width'])
            ) {
                http_response_code(400);
                exit;
            }

            // Generate tracking number
            $trackingNumber = uniqid();


            // Insert parcel to database
            $stmt = $pdo->prepare('INSERT INTO parcels (
                tracking_number, name, delivery_date,
                delivery_address_street, delivery_address_house_number, delivery_address_house_number_extension,
                delivery_address_city, delivery_address_country, delivery_address_postcode,
                weight, dimension_height, dimension_length, dimension_width
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');

            $stmt->bindValue(1, $trackingNumber, SQLITE3_TEXT);
            $stmt->bindValue(2, $body['name'], SQLITE3_TEXT);
            $stmt->bindValue(3, $body['deliveryDate'], SQLITE3_TEXT);
            $stmt->bindValue(4, $body['deliveryAddress']['street'] . ' ' . $body['deliveryAddress']['houseNumber'] . ' ' . $body['deliveryAddress']['houseNumberExtension'], SQLITE3_TEXT);
            $stmt->bindValue(5, $body['deliveryAddress']['postcode'], SQLITE3_TEXT);
            $stmt->bindValue(6, $body['deliveryAddress']['city'], SQLITE3_TEXT);
            $stmt->bindValue(7, $body['deliveryAddress']['country'], SQLITE3_TEXT);
            $stmt->bindValue(8, $body['weight'], SQLITE3_INTEGER);
            $stmt->bindValue(9, $body['dimension']['height'], SQLITE3_INTEGER);
            $stmt->bindValue(10, $body['dimension']['length'], SQLITE3_INTEGER);
            $stmt->bindValue(11, $body['dimension']['width'], SQLITE3_INTEGER);

            $stmt->execute([    
            $trackingNumber,     
            $body['name'], 
            $body['deliveryDate'],
            $body['deliveryAddress']['street'], $body['deliveryAddress']['houseNumber'], $body['deliveryAddress']['houseNumberExtension'],
            $body['deliveryAddress']['city'], $body['deliveryAddress']['country'], $body['deliveryAddress']['postcode'],
            $body['weight'], 
            $body['dimension']['height'], $body['dimension']['length'], $body['dimension']['width']
        ]);

            // Return tracking number in response
            $response = [
                'trackingNumber' => $trackingNumber
            ];

            // Return response
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
}
?>
