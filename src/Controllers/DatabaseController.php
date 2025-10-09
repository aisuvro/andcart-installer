<?php

namespace Aisuvro\AndcartInstaller\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Aisuvro\AndcartInstaller\Helpers\DatabaseBackupManager;
use Aisuvro\AndcartInstaller\Helpers\LogManager;
use Aisuvro\AndcartInstaller\Helpers\ProgressTracker;
use Aisuvro\AndcartInstaller\Helpers\PerformanceMonitor;
use Aisuvro\AndcartInstaller\Helpers\DatabaseOptimizer;

class DatabaseController extends Controller
{
    public function migrate(Request $request)
    {
        $backupId = null;
        PerformanceMonitor::startTimer('database_migration');
        
        try {
            // Optimize for large databases
            DatabaseOptimizer::optimizeMemoryUsage();
            DatabaseOptimizer::optimizeForLargeDatabase();
            
            // Create backup before migration
            PerformanceMonitor::startTimer('backup_creation');
            LogManager::logOperation('migration_backup_started');
            $backupId = DatabaseBackupManager::createBackup();
            Cache::put('installer_backup_id', $backupId, 3600);
            PerformanceMonitor::endTimer('backup_creation');
            
            LogManager::logOperation('sql_import_started', ['backup_id' => $backupId]);
            ProgressTracker::setStep('sql_import', 'in_progress');
            
            // Import SQL file instead of running migrations
            PerformanceMonitor::startTimer('sql_import_execution');
            $this->importSqlFile();
            PerformanceMonitor::endTimer('sql_import_execution');
            
            $migrationMetrics = PerformanceMonitor::endTimer('database_migration');
            LogManager::logOperation('sql_import_completed', [
                'backup_id' => $backupId,
                'performance' => $migrationMetrics
            ]);
            ProgressTracker::setStep('sql_import', 'completed');
            
            return response()->json([
                'success' => true,
                'message' => 'Database import from SQL file completed successfully',
                'backup_id' => $backupId,
                'performance' => $migrationMetrics
            ]);
            
        } catch (Exception $e) {
            LogManager::logError('SQL import failed', $e, ['backup_id' => $backupId]);
            
            // Attempt rollback if backup exists
            if ($backupId) {
                try {
                    $this->rollback($backupId);
                    return response()->json([
                        'success' => false,
                        'message' => 'SQL import failed. Database restored from backup.',
                        'error' => $e->getMessage()
                    ], 500);
                } catch (Exception $rollbackException) {
                    LogManager::logError('Rollback failed', $rollbackException, ['backup_id' => $backupId]);
                }
            }
            
            return response()->json([
                'success' => false,
                'message' => 'SQL import failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function rollback($backupId = null)
    {
        try {
            $backupId = $backupId ?: Cache::get('installer_backup_id');
            
            if (!$backupId) {
                throw new Exception('No backup ID found');
            }
            
            LogManager::logOperation('rollback_started', ['backup_id' => $backupId]);
            
            DatabaseBackupManager::restoreBackup($backupId);
            
            LogManager::logOperation('rollback_completed', ['backup_id' => $backupId]);
            
            return response()->json([
                'success' => true,
                'message' => 'Database restored successfully'
            ]);
            
        } catch (Exception $e) {
            LogManager::logError('Rollback failed', $e, ['backup_id' => $backupId]);
            
            return response()->json([
                'success' => false,
                'message' => 'Rollback failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function checkBackup()
    {
        $backupId = Cache::get('installer_backup_id');
        
        return response()->json([
            'has_backup' => !is_null($backupId),
            'backup_id' => $backupId
        ]);
    }

    /**
     * Import database from SQL file instead of running migrations
     * 
     * @throws Exception
     */
    private function importSqlFile()
    {
        $sqlFilePath = base_path('database/andcart.sql');
        
        if (!File::exists($sqlFilePath)) {
            throw new Exception("SQL file not found at: {$sqlFilePath}");
        }
        
        LogManager::logOperation('sql_import_started', ['file' => $sqlFilePath]);
        
        try {
            // Read the SQL file content
            $sql = File::get($sqlFilePath);
            
            // Remove comments and empty lines for cleaner processing
            $sql = $this->cleanSqlContent($sql);
            
            // Split SQL into individual statements
            $statements = $this->splitSqlStatements($sql);
            
            // Disable foreign key checks to avoid constraint issues during import
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Execute each statement
            foreach ($statements as $index => $statement) {
                $statement = trim($statement);
                if (!empty($statement)) {
                    try {
                        DB::statement($statement);
                        LogManager::logOperation('sql_statement_executed', [
                            'statement_index' => $index + 1,
                            'statement_preview' => substr($statement, 0, 100) . '...'
                        ]);
                    } catch (Exception $e) {
                        LogManager::logError("SQL statement failed at index {$index}", $e, [
                            'statement' => $statement
                        ]);
                        throw new Exception("SQL import failed at statement " . ($index + 1) . ": " . $e->getMessage());
                    }
                }
            }
            
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            LogManager::logOperation('sql_import_completed', [
                'total_statements' => count($statements),
                'file_size' => File::size($sqlFilePath)
            ]);
            
        } catch (Exception $e) {
            // Re-enable foreign key checks in case of error
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            throw $e;
        }
    }

    /**
     * Clean SQL content by removing comments and unnecessary whitespace
     * 
     * @param string $sql
     * @return string
     */
    private function cleanSqlContent($sql)
    {
        // Remove SQL comments (-- and /* */)
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        
        // Remove MySQL specific commands that might cause issues
        $sql = preg_replace('/^\s*SET\s+.*?;/mi', '', $sql);
        $sql = preg_replace('/^\s*START TRANSACTION\s*;/mi', '', $sql);
        $sql = preg_replace('/^\s*COMMIT\s*;/mi', '', $sql);
        $sql = preg_replace('/^\s*\/\*!.*?\*\/\s*;?/mi', '', $sql);
        
        return $sql;
    }

    /**
     * Split SQL content into individual statements
     * 
     * @param string $sql
     * @return array
     */
    private function splitSqlStatements($sql)
    {
        // Split by semicolon, but be careful about semicolons within strings
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';
        $length = strlen($sql);
        
        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];
            
            // Handle string delimiters
            if (($char === '"' || $char === "'") && !$inString) {
                $inString = true;
                $stringChar = $char;
            } elseif ($char === $stringChar && $inString) {
                // Check if it's escaped
                if ($i > 0 && $sql[$i - 1] !== '\\') {
                    $inString = false;
                    $stringChar = '';
                }
            }
            
            $current .= $char;
            
            // If we hit a semicolon outside of a string, we have a complete statement
            if ($char === ';' && !$inString) {
                $statement = trim($current);
                if (!empty($statement)) {
                    $statements[] = $statement;
                }
                $current = '';
            }
        }
        
        // Add any remaining content as the last statement
        $statement = trim($current);
        if (!empty($statement)) {
            $statements[] = $statement;
        }
        
        return array_filter($statements, function($stmt) {
            return !empty(trim($stmt));
        });
    }
}