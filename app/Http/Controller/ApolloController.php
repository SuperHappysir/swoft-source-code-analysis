<?php declare(strict_types=1);


namespace App\Http\Controller;

use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;

/**
 * Class ApolloController
 *
 * @since 2.0
 *
 * @Controller(prefix="apollo")
 */
class ApolloController
{
    /**
     * @Inject()
     *
     * @var \App\Model\Logic\ApolloLogic
     */
    protected $apolloLogic;
    
    /**
     * @RequestMapping()
     *
     * @return array
     * @throws \Swoft\Apollo\Exception\ApolloException
     */
    public function index(): array
    {

        
        return $this->apolloLogic->pull();
    }
}
