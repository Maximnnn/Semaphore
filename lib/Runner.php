<?php

namespace Semaphore;

class Runner
{
    const THROW_ERROR = 1;
    const RETURN_FALSE = 2;

    const LOCK_TIME = 60;
    const SLEEP_TIME = 0.01;

    const NOT_EXECUTED = 'command not executed';

    /**
     * @var Semaphore
     */
    private $semaphore;

    public function __construct(Semaphore $semaphore)
    {
        $this->semaphore = $semaphore;
    }

    public function execute(LockedCommandInterface $command, int $errorCode = self::THROW_ERROR)
    {
        try {

            $result = $this->run($command, $errorCode);

        } catch (\Exception $exception) {
            $result = $this->onError($errorCode, $exception);
        } finally {
            $this->semaphore->release($this->getKey($command));
        }

        return $result;
    }

    protected function run(LockedCommandInterface $command, int $code)
    {
        $start = time();
        $end   = $start + $command->getMaxWaitTime();

        $executed = false;
        $result   = false;

        while (time() <= $end && !$executed) {

            if ($this->semaphore->acquire($this->getKey($command), self::LOCK_TIME)) {

                $result   = $command->execute();
                $executed = true;
            } else {
                sleep(self::SLEEP_TIME);
            }
        }

        return $executed ? $result : $this->onError($code);
    }

    protected function getKey(LockedCommandInterface $command): string
    {
        return get_class($command) . $command->getKey();
    }

    protected function onError(int $code, \Exception $exception = null)
    {
        switch ($code) {
            case self::THROW_ERROR:
                throw new \RuntimeException($exception ? $exception->getMessage() : self::NOT_EXECUTED);
                break;
            case self::RETURN_FALSE:
                return false;
                break;
        }
        return false;
    }
}