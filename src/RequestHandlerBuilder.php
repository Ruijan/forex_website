<?php

use src\requests\CancelTradeRequest;
use src\requests\CloseTradeRequest;
use src\requests\CollectEventsRequest;
use src\requests\NextActionRequest;
use src\requests\OpenTradeRequest;
use src\requests\PredictTradeRequest;
use src\requests\PredictableTradesRequest;
use src\requests\UpdateMarketRequest;

require_once(__DIR__.'/RequestHandler.php');

class RequestHandlerBuilder
{

    public function __construct()
    {}
    
    public function makeRequestHandlerWithRequest($request, $parameters, $mysqli){
        $link = "https://sslecal2.forexprostools.com?columns=exc_flags,exc_currency,".
            "exc_importance,exc_actual,exc_forecast,exc_previous&features=datepicker,".
            "timezone&countries=25,32,6,37,72,22,17,39,14,10,35,43,56,36,110,11,26,12,4,5".
            "&calType=day&timeZone=55&lang=1";
        
        $requestHandler = new RequestHandler();
        $requestHandler->setRequest($requestHandler->getRequestTypeFromString($request));
        
        $tradeDBHandler = new \TradeDBHandler($mysqli);
        $eventDBHandler = new \EventDBHandler($mysqli);
        $eventParser = new \EventParser($link);
        
        $requestHandler->init($tradeDBHandler, $eventDBHandler, $eventParser);
        $handlers = [
            new UpdateMarketRequest(),
            new PredictTradeRequest(),
            new PredictableTradesRequest(new SimpleHTMLDisplayer(DisplayMode::TABLE)),
            new OpenTradeRequest(),
            new CloseTradeRequest(),
            new CancelTradeRequest(),
            new NextActionRequest(new SimpleHTMLDisplayer(DisplayMode::SIMPLE)),
            new CollectEventsRequest(),
            new \GUIDisplayerRequest()
        ];
        
        foreach($handlers as $handler){
            $handler->init($tradeDBHandler, $eventDBHandler, $eventParser, $parameters);
        }
        
        $requestHandler->setRequestHandlers($handlers);
        return $requestHandler;
    }
}

