<?php
include('common.php');
include('BZMeterList.php');

class MeterList {
    public static function getMeterlist($args) {
	#return array('test'=> '123');
        $data = BZMeterList::MeterSelect();
        return outSuccess($data,count($data));
        #return OutReturn($data);
    }
}
//MeterSelect::getMeterSelect();
?>
