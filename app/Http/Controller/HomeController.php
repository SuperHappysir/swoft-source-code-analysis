<?php declare(strict_types=1);

namespace App\Http\Controller;

use App\Model\Entity\TestAaaa;
use Happysir\Lock\Contract\LockInterface;
use Happysir\Lock\RedisLock;
use ReflectionException;
use Swoft;
use Swoft\Bean\BeanFactory;
use Swoft\Bean\Exception\ContainerException;
use Swoft\Context\Context;
use Swoft\Http\Message\ContentType;
use Swoft\Http\Message\Response;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Server\Router\Router;
use Swoft\View\Renderer;
use Swoole\Coroutine;
use Throwable;

/**
 * Class HomeController
 * @Controller()
 */
class HomeController
{
    /**
     * @Swoft\Bean\Annotation\Mapping\Inject()
     *
     * @var LockInterface
     */
    protected $distributedLock;
    
    /**
     * @RequestMapping("/")
     * @throws Throwable
     */
    public function index(): Response
    {
    
        /** @var Router $router Register HTTP routes */
        $router = bean('httpRouter');
        var_dump($router->getRoutes());
        /** @var LockInterface $distributedLock */
        // $distributedLock = bean(LockInterface::class);
        
        
        
        
        // $distributedLock = bean(RedisLock::class);
        // $distributedLock = new RedisLock();
        
        // var_dump($distributedLock->lock('test', 2));
    
        // TestAaaa::where('id', 1)->update(['num' => TestAaaa::find(1)->getNum() + 1]);
        // if ($this->distributedLock->tryLock('test', 1)) {
        //     TestAaaa::where('id', 1)->update(['num' => TestAaaa::find(1)->getNum() + 1]);
        //     Coroutine::sleep(20);
        //     $this->distributedLock->unLock();
        // } else {
        //     Swoft\Redis\Redis::incr('testnum');
        // }
        

        
        echo time() . PHP_EOL;
        
        // var_dump($distributedLock->lock('test'));
        // echo time() . PHP_EOL;
        // var_dump($distributedLock->lock('test'));
        // echo time() . PHP_EOL;
        // Coroutine::sleep(11);
        
        /** @var Renderer $renderer */
        $renderer = Swoft::getBean('view');
        $content  = $renderer->render('home/index');

        return Context::mustGet()->getResponse()->withContentType(ContentType::HTML)->withContent($content);
    }

    /**
     * @RequestMapping("/hello[/{name}]")
     * @param string $name
     *
     * @return Response
     * @throws ReflectionException
     * @throws ContainerException
     */
    public function hello(string $name): Response
    {
        return Context::mustGet()->getResponse()->withContent('Hello' . ($name === '' ? '' : ", {$name}"));
    }
}
