<?php

namespace App\Model\Logic;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Primary;

/**
 * Class TestImpl
 *
 * @Bean()
 * @Primary()
 */
class TestImpl implements TestInterface
{
    public function test()
    {
        echo 1;
    }
}
