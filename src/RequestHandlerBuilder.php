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
    
    public function makeRequestHandlerWithRequest($request, $mysqli){
        $link = "https://sslecal2.forexprostools.com?columns=exc_flags,exc_currency,".
            "exc_importance,exc_actual,exc_forecast,exc_previous&features=datepicker,".
            "timezone&countries=25,32,6,37,72,22,17,39,14,10,35,43,56,36,110,11,26,12,4,5".
            "&calType=day&timeZone=55&lang=1";
        
        $requestHandler = new RequestHandler();
        $requestHandler->setRequest($requestHandler->getRequestTypeFromString($request));
        $requestHandler->init(new \TradeDBHandler($mysqli), new \EventDBHandler($mysqli), 
            new \EventParser($link));
        
        $handlers = [
            new UpdateMarketRequest(),
            new PredictTradeRequest(),
            new PredictableTradesRequest(),
            new OpenTradeRequest(),
            new CloseTradeRequest(),
            new CancelTradeRequest(),
            new NextActionRequest(),
            new CollectEventsRequest()
        ];
        $requestHandler->setRequestHandlers($handlers);
        return $requestHandler;
    }
}

