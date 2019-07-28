<?php declare(strict_types=1);


namespace App\Process;


use App\Model\Logic\MonitorLogic;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Db\Exception\DbException;
use Swoft\Log\Helper\CLog;
use Swoft\Process\Process;
use Swoft\Process\UserProcess;
use Swoole\Coroutine;

/**
 * Class MonitorProcess
 *
 * @since 2.0
 *
 * @Bean()
 */
class MnsProcess extends UserProcess
{
    /**
     * @param Process $process
     */
    public function run(Process $process): void
    {
        $process->name('mns-consumption-process');
    
        while (true) {
            // 拉取mns
            // CLog::info('拉取mns成功');
            
            // 消费mns
            // CLog::info('消费mns成功');
            
            Coroutine::sleep(3);
        }
    }
}
