<?php


namespace Semaphore;


interface LockedCommandInterface
{
    public function execute();

    public function getKey(): string;

    public function getMaxWaitTime(): int;
}