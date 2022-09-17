<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use App\Controller\StatisticsController;
use App\Controller\Factory\StatisticsControllerFactory;
use SocialPost\Service\Factory\SocialPostServiceFactory;
use Statistics\Service\Factory\StatisticsServiceFactory;
use Statistics\Extractor\StatisticsToExtractor;

class MockedStatisticsController extends StatisticsController
{
    /**
     * @param array $vars
     * @param string $template
     * @param bool $useLayout
     */
    public function render(array $vars, string $template, $useLayout = true)
    {
        echo json_encode($vars, JSON_PRETTY_PRINT);
    }
}


/**
 * Class ATestTest
 * @covers App\Controller\StatisticsController
 * @package Tests\unit
 */
class StatisticsControllerTest extends TestCase
{
    public function setUp(): void
    {
        $dotEnv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/../../../../../..', '.env.test');
        $dotEnv->load();

        \App\Config\Config::init();
    }

    /**
     * @test
     */
    public function indexAction(): void
    {
        $this->controller = (new StatisticsControllerFactory())->create();

        $this->assertInstanceOf(StatisticsController::class, $this->controller);
        $params = ["month" => null];
        ob_start();
        @$this->controller->indexAction($params);
        ob_end_clean();
    }

    public function throwDummyException()
    {
        throw new \Exception("Dummy exception.");
    }

    /**
     * @test
     */
    public function indexActionInvalid(): void
    {
        $statsService = StatisticsServiceFactory::create();
        $socialService = SocialPostServiceFactory::create();
        $extractor = $this->getMockBuilder(StatisticsToExtractor::class)
            ->setMethods(["extract"])
            ->getMock();
        $extractor->expects($this->once())
            ->method("extract")
            ->willThrowException(new \Exception());

        $controller = new MockedStatisticsController($statsService, $socialService, $extractor);

        $params = ["month" => null];

        ob_start();
        @$controller->indexAction($params);
        $actual = ob_get_contents();
        ob_end_flush();

        $data = json_decode($actual, true);
        $this->assertArrayHasKey("message", $data);
        $this->assertEquals("An error occurred", $data["message"]);
    }
}
