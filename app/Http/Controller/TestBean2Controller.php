<?php declare(strict_types=1);

namespace App\Http\Controller;

use Swoft\Context\Context;
use Swoft\Http\Message\Response;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Throwable;

/**
 * Class HomeController
 * @Controller("test-bean2")
 */
class TestBean2Controller
{

    /**
     * @RequestMapping("test")
     * @throws Throwable
     */
    public function index(): Response
    {
        return Context::mustGet()->getResponse();
    }
}
