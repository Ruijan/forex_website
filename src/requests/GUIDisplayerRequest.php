<?php
use src\requests\ForexRequest;

$pathToSrc = str_replace("requests/", "", __DIR__."/");

require_once('ForexRequest.php');
require_once($pathToSrc.'HTMLDisplayer.php');

class GUIDisplayerRequest extends ForexRequest
{
    public function __construct()
    {
    }
    
    public function execute(){
        print($this->createHeader().$this->createTopNavBar()
        .$this->createLeftNavBar().$this->createTopBodyTag().$this->createBody().$this->createBottomBodyTag());
    }
    
    
    private function createHeader(){
        return '
      <!DOCTYPE html><html lang="en">
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" href="../../favicon.ico">
        <title>Statistiques</title>
        <link href="../../bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="../../bootstrap/css/ie10-viewport-bug-workaround.css" rel="stylesheet">
        <link href="../styles/dashboard.css" rel="stylesheet">
        <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
        <script src="../../bootstrap/js/ie-emulation-modes-warning.js"></script>
        <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
        <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    </head>';
    }
    
    private function createTopNavBar(){
        return '<nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" '.
          'data-target="#navbar" aria-expanded="false" aria-controls="nav-sidebar"
          onclick="if($(\'.sidebar\').css(\'display\') == \'none\'){
            $(\'.sidebar\').css(\'display\', \'block\');
          }
          else{
            $(\'.sidebar\').css(\'display\', \'none\');
          }">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Trading Analysis</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            
        </div>
      </div>
    </nav>';
    }
    
    private function createLeftNavBar(){
        $toptag = '<div class="container-fluid">
      <div class="row">
        <div class="col-sm-3 col-md-2 sidebar">';
        
        $list = '<ul class="nav nav-sidebar">
            <li '. ((!isset($this->parameters['stat']) || $this->parameters['stat'] == 'events') ? 'class="active"' : '') 
            .'><a href="index.php?stat=events">Events</a></li>
            <li '. ((isset($this->parameters['stat']) && $this->parameters['stat'] == 'trades') ? 'class="active"' : '')
            .'><a href="index.php?stat=trades">Trades</a></li>
          </ul>';
        $calendars = "<hr/>".$this->createFromToCalendars();
        $bottomtag = '
        </div>
      </div>
    </div>';
        return $toptag.$list.$calendars.$bottomtag;
    }
    private function createTopBodyTag(){
        return '<body class="col-md-offset-2">
      <head>
        <link rel="stylesheet" href="../styles/stats_style.css">
      </head>
      <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
      <script type="text/javascript" src="https://www.google.com/jsapi"></script>
      <section id="conteneur"><article>';
    }
    private function createBody(){
        $fromDate = $this->createDateFromParameters("from");
        $fromDate->setTime(0,0,0);
        $toDate = $this->createDateFromParameters("to");
        $toDate->setTime(23,59,59);
        $simpleDisplayer = new SimpleHTMLDisplayer(DisplayMode::TABLE);
        $body = "";
        if (isset($this->parameters['stat']) and $this->parameters['stat'] == 'trades'){
            $trades = $this->tradeDBHandler->getTradesFromTo($fromDate, $toDate);
            $body .= "<h1>Displaying Trades";
            if(isset($this->parameters["from"])){
                $body .= " from ".$fromDate->format("Y-m-d");
            }
            else{
                $body .= " from Today";
            }
            if(isset($this->parameters["to"])){
                $body .= " to ".$toDate->format("Y-m-d");
            }
            $body .= "</h1><br/>";
            $body .= "<table>";
            $body .= $simpleDisplayer->displayHeaderForTableInTrade();
            foreach($trades as $trade){
                $body .= "<tr>".$simpleDisplayer->displayTrade($trade)."</tr>";
            }
        }
        elseif(isset($this->parameters['stat']) and $this->parameters['stat'] == 'events'){
            $events = $this->eventDBHandler->getEventsFromTo($fromDate, $toDate);
            $body .= "<h1>Displaying Events";
            if(isset($this->parameters["from"])){
                $body .= " from ".$fromDate->format("Y-m-d");
            }
            else{
                $body .= " from Today";
            }
            
            if(isset($this->parameters["to"])){
                $body .= " to ".$toDate->format("Y-m-d");
            }
            $body .= "</h1><br/>";
            $body .= "<table>";
            $body .= $simpleDisplayer->displayHeaderForTableInEvent();
            foreach($events as $event){
                $body .= "<tr>".$simpleDisplayer->displayEvent($event)."</tr>";
            }
        }
        return $body."</table>";
    }
    
    private function createDateFromParameters($parameterName)
    {
        $date = new DateTime();
        $date = $date->createFromFormat("Y-m-d",gmdate('Y-m-d', time()));
        if(isset($this->parameters[$parameterName]) and $this->parameters[$parameterName]!= ""){
            $date = $date->createFromFormat("m/d/Y",$this->parameters[$parameterName]);
        }
        return $date;
    }

    private function createFromToCalendars(){
        
        $toptag = "<div sytle='margin: auto; width:100%; text-align: center;'>";
        $calendar = $this->createCalendar('from','to', date("Y-m-d"));
        $form = "<form action='index.php' method='post'>
    <input id='stat' name='stat' value='".
    (isset($this->parameters['stat']) ? $this->parameters['stat'] : 'events').
    "' type='hidden'/>
    <table style='width:100%;'>
    <tr><td style='width:30%;'><label for='from'>From </label></td><td style='width:70%;'>
        <input type='text' id='from' name='from' style='width:100%; text-align: center;' value='".
        (isset($this->parameters['from']) ? $this->parameters['from'] : '')."'></td></tr>
    <tr><td><label for='to'>To </label></td><td>
        <input type='text' id='to' name='to' style='width:100%; text-align: center;' value='".
        (isset($this->parameters['to']) ? $this->parameters['to'] : '')."'></td></tr>
    </table>";
        
        $validate = '<input type="submit" id="button" value="Apply">';
        $bottomtag = "</form></div>";
        return $toptag.$calendar.'<br/>'.$form.$validate.$bottomtag;
    }
    private function createCalendar($date1, $date2, $default){
        $onChangeScript = '
    <script>
        $("selector").datepicker({
            
        });
        $( function() {
        var dateFormat = "mm/dd/yy",
          from = $( "#from" )
            .datepicker({
              beforeShowDay: $.datepicker.noWeekends, // disable weekends
              changeMonth: true,
              numberOfMonths: 1
            })
            .on( "change", function() {
              to.datepicker( "option", "minDate", getDate( this ) );
            }),
          to = $( "#to" ).datepicker({
            beforeShowDay: $.datepicker.noWeekends, // disable weekends
            changeMonth: true,
            numberOfMonths: 1
          })
          .on( "change", function() {
            from.datepicker( "option", "maxDate", getDate( this ) );
          });
        
        function getDate( element ) {
          var date;
          try {
            date = $.datepicker.parseDate( dateFormat, element.value );
          } catch( error ) {
            date = null;
          }
        
          return date;
        }
        } );
      </script>';
        return $onChangeScript;
    }
    
    private function createBottomBodyTag(){
        return '</article></section></body>';
    }
    
}
