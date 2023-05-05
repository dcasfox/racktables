<?php
include('common.php');
include('BZMeterList.php');

class MeterList {

    public static function getTagsDropDownSelect($args) {
        $data = BZMeterList::TagsDropDownSelect($args);    
        return outSuccess($data,count($data));
    } 
    public static function getMeterlist($args) {
        $data = BZMeterList::MeterSelect($args);    
        return outSuccess($data,count($data));
    }
    public static function getPowerMeteritems($args) {        
        $data = BZMeterList::PowerMeteritems($args);  
        return outSuccess($data,count($data));
    }
}

?>
