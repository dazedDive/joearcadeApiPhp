<?php
class FlipperController extends DatabaseController
{
    public function affectDataToRow(&$row, $sub_rows){
        if(isset($sub_rows['image'])){
            $images = array_values(array_filter($sub_rows['image'], function($item) use ($row){
                return $item->Id_flipper == $row->Id_flipper;
            }));
            if(isset($images)){
                $row->image_list=$images;
            }
        }
    }
    
}
?>