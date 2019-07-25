<?php

namespace Swoft\Processor;

use function alias;
use function file_exists;
use function get_class;
use InvalidArgumentException;
use ReflectionException;
use function sprintf;
use Swoft\Annotation\AnnotationRegister;
use Swoft\Annotation\Exception\AnnotationException;
use Swoft\Bean\BeanFactory;
use Swoft\Bean\Exception\ContainerException;
use Swoft\BeanHandler;
use Swoft\Config\Config;
use Swoft\Contract\ComponentInterface;
use Swoft\Contract\DefinitionInterface;
use Swoft\Log\Helper\CLog;
use Swoft\Helper\SwoftHelper;
use Swoft\Stdlib\Helper\ArrayHelper;

/**
 * Bean processor
 * @since 2.0
 */
class BeanProcessor extends Processor
{
    /**
     * Handle bean
     *
     * @return bool
     * @throws ContainerException
     * @throws ReflectionException
     * @throws AnnotationException
     */
    public function handle(): bool
    {
        if (!$this->application->beforeBean()) {
            return false;
        }

        $handler     = new BeanHandler();
        
        // 获取bean定义信息
        // 获取app中bean.php和各组件中Autoload中定义的所有依赖关系
        // bean.php中的会覆盖Autoload中的依赖信息，合并完成是并集信息
        $definitions = $this->getDefinitions();
    
        // 获取Annotation收集Process注册到AnnotationRegister中的所有注解解析器
        $parsers     = AnnotationRegister::getParsers();
    
        // 获取Annotation收集Process注册到AnnotationRegister中的所有注解
        $annotations = AnnotationRegister::getAnnotations();

        // 将注解、解析器、bean依赖关系定义信息添加到BeanFactory
        // 供BeanFactoryBeanFactory初始化bean及处理依赖关系
        BeanFactory::addDefinitions($definitions);
        BeanFactory::addAnnotations($annotations);
        BeanFactory::addParsers($parsers);
    
        // 设置BeanFactory所依赖的HandlerInterface
        BeanFactory::setHandler($handler);
    
        // BeanFactory初始化
        // 1. 解析注解
        // 2. 解析依赖
        // 3. 初始化bean
        BeanFactory::init();

        // 从Bean工厂中获取Config管理对象
        /* @var Config $config*/
        $config = BeanFactory::getBean('config');

        CLog::info('config path=%s', $config->getPath());
        CLog::info('config env=%s', $config->getEnv());

        // 获取BeanFactory初始化状态
        $stats = BeanFactory::getStats();

        CLog::info('Bean is initialized(%s)', SwoftHelper::formatStats($stats));

        return $this->application->afterBean();
    }

    /**
     * Get bean definitions
     *
     * @return array
     */
    private function getDefinitions(): array
    {
        // Core beans
        $definitions = [];
        
        // 获取所有组件的AutoLoader对象
        $autoLoaders = AnnotationRegister::getAutoLoaders();

        // 按应用程序获取禁止使用的AutoLoader类
        $disabledLoaders = $this->application->getDisabledAutoLoaders();

        // 遍历所有AutoLoader对象
        foreach ($autoLoaders as $autoLoader) {
            // 如果AutoLoader对象未实现DefinitionInterface接口跳过处理
            if (!$autoLoader instanceof DefinitionInterface) {
                continue;
            }

            // 获取AutoLoader对象的类名称
            $loaderClass = get_class($autoLoader);

            // If the component is disabled by user.
            // 如果组件被用户禁用, 跳过处理
            if (isset($disabledLoaders[$loaderClass])) {
                CLog::info('Auto loader(%s) is <cyan>disabled</cyan>, skip handle it', $loaderClass);
                continue;
            }

            // If the component is not enabled.
            // 如果未启用该组件, 跳过处理
            if ($autoLoader instanceof ComponentInterface && !$autoLoader->isEnable()) {
                continue;
            }

            // 合并收集bean定义
            $definitions = ArrayHelper::merge($definitions, $autoLoader->beans());
        }

        // Bean definitions
        // 获取用户配置的bean定义
        $beanFile = $this->application->getBeanFile();
        $beanFile = alias($beanFile);

        if (!file_exists($beanFile)) {
            throw new InvalidArgumentException(
                sprintf('The bean config file of %s is not exist!', $beanFile)
            );
        }

        $beanDefinitions = require $beanFile;
        
        // 合并bean定义，使用用户设置的bean定义覆盖组件中的bean定义
        $definitions     = ArrayHelper::merge($definitions, $beanDefinitions);

        return $definitions;
    }
}
