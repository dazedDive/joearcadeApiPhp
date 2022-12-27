<?php
class BookingController extends DatabaseController
{
    public function affectDataToRow(&$row, $sub_rows){

        if (isset($sub_rows["customer"])){
            $customers = array_filter ($sub_rows['customer'], function ($item) use ($row){
                return $item->Id_customer === $row->Id_customer;
            });
            
            $row->customer = count($customers) ==1 ? array_shift($customers) : null;
            }

        if (isset($sub_rows["flipper"])){
            $flippers = array_filter ($sub_rows['flipper'], function ($item) use ($row){
                return $item->Id_flipper === $row->Id_flipper;
            });
            
            $row->flipper = count($flippers) == 1 ? array_shift($flippers) : null;
        }
    }
    
}
?>