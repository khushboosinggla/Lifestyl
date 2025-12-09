<?php
/**
 * Database Initialization Script for Lifestyl E-commerce
 * 
 * This script creates the necessary database and tables for storing e-commerce data.
 * It is designed to be run once during application setup or when upgrading.
 */

// Check if script is being run from CLI or web
$isCLI = (php_sapi_name() === 'cli');

/**
 * Output message with appropriate formatting based on execution context
 */
function outputMessage($message, $isError = false) {
    global $isCLI;
    
    if ($isCLI) {
        echo ($isError ? "\033[31m" : "\033[32m") . $message . "\033[0m" . PHP_EOL;
    } else {
        echo '<div style="padding: 10px; margin: 5px; ' . 
             'background-color: ' . ($isError ? '#ffebee' : '#e8f5e9') . '; ' . 
             'color: ' . ($isError ? '#c62828' : '#2e7d32') . '; ' . 
             'border-left: 4px solid ' . ($isError ? '#c62828' : '#2e7d32') . ';">' . 
             htmlspecialchars($message) . '</div>';
    }
}

// Initialize database connection parameters
$host = 'localhost';
$username = 'root';    // Default XAMPP username
$password = '';        // Default XAMPP password (empty)
$dbname = 'lifestyl';  // Your database name

// Check if this is a web request and display a simple UI
if (!$isCLI) {
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Lifestyl Database Initialization</title>
        <style>
            body { font-family: system-ui, -apple-system, sans-serif; line-height: 1.6; max-width: 800px; margin: 0 auto; padding: 20px; }
            h1 { color: #2563eb; }
            .card { background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 6px; padding: 20px; margin-bottom: 20px; }
            .btn { display: inline-block; padding: 10px 16px; background-color: #2563eb; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; }
            .btn:hover { background-color: #1d4ed8; }
            pre { background-color: #f1f5f9; padding: 10px; border-radius: 4px; overflow-x: auto; }
        </style>
    </head>
    <body>
        <h1>Lifestyl E-commerce Database Initialization</h1>
        <div class="card">
            <h2>Database Configuration</h2>
            <p>Current database configuration:</p>
            <ul>
                <li>Host: ' . htmlspecialchars($host) . '</li>
                <li>Database Name: ' . htmlspecialchars($dbname) . '</li>
                <li>Username: ' . htmlspecialchars($username) . '</li>
            </ul>
            <p>If you need to change these settings, please update the configuration in this file.</p>
        </div>';
    
    // Check if the initialization form was submitted
    if (isset($_POST['initialize'])) {
        echo '<div class="card"><h2>Initialization Results</h2>';
        // Run initialization logic
        initializeDatabase();
        echo '</div>';
    } else {
        echo '<div class="card">
            <h2>Initialize Database</h2>
            <p>This will create the necessary database and tables required for Lifestyl E-commerce to function.</p>
            <p><strong>Warning:</strong> If you have existing data, it will be preserved unless there are conflicts.</p>
            <form method="post">
                <button type="submit" name="initialize" class="btn">Initialize Database</button>
            </form>
        </div>';
    }
    
    echo '</body></html>';
} else {
    // CLI mode
    echo "Lifestyl E-commerce Database Initialization\n";
    echo "========================================\n\n";
    
    // Run initialization logic
    initializeDatabase();
}

/**
 * Main function to initialize the database
 */
function initializeDatabase() {
    global $host, $username, $password, $dbname;
    
    try {
        // Step 1: Create database if it doesn't exist
        outputMessage("Step 1: Creating database if it doesn't exist...");
        createDatabase();
        
        // Step 2: Create tables using the SQL script
        outputMessage("Step 2: Creating tables...");
        executeSqlScript();
        
        outputMessage("Database initialization completed successfully!");
        outputMessage("You can now use the Lifestyl E-commerce application.");
        
    } catch (Exception $e) {
        outputMessage("Error: " . $e->getMessage(), true);
    }
}

/**
 * Create the database if it doesn't exist
 */
function createDatabase() {
    global $host, $username, $password, $dbname;
    
    try {
        // Connect without specifying a database
        $conn = new PDO("mysql:host=$host", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Create the database if it doesn't exist
        $conn->exec("CREATE DATABASE IF NOT EXISTS `$dbname` 
                    DEFAULT CHARACTER SET utf8mb4 
                    DEFAULT COLLATE utf8mb4_unicode_ci");
        
        outputMessage("Database '$dbname' created or already exists.");
        
    } catch(PDOException $e) {
        throw new Exception("Database creation failed: " . $e->getMessage());
    }
}

/**
 * Execute the SQL script to create tables
 */
function executeSqlScript() {
    global $host, $username, $password, $dbname;
    
    try {
        // Connect to the database
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Read the SQL script
        $sqlScript = file_get_contents('create_tables.sql');
        
        // Split the SQL script into individual statements
        $statements = explode(';', $sqlScript);
        
        // Execute each statement
        foreach($statements as $statement) {
            $statement = trim($statement);
            if (!empty($statement)) {
                $conn->exec($statement);
                // Extract table name from the statement for better feedback
                if (preg_match('/CREATE TABLE IF NOT EXISTS (\w+)/i', $statement, $matches)) {
                    outputMessage("Table '{$matches[1]}' created or already exists.");
                }
            }
        }
        
        outputMessage("All tables have been created successfully.");
        
    } catch(PDOException $e) {
        throw new Exception("Failed to execute SQL script: " . $e->getMessage());
    }
}
?>