<?php
class DALmeterselect {
    function fixArrayKey(&$arr)
    {
        $arr = array_combine(
            array_map(
                function ($str) { //strtolower
                    return str_replace(" ", "", preg_replace('/[^A-Za-z0-9\-]/', '', strtolower($str)));
                },
                array_keys($arr)
            ),
            array_values($arr)
        );

       // foreach ($arr as $key => $val) {
       //     if (is_array($val)) {
       //        DALmeterselect::fixArrayKey($arr[$key]);
       //     }
       // }
    }

    function get_host_tag($token){
       //where tt.tag = '_d7af5a388680ccba9161e6d8a810d98470a26591e959fb209fe07bd67769476b'
        $datas = array();
        $sql = " 

        SELECT Obj.name,ht.tag ,ht.value ,tt.tag  as token FROM `Object` Obj 
        LEFT JOIN TagStorage ts on ts.entity_id = Obj.id
        LEFT JOIN TagTree tt on  tt.id = ts.tag_id
        LEFT JOIN zabbix.hosts h on Obj.name = h.name
        LEFT JOIN zabbix.host_tag ht on ht.hostid = h.hostid 
        LEFT JOIN Dictionary d on d.dict_key =Obj.objtype_id 
        where  ht.value is not null
        
        " ;
        //$result = mysqli_query($link,$sql);
        $result = usePreparedSelectBlade($sql);
        
        if ($result) {
            //if (mysqli_num_rows($result)>0) {
                while ($row = $result->fetch(PDO::FETCH_ASSOC)){
                    if ($row['token']==$token) {
                        $datas[$row['name']][$row['tag']] = $row['value'];
                    }
                }

        }
          //  mysqli_free_result($result);

        if(!empty($datas)){
            return $datas;
        }
        else{
            $datas['message']['data'] ='nodata';
            #return "Nodata";
            return $datas;
        }

    }


    function get_common_Data(){

        $datas = array();
        $sql = " SELECT objtype_id,name,asset_no,label,dict_value FROM `Object` Obj left Join Dictionary d on d.dict_key =Obj.objtype_id where dict_value = 'Power Meter'" ;
        //$result = mysqli_query($link,$sql);
        $result = usePreparedSelectBlade($sql);

        if ($result) {

            //if (mysqli_num_rows($result)>0) {
                while ($row = $result->fetch(PDO::FETCH_ASSOC)){
                //while ($row = mysqli_fetch_assoc($result)) {
                    $datas[$row['name']] = $row;
                }
           // }

        }
          //  mysqli_free_result($result);

        if(!empty($datas)){
            return $datas;
        }
        else{
            return "Nodata";
            return $datas['message'] = 'Nodata' ;
        }

    }

    function get_extend_Data(){
        
        $datas = array();
        $tmp_array_ =   array();
        
        $sql = " SELECT name,Attrrbute_Name,string_value FROM (
            SELECT Obj.name as name,attr_col.name as Attrrbute_Name,attr.string_value,d.dict_value as dict_value   FROM `Object` Obj
                LEFT JOIN Dictionary d on d.dict_key =Obj.objtype_id 
                LEFT JOIN AttributeValue attr on attr.object_id  = Obj.id  
                LEFT JOIN `Attribute` attr_col on attr_col.id  = attr.attr_id  
                WHERE attr.string_value  is not null
            UNION ALL
            SELECT Obj.name as Object_Name,attr_col.name as Attrrbute_Name,d.dict_value as string_value ,d2.dict_value as dict_value FROM `Object` Obj
                LEFT JOIN AttributeValue attr on attr.object_id  = Obj.id  
                LEFT JOIN `Attribute` attr_col on attr_col.id  = attr.attr_id  
                LEFT JOIN Dictionary d on d.dict_key = attr.uint_value 
                LEFT JOIN Dictionary d2 on d2.dict_key =Obj.objtype_id 
                WHERE d.dict_value is not null 
            )
            AS tbl   ";

        //$result = mysqli_query($link,$sql);
        $result = usePreparedSelectBlade($sql);

