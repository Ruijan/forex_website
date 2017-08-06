<?php
require_once('calendar/classes/tc_calendar.php');
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
            if($displayMode == DisplayMode::SIMPLE || $displayMode == DisplayMode::TABLE){
                $this->displayMode = $displayMode;
            }
            else{
                throw new ErrorException("Display Mode unknown.");
            }
        }
        else{
            throw new ErrorException("Wrong type for displayMode. Expected int got: ".gettype($displayMode));
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
    
    private function simpleDisplayTrade($trade)
    {
        $trade_string = $trade->getId().";".$trade->getIDDBEvent().";".$trade->getCreationTime()->format('Y-m-d H:i:s').
        ";".$trade->getOpenTime()->format('Y-m-d H:i:s').";".$trade->getCloseTime()->format('Y-m-d H:i:s').
        ";".sprintf("%01.5f", $trade->getDv_p_tm5()).";".sprintf("%01.5f", $trade->getDv_p_t0()).";".$trade->getPrediction().
        ";".$trade->getP_proba().";".$trade->getGain().";".$trade->getCommission().";".$trade->getState();
        return $trade_string;
    }
    
    private function tableDisplayTrade($trade)
    {
        $trade_string = "<td class='id'>".$trade->getId().
        "</td><td class='id_db_event'>".$trade->getIDDBEvent().
        "</td><td class='creation_time'>".$trade->getCreationTime()->format('Y-m-d H:i:s').
        "</td><td class='open_time'>".$trade->getOpenTime()->format('Y-m-d H:i:s').
        "</td><td class='close_time'>".$trade->getCloseTime()->format('Y-m-d H:i:s').
        "</td><td class='market'>".sprintf("%01.5f", $trade->getDv_p_tm5()).
        "</td><td class='market'>".sprintf("%01.5f", $trade->getDv_p_t0()).
        "</td><td class='prediction'>".$trade->getPrediction().
        "</td><td class='p_prediction'>".$trade->getP_proba().
        "</td><td class='gain'>".$trade->getGain().
        "</td><td class='commission'>".$trade->getCommission().
        "</td><td class='state'>".Trade::getStringFromState($trade->getState())."</td>";
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
    
    private function tableDisplayEvent($event)
    {
        $event_string = "<td class='id'>".$event->getId()."</td><td class='id_event'>".$event->getEventId().
        "</td><td class='id_news'>".$event->getNewsId()."</td><td class='announced'>".
        $event->getAnnouncedTime()->format('Y-m-d H:i:s')."</td><td class='real'>".
        $event->getRealTime()->format('Y-m-d H:i:s')."</td><td class='actual'>".
        $event->getActual()."</td><td class='previous'>".$event->getPrevious().
        "</td><td class='next_event'>".$event->getNextEvent()."</td><td class='state'>".
        Event::getStringFromState($event->getState())."</td>";
        return $event_string;
    }
    
    private function simpleDisplayEvent($event)
    {
        $event_string = $event->getId().";".$event->getEventId().";".$event->getNewsId().
        ";".$event->getAnnouncedTime()->format('Y-m-d H:i:s').";".$event->getRealTime()->format('Y-m-d H:i:s').
        ";".$event->getActual().";".$event->getPrevious().";".$event->getNextEvent().";".$event->getState();
        return $event_string;
    }

}
