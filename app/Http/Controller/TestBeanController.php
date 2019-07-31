<?php

namespace App\Http\Controller;

use App\Model\Logic\TestInterface;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Http\Message\Response;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;

/**
 * Class TestBeanController
 * @Controller("test-bean")
 *
 * @package App\Http\Controller
 */
class TestBeanController
{
    /**
     * @Inject()
     * @var \App\Model\Logic\TestInterface
     */
    protected $testLogic;
    
    /**
     * @RequestMapping()
     * @return \Swoft\Http\Message\Response
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     */
    public function index() : Response
    {
        # 成功获取bean
        $this->testLogic->test();
        
        # 获取不到bean
        /** @var TestInterface $testLogic */
        $testLogic = bean(TestInterface::class);
        $testLogic->test();
    }
}
