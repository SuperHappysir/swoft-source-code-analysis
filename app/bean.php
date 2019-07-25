<?php

use App\Common\DbSelector;
use App\Common\RpcProvider;
use Swoft\Db\Database;
use Swoft\Db\Pool;
use Swoft\Http\Server\HttpServer;
use Swoft\Http\Server\Swoole\RequestListener;
use Swoft\Log\Handler\FileHandler;
use Swoft\Redis\RedisDb;
use Swoft\Rpc\Client\Client as ServiceClient;
use Swoft\Rpc\Client\Pool as ServicePool;
use Swoft\Rpc\Server\ServiceServer;
use Swoft\Server\SwooleEvent;
use Swoft\Task\Swoole\FinishListener;
use Swoft\Task\Swoole\TaskListener;
use Swoft\WebSocket\Server\WebSocketServer;

return [
    'lineFormatter'      => [
        'format'     => '%datetime% [%level_name%] [%channel%] [%event%] [tid:%tid%] [cid:%cid%] [traceid:%traceid%] [spanid:%spanid%] [parentid:%parentid%] [context:%context%] %messages%',
        'dateFormat' => 'Y-m-d H:i:s',
    ],
    'noticeHandler'      => [
        'class'     => FileHandler::class,
        'logFile'   => '@runtime/logs/notice.log',
        'formatter' => \bean('lineFormatter'),
        'levels'    => 'notice,info,debug,trace',
    ],
    'applicationHandler' => [
        'class'     => FileHandler::class,
        'logFile'   => '@runtime/logs/error.log',
        'formatter' => \bean('lineFormatter'),
        'levels'    => 'error,warning',
    ],
    'logger'             => [
        'flushRequest' => true,
        'enable'       => true,
        'json'         => true,
        'handlers'     => [
            'application' => \bean('applicationHandler'),
            'notice'      => \bean('noticeHandler'),
        ],
    ],
    'httpServer'         => [
        'class'    => HttpServer::class,
        'port'     => 18306,
        'listener' => [
            'rpc' => bean('rpcServer')
        ],
        'process'  => [//            'monitor' => bean(MonitorProcess::class)
        ],
        'on'       => [
            //            SwooleEvent::TASK   => bean(SyncTaskListener::class),  // Enable sync task
            SwooleEvent::TASK   => bean(TaskListener::class),  // Enable task must task and finish event
            SwooleEvent::FINISH => bean(FinishListener::class)
        ],
        /* @see HttpServer::$setting */
        'setting'  => [
            'heartbeat_check_interval' => 60,
            'heartbeat_idle_time'      => 600,
            'reactor_num'              => 12,
            'worker_num'               => 12,
            'task_worker_num'          => 12,
            'task_enable_coroutine'    => true
        ]
    ],
    'httpDispatcher'     => [
        // Add global http middleware
        'middlewares' => [
            // Allow use @View tag
            \Swoft\View\Middleware\ViewMiddleware::class,
        ],
    ],
    'db'                 => [
        'class'    => Database::class,
        'dsn'      => 'mysql:dbname=test;host=127.0.0.1',
        'username' => 'root',
        'password' => '123456',
    ],
    'db2'                => [
        'class'      => Database::class,
        'dsn'        => 'mysql:dbname=test2;host=172.17.0.2',
        'username'   => 'root',
        'password'   => 'swoft123456',
        'dbSelector' => bean(DbSelector::class)
    ],
    'db2.pool'           => [
        'class'    => Pool::class,
        'database' => bean('db2')
    ],
    'db3'                => [
        'class'    => Database::class,
        'dsn'      => 'mysql:dbname=test2;host=172.17.0.2',
        'username' => 'root',
        'password' => 'swoft123456'
    ],
    'db3.pool'           => [
        'class'    => Pool::class,
        'database' => bean('db3')
    ],
    'migrationManager'   => [
        'migrationPath' => '@app/Migration',
    ],
    'redis'              => [
        'class'    => RedisDb::class,
        'host'     => '127.0.0.1',
        'port'     => 6379,
        'database' => 0,
        'option'   => [
            'prefix' => 'swoft:'
        ]
    ],
    'user'               => [
        'class'    => ServiceClient::class,
        'host'     => '127.0.0.1',
        'port'     => 18307,
        'setting'  => [
            'timeout'         => 0.5,
            'connect_timeout' => 1.0,
            'write_timeout'   => 10.0,
            'read_timeout'    => 0.5,
        ],
        'packet'   => bean('rpcClientPacket'),
        'provider' => bean(RpcProvider::class)
    ],
    'user.pool'          => [
        'class'  => ServicePool::class,
        'client' => bean('user')
    ],
    'rpcServer'          => [
        'class' => ServiceServer::class,
    ],
    'wsServer'           => [
        'class'   => WebSocketServer::class,
        'port'    => 18308,
        'on'      => [
            // Enable http handle
            SwooleEvent::REQUEST => bean(RequestListener::class),
        ],
        'debug'   => env('SWOFT_DEBUG', 0),
        /* @see WebSocketServer::$setting */
        'setting' => [
            'log_file' => alias('@runtime/swoole.log'),
        ],
    ],
    'tcpServer'          => [
        'port'  => 18309,
        'debug' => 1,
    ],
    /** @see \Swoft\Tcp\Protocol */
    'tcpServerProtocol'  => [
        'type' => \Swoft\Tcp\Packer\SimpleTokenPacker::TYPE,
        // 'openLengthCheck' => true,
    ],
    'cliRouter'          => [// 'disabledGroups' => ['demo', 'test'],
    ]
];
