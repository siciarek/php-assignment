<?php

declare(strict_types=1);

namespace Tests\unit;

use PHPUnit\Framework\TestCase;
use SocialPost\Dto\SocialPostTo;

/**
 * Class ATestTest
 * @package Tests\unit
 * @covers SocialPost\Dto\SocialPostTo
 */
class SocialPostToTest extends TestCase
{
    /**
     * @test
     */
    public function gettersTest(): void
    {
        $id = "author_1";
        $type = "status";
        $authorName = "Raymond Chandler";

        $obj = (new SocialPostTo())
            ->setId($id)
            ->setType($type)
            ->setAuthorName($authorName);

        $this->assertEquals($id, $obj->getId());
        $this->assertEquals($type, $obj->getType());
        $this->assertEquals($authorName, $obj->getAuthorName());
    }
}
