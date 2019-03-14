<?php
/**
 * Class logger
 *
 * Simple class which is implements basic logging functions.
 * Mostly you will use log() and setLevel() methods.
 *
 * @example
 * $logger = new logger('', 'ERROR');
 *
 * $logger->log('DB query error', 'error');
 * $logger->log($mysqli->error, 'debug'); // will not be logging
 *
 * $logger->setLevel('debug');
 * $logger->log($mysqli->error, 'debug'); // will be logging
 * $logger->setLevel(); // reset the level
 *
 * @author idzhalalov@gmail.com
 */


class logger
{
    protected $logLevel;
    protected static $filePath;

    /**
     * Create a logger object
     *
     * @param string $filePath - a full path to a log file (default: /log.log)
     * @param string $level
     */
    public function __construct($filePath = '', $level = 'error')
    {
        if (!empty($filePath)) {
            self::$filePath = $filePath;
        }
        if (empty(self::$filePath)) {
            self::$filePath = 'log.log';
        }
    }

    protected function logPath()
    {
        return self::$filePath;
    }

    protected function defaultLevel()
    {
        return (defined('LOG_LEVEL')) ? LOG_LEVEL : 'error';
    }

    /**
     * Add log message
     *
     * @param        $message
     * @param string $level - default: "INFO"
     * @param bool   $backtrace - add backtrace (default: false)
     *
     * @return bool|int
     */
    public function log($message, $level="info", $backtrace = false)
    {
        if ($level == null) {
            $level = 'info';
        }

        if ( ! $this->loggerFilter($level)) {
            return false;
        }

        $dt = date('m/d/Y H:i:s', time()) . substr((string)microtime(), 1, 4);
        $message = strtoupper($level) . ' | ' . $dt . ' | ' . (string) $message;

        if ($backtrace) {
            $message .= PHP_EOL . print_r(debug_backtrace(), true);
        }
        $message .= PHP_EOL;

        return file_put_contents($this->logPath(), $message, FILE_APPEND | LOCK_EX);
    }

    /**
     * Log filter.
     *
     * Check whether a level could be logged or not
     *
     * @param $loggerLevel
     *
     * @return bool
     */
    public function loggerFilter($loggerLevel)
    {
        $logLevels = $this->logLevels();
        $loggerLevel = strtoupper($loggerLevel);

        $defaultLevel = $this->level();
        $defaultLevel = strtoupper($defaultLevel);
        $defaultLevelNum = $logLevels[$defaultLevel];

        if (isset($logLevels[$loggerLevel])) {
            $loggerLevelNum = $logLevels[$loggerLevel];
        } else {
            $loggerLevelNum = $logLevels[$defaultLevel];
        }

        return (bool) ($defaultLevelNum <= $loggerLevelNum);
    }

    /**
     * Available log levels
     *
     * @return array
     */
    public function logLevels()
    {
        return [
            'DEBUG' => 0,
            'INFO' => 1,
            'WARN' => 2,
            'ERROR' => 3
        ];
    }

    /**
     * Set log level for a current logger instance
     *
     * @param string $level
     */
    public function setLevel($level = null)
    {
        if ($level == null) {
            $level = $this->defaultLevel();
        }

        $level = strtoupper($level);
        $logLevels = $this->logLevels();
        if (isset($logLevels[$level])) {
            $this->logLevel = $level;
        }
    }

    /**
     * Current log level
     *
     * @return string
     */
    public function level()
    {
        if ($this->logLevel !== null) {
            $result = $this->logLevel;
        } else {
            $result = $this->defaultLevel();
        }

        return $result;
    }
}