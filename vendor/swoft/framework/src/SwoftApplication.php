<?php declare(strict_types=1);

namespace Swoft;

use Swoft;
use Swoft\Concern\SwoftTrait;
use Swoft\Contract\ApplicationInterface;
use Swoft\Contract\SwoftInterface;
use Swoft\Helper\SwoftHelper;
use Swoft\Processor\AnnotationProcessor;
use Swoft\Processor\ApplicationProcessor;
use Swoft\Processor\BeanProcessor;
use Swoft\Processor\ConfigProcessor;
use Swoft\Processor\ConsoleProcessor;
use Swoft\Processor\EnvProcessor;
use Swoft\Processor\EventProcessor;
use Swoft\Processor\Processor;
use Swoft\Processor\ProcessorInterface;
use Swoft\Stdlib\Helper\ComposerHelper;
use Swoft\Stdlib\Helper\FSHelper;
use Swoft\Stdlib\Helper\ObjectHelper;
use Swoft\Stdlib\Helper\Str;
use Swoft\Log\Helper\CLog;
use function define;
use function defined;
use function dirname;
use const IN_PHAR;
use Swoole\Runtime;

/**
 * Swoft application
 *
 * @since 2.0
 */
class SwoftApplication implements SwoftInterface, ApplicationInterface
{
    use SwoftTrait;

    /**
     * Base path
     *
     * @var string
     */
    protected $basePath = '';

    /**
     * Env file
     *
     * @var string
     */
    protected $envFile = '@base/.env';

    /**
     * Application path
     *
     * @var string
     */
    protected $appPath = '@base/app';

    /**
     * Default bean file
     *
     * @var string
     */
    protected $beanFile = '@app/bean.php';

    /**
     * Config path
     *
     * @var string
     */
    protected $configPath = '@base/config';

    /**
     * Runtime path
     *
     * @var string
     */
    protected $runtimePath = '@base/runtime';

    /**
     * @var string
     */
    protected $resourcePath = '@base/resource';

    /**
     * @var ApplicationProcessor
     */
    private $processor;

    /**
     * @var bool
     */
    private $enableCoroutine = false;

    /**
     * Can disable processor class before handle.
     * eg.
     * [
     *  Swoft\Processor\ConsoleProcessor::class => 1,
     * ]
     *
     * @var array
     */
    private $disabledProcessors = [];

    /**
     * Can disable AutoLoader class before handle.
     * eg.
     * [
     *  Swoft\Console\AutoLoader::class  => 1,
     * ]
     *
     * @var array
     */
    private $disabledAutoLoaders = [];

    /**
     * Scans containing these namespace prefixes will be excluded.
     *
     * @var array
     * eg.
     * [
     *  'PHPUnit\\',
     * ]
     */
    private $disabledPsr4Prefixes = [];

    /**
     * Get the application version
     * @return string
     */
    public static function getVersion(): string
    {
        return self::VERSION;
    }

    /**
     * Class constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        //=====================================
        // swoft app 应用准备阶段
        // 在这个阶段监测了运行环境
        // 初始化了日志管理器
        // 设置了运行目录
        // 定义和设置了Swoft启动流程需要的处理器
        //=====================================
        // 运行环境检查
        SwoftHelper::checkRuntime();

        // 将SwoftApplication存储为全局静态属性。
        Swoft::$app = $this;

        // 应用初始化前的一些操作
        // 这里是swoft开发者为扩展应用留下的钩子函数
        $this->beforeInit();

        // 初始化日志管理器
        $this->initCLogger();
    
        // 设置用户指定的$config属性
        if ($config) {
            ObjectHelper::init($this, $config);
        }
    
        // 开启swoole hook, 将swoole支持的阻塞IO为协程异步IO
        if ($this->enableCoroutine) {
            CLog::info('Swoole\Runtime::enableCoroutine');
            Runtime::enableCoroutine();
        }

        // 初始化应用准备工作
        $this->init();

        CLog::info('Project path is <info>%s</info>', $this->basePath);
    
        // 应用初始化后的一些操作
        // 这里是swoft开发者为扩展应用留下的钩子函数
        $this->afterInit();
    }

    protected function beforeInit(): void
    {
        // Check phar env
        if (!defined('IN_PHAR')) {
            define('IN_PHAR', false);
        }
    }

    protected function init(): void
    {
        // Init system path aliases
        // 初始化当前项目的目录位置
        // 设置当前项目目录位置别名
        $this->findBasePath();
        $this->setSystemAlias();
    
        // 获取初始化swoft需要的程序处理器
        $processors = $this->processors();
    
        // 初始化Swoft的基础处理器
        $this->processor = new ApplicationProcessor($this);
    
        // 将swoft需要的程序处理器注册到基础处理器
        $this->processor->addFirstProcessor(...$processors);
    }

    protected function afterInit(): void
    {
        // If run in phar package
        if (IN_PHAR) {
            $this->setRuntimePath(Str::rmPharPrefix($this->runtimePath));
        }

        // Do something ...
        // $this->disableProcessor(ConsoleProcessor::class, EnvProcessor::class);
    }

    private function findBasePath()
    {
        if ($this->basePath) {
            return;
        }

        // Get bash path from current class file.
        $filePath = ComposerHelper::getClassLoader()->findFile(static::class);
        $filePath = FSHelper::conv2abs($filePath, false);

        $this->basePath = dirname($filePath, 2);
    }

    /**
     * Run application
     */
    public function run(): void
    {
        if (!$this->beforeRun()) {
            return;
        }
    
        // 运行Swoft基础处理器
        $this->processor->handle();
    }

