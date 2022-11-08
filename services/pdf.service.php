<?php
require_once ("fpdf/fpdf.php");
class Pdf extends FPDF
{
    public function Header()
    {
        $this->Image('image/logo.png',10,6,100);
            
    }

    
}

?>
