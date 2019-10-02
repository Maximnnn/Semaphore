<?php
require_once 'vendor/autoload.php';

$m = new \Memcached();
$m->addServer('127.0.0.1', 11211);

$s = new \Semaphore\Semaphore($m);

class Command implements \Semaphore\LockedCommandInterface
{

    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function execute()
    {
        return ($this->data * 2);
    }

    public function getKey(): string
    {
        return 'key';
    }

    public function getMaxWaitTime(): int
    {
        return 10;
    }
}

$r = new \Semaphore\Runner($s);

$command = new Command(222);

echo 'acquire: '; var_dump($s->acquire('Commandkey', 5));

echo PHP_EOL;

echo 'acquire: '; var_dump($s->acquire('Commandkey', 5));

echo PHP_EOL;

echo 'key: ' . get_class($command) . 'key';

echo PHP_EOL;
echo PHP_EOL;
echo PHP_EOL;

var_dump($r->execute($command));


