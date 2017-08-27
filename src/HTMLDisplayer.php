<?php
require_once('Trade.php');
require_once('Event.php');

abstract class DisplayMode
{
    const TABLE = 0;
    const SIMPLE = 1;
    // etc.
}

class SimpleHTMLDisplayer
{
    
    private $displayMode = DisplayMode::SIMPLE;
    
    public function __construct($displayMode=DisplayMode::SIMPLE)
    {
        $this->setDisplayMode($displayMode);
    }
    
    public function getDisplayMode(){
        return $this->displayMode;
    }
    
    public function setDisplayMode($displayMode){
        if(is_int($displayMode)){
            if($displayMode != DisplayMode::SIMPLE && $displayMode != DisplayMode::TABLE){
                throw new ErrorException("Display Mode unknown.");
            }
            $this->displayMode = $displayMode;
        }
        else{
            throw new ErrorException("Wrong type for displayMode. Expected int got: "
                .gettype($displayMode));
        }
    }
    
    public function displayTrade($trade){
        if($this->displayMode == DisplayMode::SIMPLE){
            return $this->simpleDisplayTrade($trade);
        }
        else{
            return $this->tableDisplayTrade($trade);
        }
    }
    
    public function displayHeaderForTableInTrade(){
        return "<tr>
            <th>Id</th>
            <th>Id Event</th>
            <th>Creation Time</th>
            <th>Open Time</th>
            <th>Close Time</th>
            <th>Value T-5</th>
            <th>Value T0</th>
            <th>Prediction</th>
            <th>Pred. Proba.</th>
            <th>Gain</th>
            <th>Commission</th>
            <th>Currency</th>
            <th>State</th>
            </tr>";
    }
    
    private function simpleDisplayTrade($trade)
    {
        $trade_string = $trade->getId().
        ";".$trade->getIDDBEvent().
        ";".$trade->getCreationTime()->format('Y-m-d H:i:s').
        ";".$trade->getOpenTime()->format('Y-m-d H:i:s').
        ";".$trade->getCloseTime()->format('Y-m-d H:i:s').
        ";".sprintf("%01.5f", $trade->getDvPTm5()).
        ";".sprintf("%01.5f", $trade->getDvPT0()).
        ";".$trade->getPrediction().
        ";".$trade->getP_proba().
        ";".$trade->getGain().
        ";".$trade->getCommission().
        ";".$trade->getState();
        return $trade_string;
    }
    
    private function tableDisplayTrade($trade)
    {
        $trade_string = "<td class='id'>".$trade->getId().
        "</td><td class='id_db_event'>".$trade->getIDDBEvent().
        "</td><td class='creation_time'>".$trade->getCreationTime()->format('Y-m-d H:i:s').
        "</td><td class='open_time'>".
            (is_null($trade->getOpenTime()) ? "" : $trade->getOpenTime()->format('Y-m-d H:i:s')).
        "</td><td class='close_time'>".s
        (is_null($trade->getCloseTime()) ? "" : $trade->getCloseTime()->format('Y-m-d H:i:s')).
        "</td><td class='market'>".sprintf("%01.5f", $trade->getDvPTm5()).
        "</td><td class='market'>".sprintf("%01.5f", $trade->getDvPT0()).
        "</td><td class='prediction'>".$trade->getPrediction().
        "</td><td class='p_prediction'>".$trade->getP_proba().
        "</td><td class='gain'>".$trade->getGain().
        "</td><td class='commission'>".$trade->getCommission().
        "</td><td class='currency'>".$trade->getCurrency().
        "</td><td class='state ".$trade->getStringFromState($trade->getState())." "
            .($trade->getGain() > 0 ? "won" : "lost")."'>"
            .$trade->getStringFromState($trade->getState())."</td>";
        return $trade_string;
    }

    
    public function displayEvent($event){
        if($this->displayMode == DisplayMode::SIMPLE){
            return $this->simpleDisplayEvent($event);
        }
        else{
            return $this->tableDisplayEvent($event);
        }

    }
    
    public function displayHeaderForTableInEvent(){
        return "<tr>
            <th>Id</th>
            <th>Id Event</th>
            <th>Announced Time</th>
            <th>Released Time</th>
            <th>Actual</th>
            <th>Previous</th>
            <th>Time Before Next Event</th>
            <th>State</th>
            </tr>";
    }
    
    private function tableDisplayEvent($event)
    {
        $event_string = "<td class='id'>".$event->getId()."</td>".
        "<td class='id_event'>".$event->getEventId()."</td>".
        "<td class='id_news'>".$event->getNewsId()."</td>".
        "<td class='announced'>".$event->getAnnouncedTime()->format('Y-m-d H:i:s')."</td>".
        "<td class='real'>".$event->getReleasedTime()->format('Y-m-d H:i:s')."</td>".
        "<td class='actual'>".$event->getActual()."</td>".
        "<td class='previous'>".$event->getPrevious()."</td>".
        "<td class='next_event'>".$event->getNextEvent()."</td>".
        "<td class='state ".$event->getStringFromState($event->getState())."'>"
            .$event->getStringFromState($event->getState())."</td>";
        return $event_string;
    }
    
    private function simpleDisplayEvent($event)
    {
        $event_string = $event->getId().
        ";".$event->getEventId().
        ";".$event->getNewsId().
        ";".$event->getAnnouncedTime()->format('Y-m-d H:i:s').
        ";".$event->getReleasedTime()->format('Y-m-d H:i:s').
        ";".$event->getActual().
        ";".$event->getPrevious().
        ";".$event->getNextEvent().
        ";".$event->getState();
        return $event_string;
    }

}