        if ($result) {
            //if (mysqli_num_rows($result)>0) {
                while ($row = $result->fetch(PDO::FETCH_ASSOC)){
                //while ($row = mysqli_fetch_assoc($result)) {
                    $tmp_array = array();
                    $tmp_array = array(
                        $row['Attrrbute_Name'] => $row['string_value']
                    );
                    if (array_key_exists($row['name'],$tmp_array_)){
                        $tmp_array_[$row['name']]  = array_merge($tmp_array_[$row['name']],$tmp_array);
                    }
                    else {
                        $tmp_array_[$row['name']] = $tmp_array;
                    }
                }
                
            //}
        }
           // mysqli_free_result($result);
            return $tmp_array_;
    }

    function up_and_down_stream(){
        
        $tmp_array_up = array();
        $tmp_array_down =   array();
        
        $sql = "  SELECT Obj.name as item,p1.name as port_link,Obj2.name  as down_stream FROM  Link l
        LEFT JOIN Port p1 on p1.id =l.portb
        LEFT JOIN Port p2 on p2.id =l.porta
        LEFT JOIN PortOuterInterface POI on POI.id  = p1.`type`
        LEFT JOIN `Object` Obj on p1.object_id = Obj.id
        LEFT JOIN `Object` Obj2 on p2.object_id = Obj2.id
        LEFT Join Dictionary d on d.dict_key =Obj.objtype_id
        WHERE POI.oif_name like 'Electric Cable up'
        ";

        //$result = mysqli_query($link,$sql);
        $result = usePreparedSelectBlade($sql);

        if ($result) {
            //if (mysqli_num_rows($result)>0) {
                while ($row = $result->fetch(PDO::FETCH_ASSOC)){
                //while ($row = mysqli_fetch_assoc($result)) {
                
    
                    if (array_key_exists($row['item'],$tmp_array_up)){
                        $tmp_array_up[$row['item']]  = array_merge($tmp_array_up[$row['item']],array($row['down_stream']));
                    }
                    else {
                        $tmp_array_up[$row['item']] = array($row['down_stream']);
                    }

                    if (array_key_exists($row['down_stream'],$tmp_array_down)){
                        $tmp_array_down[$row['down_stream']]  = array_merge($tmp_array_down[$row['down_stream']],array($row['item']));
                    }
                    else {
                        $tmp_array_down[$row['down_stream']] = array($row['item']);
                    }

               // }
                
            }
        }
            //mysqli_free_result($result);
            return [$tmp_array_up,$tmp_array_down];
    }

    function down_stream(){
        
        $tmp_array_up = array();
        $tmp_array_down =   array();
        
        $sql = " 

         
        SELECT * from  (
            SELECT Obj.name as item,p1.name as port_link,Obj2.name as down_stream FROM  Link l
           LEFT JOIN Port p1 on p1.id =l.portb
           LEFT JOIN Port p2 on p2.id =l.porta
           LEFT JOIN PortOuterInterface POI on POI.id  = p1.`type`
           
           LEFT JOIN `Object` Obj on p1.object_id = Obj.id
           LEFT JOIN `Object` Obj2 on p2.object_id = Obj2.id
           LEFT Join Dictionary d on d.dict_key =Obj.objtype_id
           WHERE POI.oif_name like  'Electric Cable Down'
           
           
           union  
           
            SELECT Obj2.name as item,p1.name as port_link,Obj.name as down_stream FROM  Link l
           LEFT JOIN Port p1 on p1.id =l.portb
           LEFT JOIN Port p2 on p2.id =l.porta
           LEFT JOIN PortOuterInterface POI on POI.id  = p2.`type`
           
           LEFT JOIN `Object` Obj on p1.object_id = Obj.id
           LEFT JOIN `Object` Obj2 on p2.object_id = Obj2.id
           LEFT Join Dictionary d on d.dict_key =Obj.objtype_id
           WHERE POI.oif_name like  'Electric Cable Down'
           
           )
           
           AS tbl


        ";

        //$result = mysqli_query($link,$sql);
        $result = usePreparedSelectBlade($sql);

        if ($result) {
            //if (mysqli_num_rows($result)>0) {
                while ($row = $result->fetch(PDO::FETCH_ASSOC)){
                //while ($row = mysqli_fetch_assoc($result)) {
                
                    if (array_key_exists($row['down_stream'],$tmp_array_down)){
                        $tmp_array_down[$row['down_stream']]  = array_merge($tmp_array_down[$row['down_stream']],array($row['item']));
                    }
                    else {
                        $tmp_array_down[$row['down_stream']] = array($row['item']);
                    }
               // }
                
            }
        }
            //mysqli_free_result($result);
            return $tmp_array_down;
    }

    function up_stream(){
        
        $tmp_array_up = array();
        $tmp_array_down =   array();
        
        $sql = "  
         
        SELECT * from  (
            SELECT Obj.name as item,p1.name as port_link,Obj2.name as down_stream FROM  Link l
           LEFT JOIN Port p1 on p1.id =l.portb
           LEFT JOIN Port p2 on p2.id =l.porta
           LEFT JOIN PortOuterInterface POI on POI.id  = p2.`type`
           
           LEFT JOIN `Object` Obj on p1.object_id = Obj.id
           LEFT JOIN `Object` Obj2 on p2.object_id = Obj2.id
           LEFT Join Dictionary d on d.dict_key =Obj.objtype_id
           WHERE POI.oif_name like  'Electric Cable Up'
           
           
           union  
           
            SELECT Obj2.name as item,p1.name as port_link,Obj.name as down_stream FROM  Link l
           LEFT JOIN Port p1 on p1.id =l.portb
           LEFT JOIN Port p2 on p2.id =l.porta
           LEFT JOIN PortOuterInterface POI on POI.id  = p1.`type`
           
           LEFT JOIN `Object` Obj on p1.object_id = Obj.id
           LEFT JOIN `Object` Obj2 on p2.object_id = Obj2.id
           LEFT Join Dictionary d on d.dict_key =Obj.objtype_id
           WHERE POI.oif_name like  'Electric Cable Up'
           
           )
           
           AS tbl

        
        ";

        //$result = mysqli_query($link,$sql);
        $result = usePreparedSelectBlade($sql);

        if ($result) {
            //if (mysqli_num_rows($result)>0) {
                while ($row = $result->fetch(PDO::FETCH_ASSOC)){
                //while ($row = mysqli_fetch_assoc($result)) {
                
            
                    if (array_key_exists($row['item'],$tmp_array_up)){
                        $tmp_array_up[$row['item']]  = array_merge($tmp_array_up[$row['item']],array($row['down_stream']));
                    }
                    else {
                        $tmp_array_up[$row['item']] = array($row['down_stream']);
                    }
                
            }
        }
            //mysqli_free_result($result);
            return $tmp_array_up;
    }





    function GetMeterInfo($token='_d7af5a388680ccba9161e6d8a810d98470a26591e959fb209fe07bd67769476b',$filter_tag='Active_Energy'){
        //require_once 'db_connect.php';
        

        $all = array();
        $item_host_map = array();
        $datas_common = DALmeterselect::get_common_Data();
        $datas = DALmeterselect::get_extend_Data();
       
        $host_tag_map = DALmeterselect::get_host_tag($token);
        $host_tag_map;
        $down_stream_ =DALmeterselect::down_stream();
        #return $down_stream_;
        $up_stream_ =DALmeterselect::up_stream();
        #return $up_stream_;

        $datas_stream = DALmeterselect::up_and_down_stream();
        #return 1;

        foreach ($datas_common as $key => $value){
            $arr = array();
            $arr_up = array();
            if (array_key_exists($key,$down_stream_)){
                //
                $arr = array(
                'Downstream'  =>  $down_stream_[$key]
                );
            }
            else{
                $arr = array(
                    'Downstream'  => []
                );
            }

            if (array_key_exists($key,$up_stream_)){
                $arr_up = array(
                'Upstream'  =>  $up_stream_[$key]
                );
            }
            else{
                $arr_up = array(
                    'Upstream'  => []
                );
            }

            $a = array_map('trim', array_keys($datas[$key]));
            $b = array_map('trim', $datas[$key]);
            $datas[$key] = array_combine($a, $b);
            //return $datas[$key];
            //DALmeterselect::fixArrayKey($datas[$key]);


            if (array_key_exists('Metrics Config',$datas[$key])){
                $metrics = json_decode($datas[$key]['Metrics Config'],true);
                $datas[$key]['Metrics Config'] = $metrics;
                #unset($datas[$key]['Metrics Config']);
                #return $metrics;
                #return $metrics['Power']['Active_Energy'];
                if  (array_key_exists($filter_tag,$metrics['Power'])){
                    array_push($item_host_map,array('host'=>$key,'item'=> $metrics['Power'][$filter_tag][0]));
                    $mag[$key]=array(
                        'Name' =>$datas_common[$key]['name'],
                        'Description' =>$datas[$key]['Description'],
                        'Hosttag' =>$host_tag_map[$key],
                        'AssetAtrributes' =>$datas[$key]
                    );

                    unset($mag[$key]['tags']['Description']);
                    
                    //DALmeterselect::fixArrayKey($datas[$key]);
                    //DALmeterselect::fixArrayKey($datas[$key]);

                    $merge  = array_merge($mag[$key],$arr_up,$arr);
                    DALmeterselect::fixArrayKey($merge);
                   
                    array_push($all,$merge);
               }
                //return array_keys($metrics);
            
            }
          

        
        }
        //DALmeterselect::fixArrayKey($all);
        #DALmeterselect::fixArrayKey($item_host_map);

        $outputarray = array('Config' => $all,'ItemName' =>$item_host_map);
        DALmeterselect::fixArrayKey($outputarray);
        //return json_encode($all);
        return $outputarray;
        //mysqli_close($link);
    }
    
}

?>

