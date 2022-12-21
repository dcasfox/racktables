<?php
require_once 'DALMeterList.php';

class BZMeterList {
    public static function  MeterSelect(){
        return DALMeterSelect::GetMeterInfo();
    }
}