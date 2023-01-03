<?php
class FlipperController extends DatabaseController
{
    public function affectDataToRow(&$row, $sub_rows){
        
        if(isset($sub_rows['image'])){
            $images = array_values(array_filter($sub_rows['image'], function($item) use ($row){
                return $item->Id_flipper == $row->Id_flipper;
            }));
            /////LIAISON OTM ENTRE IMAGES ET FLIPPER//ON LA RETOURNE SOUS image_list////
            if(isset($images)){
                $row->image_list=$images;
            }
        }
    }
    
}
?>