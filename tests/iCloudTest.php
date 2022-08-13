<?php

declare(strict_types=1);

/*
 * This file is part of the szwtdl/icloud.
 *
 * (c) pengjian <szpengjian@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Tests;

use Cloud\Icloud\Application;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class iCloudTest extends TestCase
{
    public function testLogin()
    {
        $icloud = \Mockery::mock(Application::class, ['client_id' => '111', 'client_key', 'domain' => 'https://localhost:8080']);
        $icloud->shouldReceive('login')->once()->andReturn([
            'code' => 200,
            'msg' => 'ok',
            'data' => [],
        ]);
        $result = $icloud->login('szpengjian@gmail.com', '12345678');
        $this->assertIsArray($result);
        $this->assertEquals([
            'code' => 200,
            'msg' => 'ok',
            'data' => [],
        ], $result);
    }

    public function testDownload()
    {
        $icloud = \Mockery::mock(Application::class, ['client_id' => '111', 'client_key', 'domain' => 'https://localhost:8080']);
        $icloud->shouldReceive('download')->once()->andReturn([
            'code' => 200,
            'msg' => 'ok',
            'data' => [],
        ]);
        $result = $icloud->download('szpengjian@gmail.com', '12345678');
        $this->assertIsArray($result);
        $this->assertEquals([
            'code' => 200,
            'msg' => 'ok',
            'data' => [],
        ], $result);
    }
}
