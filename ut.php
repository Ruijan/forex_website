<?php

include 'phpunit-6.2.3.phar';

/**
 * @covers Event
 */
final class EventTest extends TestCase
{
    public function testCanBeCreatedFromValidEmailAddress()
    {
        $this->assertEquals(
            new DateTime('now'),
            $this->t_a
        );
    }
}
