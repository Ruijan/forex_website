<?php

require_once 'calendar/classes/TcCalendar.php';

class GUIHTMLDisplayer
{
    
    public function __construct($display_mode)
    {
        parent::__construct($display_mode);
    }
    
    public function createPage(){
        return $this->createTopHeader().$this->createHeader().$this->createTopNavBar()
        .$this->createLeftNavBar().$this->createTopBodyTag().$this->createBody().$this->createBottomBodyTag();
    }
    
    private function createTopHeader(){
        return '<!DOCTYPE html><html lang="en">';
    }
    
    private function createHeader(){
        return '
      <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="icon" href="../../favicon.ico">
        <title>Statistiques</title>
        <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
        <link href="../bootstrap/css/ie10-viewport-bug-workaround.css" rel="stylesheet">
        <link href="styles/dashboard.css" rel="stylesheet">
        <script src="../bootstrap/js/ie-emulation-modes-warning.js"></script>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
        <script language="javascript" src="calendar/calendar.js"></script>
    </head>';
    }
    
    private function createTopNavBar(){
        return '<nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container-fluid">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="nav-sidebar"
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
            <li '. (!isset($_REQUEST['stat']) || $_REQUEST['stat'] == 'events' ? 'class="active"' : '') .'><a href="index.php?stat=events">Events <span class="sr-only">(current)</span></a></li>
            <li '. (isset($_REQUEST['stat']) && $_REQUEST['stat'] == 'trades' ? 'class="active"' : "").'><a href="index.php?stat=trades">Trades</a></li>
          </ul>';
        $calendars = "<hr/>".createFromToCalendars();
        $bottomtag = '
        </div>
      </div>
    </div>';
        return $toptag.$list.$calendars.$bottomtag;
    }
    private function createTopBodyTag(){
        return '<body class="col-md-offset-2">
      <head>
        <link rel="stylesheet" href="styles/stats_style.css">
        <link href="calendar/calendar.css" rel="stylesheet" type="text/css" />
      </head>
      <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
      <script type="text/javascript" src="https://www.google.com/jsapi"></script>
      <section id="conteneur"><article>';
    }
    private function createBody(){
        $events = "";
        $mysqli = connect_database();
        $date = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
        if(isset($_GET['year_from']) and isset($_GET['month_from']) and isset($_GET['day_from'])){
            $date = mktime(0,0,0, (int)$_GET['month_from'], (int)$_GET['day_from'], (int)$_GET['year_from']);
        }
        if (isset($_REQUEST['stat']) && $_REQUEST['stat'] == 'trades'){
            $events = getEventsFromDate($mysqli, $date, 5);
        }
        else{
            $events = getEventsFromDate($mysqli, $date);
        }
        return $events;
    }
    private function createFromToCalendars(){
        
        $toptag = "<div sytle='margin: auto; width:100%; text-align: center;'>";
        $from_calendar = "From: ".createCalendar('from','to', date("Y-m-d"));
        $to_calendar = "To: ".createCalendar('to','from', date("Y-m-d"));
        $form = "<form action='index.php' method='get'>
    <input id='stat' name='stat' value='".(isset($_REQUEST['stat']) ? $_REQUEST['stat'] : 'events')."' type='hidden'/>
    <input id='year_from' name='year_from' value='' type='hidden'/>
    <input id='month_from' name='month_from' value='' type='hidden'/>
    <input id='day_from' name='day_from' value='' type='hidden'/>
    <input id='year_to' name='year_to' value='' type='hidden'/>
    <input id='month_to' name='month_to' value='' type='hidden'/>
    <input id='day_to' name='day_to' value='' type='hidden'/>";
        
        $validate = '<input type="submit" id="button" value="Apply">';
        $bottomtag = "</form></div>";
        return $toptag.$from_calendar.'<br/>'.$to_calendar.$form.$validate.$bottomtag;
    }
    private function createCalendar($date1, $date2, $default){
        $myCalendar = new TcCalendar($date1, true,false);
        $myCalendar->setIcon("calendar/images/iconCalendar.gif");
        $myCalendar->setDate(date('d', strtotime($default))
            , date('m', strtotime($default))
            , date('Y', strtotime($default)));
        $myCalendar->setPath("calendar/");
        $myCalendar->setYearInterval(2017, date('Y', strtotime($default)));
        $myCalendar->dateAllow('1900-01-01', '2025-03-01');
        $myCalendar->setOnChange("myChanged('".$date1."')");
        $myCalendar->disabledDay('sat');
        $myCalendar->disabledDay('sun');
        $myCalendar->setDatePair($date1, $date2, $default);
        $myCalendar->setAlignment('left', 'bottom');
        $myCalendar->setTheme('theme3');
        $calendar = '
    <script language="javascript">
    <!--
    function myChanged(v){
      var date_elements = document.getElementById(v).value.split("-");
      document.getElementById("year_" + v).value = date_elements[0];
      document.getElementById("month_" + v).value = date_elements[1];
      document.getElementById("day_" + v).value = date_elements[2];
    }
    //-->
    </script>';
        return $calendar.$myCalendar->getScript();
    }
    
    private function createBottomBodyTag(){
        return '</article></section></body>';
    }
    
}
