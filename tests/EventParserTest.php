<?php
require_once(str_replace("tests", "src", __DIR__."/").'EventParser.php');
require_once(str_replace("tests", "vendor", __DIR__."/").'/autoload.php');
/**
 * EventParser test case.
 */
class EventParserTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     * @var EventParser
     */
    private $eventParser;

    /**
     * Prepares the environment before running a test.
     */
    protected function setUp()
    {
        parent::setUp();
        
        // TODO Auto-generated EventParserTest::setUp()
        $this->link = "https://sslecal2.forexprostools.com?columns=
                        exc_flags,exc_currency,exc_importance,exc_actual,
                        exc_forecast,exc_previous&features=datepicker,
                        timezone&countries=25,32,6,37,72,22,17,39,14,10,
                        35,43,56,36,110,11,26,12,4,5&calType=day&
                        timeZone=55&lang=1";
        $this->eventParser = new EventParser($this->link);
    }

    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown()
    {
        // TODO Auto-generated EventParserTest::tearDown()
        $this->eventParser = null;
        
        parent::tearDown();
    }

    /**
     * Constructs the test case.
     */
    public function __construct()
    {
        // TODO Auto-generated constructor
    }

    /**
     * Tests EventParser->__construct()
     */
    public function test__construct()
    {
        
        assert($this->eventParser->getLink() == $this->link, "Links should be equal");
    }
    
    public function testSetLinkWithWrongTypeShouldThrow(){
        $link = 65;
        $this->expectExceptionMessage("Wrong type for link. Expected string got: ".gettype($link));
        $this->eventParser->setLink($link);
    }

    public function test__retrieveTableOfEvents()
    {
        $link = "https://sslecal2.forexprostools.com?columns=exc_flags,exc_currency,".
            "exc_importance,exc_actual,exc_forecast,exc_previous&features=datepicker,".
            "timezone&countries=25,32,6,37,72,22,17,39,14,10,35,43,56,36,110,11,26,12,4,5".
            "&calType=day&timeZone=55&lang=1";
        $this->eventParser->setLink($link);
        $this->eventParser->retrieveTableOfEvents();
        assert(!is_null($this->eventParser->getTable()->getElementByID('ecEventsTable')));
    }
    
    public function test__createEventsFromTable(){
        $link = "https://sslecal2.forexprostools.com?columns=exc_flags,exc_currency,".
            "exc_importance,exc_actual,exc_forecast,exc_previous&features=datepicker,".
            "timezone&countries=25,32,6,37,72,22,17,39,14,10,35,43,56,36,110,11,26,12,4,5".
            "&calType=day&timeZone=55&lang=1";
        $this->eventParser->setLink($link);
        $this->eventParser->retrieveTableOfEvents();
        $this->eventParser->createEventsFromTable();
        $events = $this->eventParser->getEvents();
        $last_event = $events[sizeof($events)-1];
        $end_of_the_day = DateTime::createFromFormat('Y-m-d H:i:s',(gmdate('Y-m-d H:i:s', time())));
        $end_of_the_day->setTime(23,59,59);
        $time_diff = $end_of_the_day->diff($last_event->getAnnouncedTime());
        $time_diff = $time_diff->s +
            $time_diff->i*60 +
            $time_diff->h*60*60 +
            $time_diff->d*24*60*60;
        
        assert(sizeof($events) > 0, "Check Size of Events :".sizeof($events));
        assert($last_event->getNextEvent() == $time_diff, 
            "Last event next event should be the difference with midnight :".
            $last_event->getNextEvent()." expected ".$time_diff);
    }
}

