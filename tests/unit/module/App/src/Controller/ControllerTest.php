<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use App\Controller\Controller;

class DummyController extends Controller
{

}

/**
 * Class ATestTest
 * @covers App\Controller\Controller
 * @package Tests\unit
 */
class ControllerTest extends TestCase
{
    /**
     * @test
     */
    public function render(): void
    {
        $controller = new DummyController();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Template nonexisting-template not found");
        $controller->render([], "nonexisting-template");
    }
}
