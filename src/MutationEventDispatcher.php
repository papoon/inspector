<?php

namespace Inspector;

class MutationEventDispatcher
{
    /** @var callable[] */
    protected array $listeners = [];

    public function listen(callable $listener): void
    {
        $this->listeners[] = $listener;
    }

    /**
     * @param array{
     *   timestamp: float,
     *   type: string,
     *   action: string,
     *   service: string,
     *   details: mixed
     * } $mutation
     */
    public function dispatch(array $mutation): void
    {
        foreach ($this->listeners as $listener) {
            $listener($mutation);
        }
    }
}