    /**
     * @param string ...$classes
     */
    public function disableAutoLoader(string ...$classes)
    {
        foreach ($classes as $class) {
            $this->disabledAutoLoaders[$class] = 1;
        }
    }

    /**
     * @param string ...$classes
     */
    public function disableProcessor(string ...$classes)
    {
        foreach ($classes as $class) {
            $this->disabledProcessors[$class] = 1;
        }
    }

    /**
     * Add first processors
     *
     * @param Processor[] $processors
     *
     * @return bool
     */
    public function addFirstProcessor(Processor ...$processors): bool
    {
        return $this->processor->addFirstProcessor(...$processors);
    }

    /**
     * Add last processors
     *
     * @param Processor[] $processors
     *
     * @return true
     */
    public function addLastProcessor(Processor ...$processors): bool
    {
        return $this->processor->addLastProcessor(...$processors);
    }

    /**
     * Add processors
     *
     * @param int         $index
     * @param Processor[] $processors
     *
     * @return true
     */
    public function addProcessor(int $index, Processor ...$processors): bool
    {
        return $this->processor->addProcessor($index, ... $processors);
    }

    /**
     * @return ProcessorInterface[]
     */
    protected function processors(): array
    {
        return [
            // Env环境变量处理器
            new EnvProcessor($this),
            // Config配置文件处理器
            new ConfigProcessor($this),
            // 注解收集处理器
            new AnnotationProcessor($this),
            // Bean容器处理器
            new BeanProcessor($this),
            // 事件处理器
            new EventProcessor($this),
            // Console处理器
            new ConsoleProcessor($this),
        ];
    }

    /**
     * @return array
     */
    public function getDisabledProcessors(): array
    {
        return $this->disabledProcessors;
    }

    /**
     * @return array
     */
    public function getDisabledAutoLoaders(): array
    {
        return $this->disabledAutoLoaders;
    }

    /**
     * @return array
     */
    public function getDisabledPsr4Prefixes(): array
    {
        return $this->disabledPsr4Prefixes;
    }

    /**
     * @param string $beanFile
     */
    public function setBeanFile(string $beanFile): void
    {
        $this->beanFile = $beanFile;
    }

    /**
     * @return string
     */
    public function getBeanFile(): string
    {
        return $this->beanFile;
    }

    /**
     * @param string $relativePath
     *
     * @return string
     */
    public function getPath(string $relativePath): string
    {
        return $this->basePath . '/' . $relativePath;
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @return string
     */
    public function getAppPath(): string
    {
        return $this->appPath;
    }

    /**
     * @return string
     */
    public function getRuntimePath(): string
    {
        return $this->runtimePath;
    }

    /**
     * @return string
     */
    public function getConfigPath(): string
    {
        return $this->configPath;
    }

    /**
     * @param string $envFile
     */
    public function setEnvFile(string $envFile): void
    {
        $this->envFile = $envFile;
    }

    /**
     * @param string $appPath
     */
    public function setAppPath(string $appPath): void
    {
        $this->appPath = $appPath;

        Swoft::setAlias('@app', $appPath);
    }

    /**
     * @param string $configPath
     */
    public function setConfigPath(string $configPath): void
    {
        $this->configPath = $configPath;

        Swoft::setAlias('@config', $configPath);
    }

    /**
     * @param string $basePath
     */
    public function setBasePath(string $basePath): void
    {
        $this->basePath = $basePath;

        Swoft::setAlias('@base', $basePath);
    }

    /**
     * @param string $runtimePath
     */
    public function setRuntimePath(string $runtimePath): void
    {
        $this->runtimePath = $runtimePath;

        Swoft::setAlias('@runtime', $runtimePath);
    }

    /**
     * Get console logger config
     *
     * @return array
     */
    public function getCLoggerConfig(): array
    {
        return [
            'name'    => 'swoft',
            'enable'  => true,
            'output'  => true,
            'levels'  => '',
            'logFile' => ''
        ];
    }

    /**
     * @return string
     */
    public function getResourcePath(): string
    {
        return $this->resourcePath;
    }

    /**
     * @param string $resourcePath
     */
    public function setResourcePath(string $resourcePath): void
    {
        $this->resourcePath = $resourcePath;
    }

    /**
     * Init console logger
     */
    private function initCLogger(): void
    {
        // Console logger config
        $config = $this->getCLoggerConfig();

        // Init console log
        CLog::init($config);
    }

    /**
     * Set base path
     */
    private function setSystemAlias(): void
    {
        $basePath     = $this->getBasePath();
        $appPath      = $this->getAppPath();
        $configPath   = $this->getConfigPath();
        $runtimePath  = $this->getRuntimePath();
        $resourcePath = $this->getResourcePath();

        Swoft::setAlias('@base', $basePath);
        Swoft::setAlias('@app', $appPath);
        Swoft::setAlias('@config', $configPath);
        Swoft::setAlias('@runtime', $runtimePath);
        Swoft::setAlias('@resource', $resourcePath);

        CLog::info('Set alias @base=%s', $basePath);
        CLog::info('Set alias @app=%s', $appPath);
        CLog::info('Set alias @config=%s', $configPath);
        CLog::info('Set alias @runtime=%s', $runtimePath);
    }

    /**
     * @return bool
     */
    public function isEnableCoroutine(): bool
    {
        return $this->enableCoroutine;
    }
}
