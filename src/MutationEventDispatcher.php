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

    public function dispatch(array $mutation): void
    {
        foreach ($this->listeners as $listener) {
            $listener($mutation);
        }
    }
}