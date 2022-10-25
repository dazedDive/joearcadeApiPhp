<?php
class AccountController extends DatabaseController
{
    ////////////////LIAISON OTO ACCOUNT<>CUSTOMER////////////////////////////////
    public function affectDataToRow(&$row, $sub_rows){
        if (isset($sub_rows['customer'])){
            $customers = array_filter ($sub_rows['customer'], function ($item) use ($row){
                return $item->Id_account === $row->Id_account;
            });
            $row->customer = count($customers) ==1 ? array_shift($customers) : null;
            }
    }
    
}
?>