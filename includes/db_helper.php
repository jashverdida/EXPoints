<?php
/**
 * Database Helper - Provides Supabase database service with MySQL compatibility
 * 
 * This helper provides a MySQL-like interface for database operations
 * using Supabase PostgreSQL backend.
 * 
 * Usage: $db = getDBConnection();
 */

// Load Supabase MySQL compatibility layer
require_once __DIR__ . '/../config/supabase-compat.php';

function getDBConnection() {
    // STRICT MODE: Supabase only, no fallback
    try {
        return new SupabaseMySQLCompat();
    } catch (Exception $e) {
        error_log("Supabase connection error: " . $e->getMessage());
        
        // No fallback - throw exception to force Supabase usage
        throw new Exception("Database unavailable. Please check your Supabase connection and internet connectivity.");
    }
}

/**
 * Get direct Supabase service (for new code)
 */
function getSupabaseService() {
    try {
        require_once __DIR__ . '/../config/supabase.php';
        return new SupabaseService();
    } catch (Exception $e) {
        error_log("Supabase service error: " . $e->getMessage());
        throw new Exception("Supabase service unavailable. Please check your configuration.");
    }
}
