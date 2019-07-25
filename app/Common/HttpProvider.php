<?php declare(strict_types = 1);

namespace App\Common;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Consul\Agent;

/**
 * Class RpcProvider
 *
 * @since 2.0
 *
 * @Bean()
 */
class HttpProvider
{
    /**
     * @Inject()
     *
     * @var Agent
     */
    private $agent;
    
    /**
     *
     * @param string $serviceName
     *
     * @return array
     * @throws \ReflectionException
     * @throws \Swoft\Bean\Exception\ContainerException
     * @throws \Swoft\Consul\Exception\ClientException
     * @throws \Swoft\Consul\Exception\ServerException
     */
    public function getList(string $serviceName = 'swoft') : array
    {
        // Get health service from consul
        $services = $this->agent->services();
        
        $result = $services->getResult();
        
        $services = [];
        foreach ($result as $id => $service) {
            if ($serviceName === $service['Service']) {
                $services[] = [
                    'Address' => $service['Address'],
                    'Port'    => $service['Port'],
                    'Tags'    => $service['Tags'],
                ];
            }
        }
        
        return $services;
    }
}
