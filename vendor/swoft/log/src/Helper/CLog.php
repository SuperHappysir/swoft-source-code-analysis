<?php declare(strict_types=1);


namespace Swoft\Log\Helper;

use Monolog\Formatter\LineFormatter;
use function sprintf;
use Swoft\Log\CLogger;
use Swoft\Log\Handler\CEchoHandler;
use Swoft\Log\Handler\CFileHandler;

/**
 * Class CLog
 *
 * @since 2.0
 */
class CLog
{
    /**
     * @var CLogger
     */
    private static $cLogger;

    /**
     * Init console logger
     *
     * @param array $config
     */
    public static function init(array $config): void
    {
        if (self::$cLogger !== null) {
            return;
        }

        $name    = $config['name'] ?? '';
        $enable  = $config['enable'] ?? true;
        $output  = $config['output'] ?? true;
        $levels  = $config['levels'] ?? '';
        $logFile = $config['logFile'] ?? '';

        $lineFormatter = new LineFormatter();
    
        // 自定义\Monolog\Logger terminal输出 Handler
        $cEchoHandler = new CEchoHandler();
        $cEchoHandler->setFormatter($lineFormatter);
        $cEchoHandler->setLevels($levels);
        $cEchoHandler->setOutput($output);
    
        // 自定义\Monolog\Logger 文件存储 Handler
        $cFileHandler = new CFileHandler();
        $cFileHandler->setFormatter($lineFormatter);
        $cFileHandler->setLevels($levels);
        $cFileHandler->setLogFile($logFile);
    
        // 初始化Clogger日志管理器(扩展自 \Monolog\Logger)
        $cLogger = new CLogger();
        $cLogger->setName($name);
        $cLogger->setEnable($enable);
        $cLogger->setHandlers([$cEchoHandler, $cFileHandler]);

        self::$cLogger = $cLogger;
    }

    /**
     * Debug message
     *
     * @param string $message
     * @param array  $params
     */
    public static function debug(string $message, ...$params): void
    {
        if (!empty($params)) {
            $message = sprintf($message, ...$params);
        }

        if (SWOFT_DEBUG) {
            self::$cLogger->debug($message, []);
        }
    }

    /**
     * Info message
     *
     * @param string $message
     * @param array  $params
     */
    public static function info(string $message, ...$params): void
    {
        if (!empty($params)) {
            $message = sprintf($message, ...$params);
        }

        self::$cLogger->info($message, []);
    }

    /**
     * Warning message
     *
     * @param string $message
     * @param array  $params
     */
    public static function warning(string $message, ...$params): void
    {
        if (!empty($params)) {
            $message = sprintf($message, ...$params);
        }

        self::$cLogger->warning($message, []);
    }

    /**
     * Error message
     *
     * @param string $message
     * @param array  $params
     */
    public static function error(string $message, ...$params): void
    {
        if (!empty($params)) {
            $message = sprintf($message, ...$params);
        }

        self::$cLogger->error($message, []);
    }
}
