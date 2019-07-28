<?php declare(strict_types=1);


namespace App\Process;


use App\Model\Entity\User;
use Swoft\Db\Exception\DbException;
use Swoft\Log\Helper\CLog;
use Swoft\Process\Annotation\Mapping\Process;
use Swoft\Process\Contract\ProcessInterface;
use Swoft\Redis\Redis;
use Swoole\Coroutine;
use Swoole\Process\Pool;

/**
 * Class Worker2Process
 *
 * @since 2.0
 *
 * @Process(workerId={1,2})
 */
class Worker2Process implements ProcessInterface
{
    /**
     * @param Pool $pool
     * @param int  $workerId
     *
     * @throws DbException
     */
    public function run(Pool $pool, int $workerId): void
    {
        while (true) {
            // CLog::info('worker-' . $workerId);
    
            Coroutine::sleep(4);
        }
    }
}
