<?php
/**
 * Created by PhpStorm.
 * User: MSI-GP60
 * Date: 7/15/2016
 * Time: 8:09 PM
 *
 * =========================================================
 * FUNCTIONS
 * connect_database()
 * createNewUser($mysqli, $id, $name)
 * createSettingsDepedency($mysqli, $id_user)
 * createTowersDepedency($mysqli, $id_user)
 * getUserSetting($mysqli, $id_user, $setting)
 * getUserTowerData($mysqli, $id_user, $tower_name)
 * isUserExist($mysqli, $id, $name)
 * checkUserParameter($mysqli, $id_game, $column_name, $value)
 * deleteUserByID($mysqli, $id)
 * deleteUserByGameID($mysqli, $id_game)
 * deleteSettingsDepedency($mysqli, $id_user)
 * deleteTowersDepedency($mysqli, $id_user)
 * updateUserLastConnectionTime($mysqli, $id_game, $time)
 */
require("connect.php");

class Event
{
    // property declaration
    public $id = -1;
    public $event_id;
    public $news_id;
    public $t_a;
    public $t_r;
    public $t_u;
    public $actual = NULL;
    public $previous = NULL;
    public $state = 0;
    public $next_event = 0;

    // method declaration
    function __construct() {
       $this->t_a = new DateTime('now');
       $this->t_a->setTimezone(new DateTimeZone("UTC"));
       $this->t_r = new DateTime('now');
       $this->t_r->setTimezone(new DateTimeZone("UTC"));
       $this->t_u = new DateTime('now');
       $this->t_u->setTimezone(new DateTimeZone("UTC"));
    }

    public function fillFromPost() {
        $previous_t_a = $this->t_a;
        if(isset($_POST["id"])){
            $this->id = $_POST["id"];
        }
        if(isset($_POST["event_id"])){
            $this->event_id = $_POST["event_id"];
        }
        if(isset($_POST["news_id"])){
            $this->news_id = $_POST["news_id"];
        }
        if(isset($_POST["t_a"])){
            $this->t_a = DateTime::createFromFormat('d/m/Y H:i:s', $_POST["t_a"]);
        }
        if(isset($_POST["t_r"])){
            $this->t_r = DateTime::createFromFormat('d/m/Y H:i:s', $_POST["t_r"]);
        }
        else{
            if($this->t_r == $previous_t_a){
                $this->t_r = $this->t_a;
            }
        }
        if(isset($_POST["actual"])){
            $this->actual = $_POST["actual"];
        }
        if(isset($_POST["previous"])){
            $this->previous = $_POST["previous"];
        }
        if(isset($_POST["dv_p_tm5"])){
            $this->dv_p_tm5 = $_POST["dv_p_tm5"];
        }
        if(isset($_POST["dv_p_t0"])){
            $this->dv_p_t0 = $_POST["dv_p_t0"];
        }
        if(isset($_POST["prediction"])){
            $this->prediction = $_POST["prediction"];
        }
        if(isset($_POST["p_proba"])){
            $this->p_proba = $_POST["p_proba"];
        }
        if(isset($_POST["label"])){
            $this->label = $_POST["label"];
        }
        if(isset($_POST["av_success"])){
            $this->av_success = $_POST["av_success"];
        }
        if(isset($_POST["gain"])){
            $this->gain = $_POST["gain"];
        }
        if(isset($_POST["state"])){
            $this->state = $_POST["state"];
        }
    }

