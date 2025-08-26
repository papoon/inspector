<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class InspectorUnresolvableFilterTest extends TestCase
{
    public function testFilterByErrorType(): void
    {
        $brokenDetails = [
            'broken1' => ['type' => 'ReflectionException', 'message' => 'Class does not exist', 'exception' => new Exception()],
            'broken2' => ['type' => 'BindingResolutionException', 'message' => 'Unresolvable dependency', 'exception' => new Exception()],
        ];

        $filtered = array_filter($brokenDetails, function ($info) {
            return mb_stripos($info['type'], 'Reflection') !== false;
        });

        $this->assertArrayHasKey('broken1', $filtered);
        $this->assertArrayNotHasKey('broken2', $filtered);
    }
}
