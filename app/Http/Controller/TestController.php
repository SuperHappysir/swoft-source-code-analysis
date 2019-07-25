<?php declare(strict_types=1);


namespace App\Http\Controller;

use App\Common\HttpProvider;
use App\Model\Entity\TestAaaa;
use ReflectionException;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Bean\Exception\ContainerException;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Log\Helper\CLog;
use Swoft\Log\Helper\Log;

/**
 * Class LogController
 *
 * @since 2.0
 *
 * @Controller("test")
 */
class TestController
{
    /**
     * @Inject()
     *
     * @var HttpProvider
     */
    protected $httpProvider;
    
    /**
     * @RequestMapping("test")
     *
     * @return array
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Consul\Exception\ClientException
     * @throws \Swoft\Consul\Exception\ServerException
     */
    public function test(): array
    {
        Log::getLogger()->info('this info log', ['hello' => 'world']);
        // return array_merge($this->httpProvider->getList() , TestAaaa::get()->toArray());
        return TestAaaa::get()->toArray();
    }
}