    public function fillFromDB($mysqli){
        if (!($stmt = $mysqli->prepare("SELECT * FROM events WHERE id=?"))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
            throw new Exception("2");
        }
        if (!$stmt->bind_param("i", $this->id) ) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
            throw new Exception("3");
        }
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
            throw new Exception("4");
        }
        $meta = $stmt->result_metadata();
        while ($field = $meta->fetch_field()) {
            $parameters[$field->name] = &$row[$field->name];
        }
        call_user_func_array(array($stmt, 'bind_result'), $parameters);
        while($stmt->fetch()){
            $stmt->close();
            $this->event_id = $parameters["event_id"];
            $this->news_id = $parameters["news_id"];
            $this->t_a = $parameters["t_announced"];
            if (gettype($this->t_a)=="string"){
                $this->t_a = DateTime::createFromFormat('Y-m-d H:i:s', $this->t_a);
            }
            $this->t_r = $parameters["t_real"];
            if (gettype($this->t_r)=="string"){
                $this->t_r = DateTime::createFromFormat('Y-m-d H:i:s', $this->t_r);
            }
            $this->t_u = $parameters["t_update"];
            if (gettype($this->t_u)=="string"){
                $this->t_u = DateTime::createFromFormat('Y-m-d H:i:s', $this->t_u);
            }
            $this->actual = $parameters["actual"];
            $this->previous = $parameters["previous"];
            $this->dv_p_tm5 = $parameters["dv_p_tm5"];
            $this->dv_p_t0 = $parameters["dv_p_t0"];
            $this->prediction = $parameters["prediction"];
            $this->p_proba = $parameters["proba_predict"];
            $this->label = $parameters["label"];
            $this->av_success = $parameters["av_success"];
            $this->gain = $parameters["gain"];
            $this->state = $parameters["state"];
            $this->next_event = $parameters["next_event"];
            return;
        }
    }

    public function tryAddingEventToDB($mysqli){
        if(!$this->isEventInDB($mysqli)){
            $this->addEventToDB($mysqli);
        }
    }

    public function isEventInDB($mysqli){
        $sql = "SELECT id, news_id FROM events WHERE news_id = ".$this->news_id;
        if(!$result = $mysqli->query($sql)){
            die('There was an error running the query [' . $mysqli->error . ']');
        }
        while($row = $result->fetch_assoc()){
            $this->id =  $row['id'];
            return true;
        }
        $result->free();
    }

    public function addEventToDB($mysqli){
        $now = new DateTime("now");
        $now->setTimezone(new DateTimeZone("UTC"));
        $values = "NULL,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?";
        if (!($stmt = $mysqli->prepare("INSERT INTO events VALUES (".$values.")"))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
            throw new Exception("2");
        }
        if (!$stmt->bind_param("iisssiddddididdi", $this->news_id, $this->event_id, $this->t_a->format('Y-m-d H:i:s'), 
            $this->t_r->format('Y-m-d H:i:s'),$now->format("Y-m-d H:i:s"), $this->next_event, $this->actual, $this->previous, 
            $this->dv_p_tm5, $this->dv_p_t0, $this->prediction, $this->p_proba, $this->label, $this->av_success, $this->gain, 
            $this->state) ) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
            throw new Exception("3");
        }
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
            throw new Exception("4");
        }
    }

    public function modifyInDB($mysqli){
        $now = new DateTime("now");
        $now->setTimezone(new DateTimeZone("UTC"));
        if (!($stmt = $mysqli->prepare("UPDATE events SET t_real=?, t_update=?, actual=?,
            dv_p_tm5=?, dv_p_t0=?, prediction=?, proba_predict=?, label=?, gain=?, state=? WHERE id=?"))) {
            echo "Prepare failed: (" . $mysqli->errno . ") " . $mysqli->error;
            throw new Exception("2");
        }
        if (!$stmt->bind_param("ssdddididii", $this->t_r->format("Y-m-d H:i:s"), $now->format("Y-m-d H:i:s"), 
            $this->actual, $this->dv_p_tm5, $this->dv_p_t0, $this->prediction, $this->p_proba, $this->label, 
            $this->gain, $this->state, $this->id)) {
            echo "Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error;
            throw new Exception("3");
        }
        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
            throw new Exception("4");
        }
    }

    static public function getEventFromID(){

    }
    public function simpleDisplay(){
        return "".$this->id.";".$this->news_id.";".$this->event_id.";".$this->t_a->format('d/m/Y H:i:s').
        ";".$this->t_r->format('d/m/Y H:i:s').";".$this->t_u->format('d/m/Y H:i:s').";".
        $this->actual.';'.$this->previous.';'.$this->dv_p_tm5.
        ";".$this->dv_p_t0.";".$this->prediction.";".$this->p_proba.";".$this->label.
        ";".$this->av_success.";".$this->gain.";".$this->state.";".$this->next_event."<br/>";
    }
    public function display(){
        return "ID: ".$this->id."<br/> News ID: ".$this->news_id."<br/> Event ID: ".$this->event_id."<br/>Time announced: ".$this->t_a->format('d/m/Y H:i:s').
        "<br/>Real time announced: ".$this->t_r->format('d/m/Y H:i:s')."<br/><br/>Updated Time: ".$this->t_u->format('d/m/Y H:i:s')."<br/>Actual: ".
        $this->actual.'<br/>Previous:'.$this->previous.'<br/>&#916;p_v_t-5:'.$this->dv_p_tm5.
        "<br/>&#916;p_v_t0: ".$this->dv_p_t0."<br/>Prediction: ".$this->prediction."<br/>Prediction prob.: ".$this->p_proba."<br/>Label: ".$this->label.
        "<br/>Av. Success: ".$this->av_success."<br/>Gain: ".$this->gain."<br/>State: ".$this->state."<br/>";
    }
    private function getEventDisplayStyle(){
        $event_style = "";
        if ($this->state == -1){
            $event_style = 'class="eventunplayable"';
        }
        else if($this->state == 0){
            $event_style = 'class="waitingforevent"';
        }
        else if($this->state == 4){
            $event_style = 'class="tradeinprogress"';
        }
        else if ($this->state == 5){
            if($this->gain >= 0){
                $event_style = 'class="eventwon"';
            }
            else{
                $event_style = 'class="eventfailed"';
            }
        }
        else{
            $event_style = 'class="eventinprogress"';
        }
        return $event_style;
    }
    public function displayAsRow(){
        return "<tr ".$this->getEventDisplayStyle()."><td>".$this->id."</td><td>".$this->news_id."</td><td>".$this->event_id."</td><td>".$this->t_a->format('d/m/Y H:i:s')."</td><td>".
        $this->t_r->format('d/m/Y H:i:s')."</td><td>".$this->t_u->format('d/m/Y H:i:s')."</td><td>".$this->actual.'</td><td>'.
        $this->previous.'</td><td>'.$this->dv_p_tm5."</td><td>".$this->dv_p_t0."</td><td>".$this->prediction."</td><td>".
        $this->p_proba."</td><td>".$this->label."</td><td>".$this->av_success."</td><td>".$this->gain."</td><td>".$this->state."</td></tr>";
    }
    static public function displayHeadersAsRow(){
        return "<tr><td>ID</td><td>News ID</td><td>Event ID</td><td>Time announced</td><td>Real time announced</td>
        <td>Updated time</td><td>Actual</td><td>Previous</td><td>&#916;p_v_t-5</td><td>&#916;p_v_t0</td><td>Prediction</td>
        <td>Prediction prob.</td><td>Label</td><td>Av. Success</td><td>Gain</td><td>State</td></tr>";
    }
    static public function displayShortHeadersAsRow(){
        return "<tr><th>ID</th><th>News ID</th><th>Event ID</th><th>Annoucement Time</th><th>Real Time</th>
        <th>Updated Time</th><th>Actual</th><th>Previous</th><th>&#916;p_v_t-5</th><th>&#916;p_v_t0</th><th>Pred.</th>
        <th>Pred. prob.</th><th>Label</th><th>Av. Success</th><th>Gain</th><th>State</th></tr>";
    }
}
