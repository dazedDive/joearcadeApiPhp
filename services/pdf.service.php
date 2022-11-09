<?php
require_once ("fpdf/fpdf.php");
class Pdf extends FPDF
{
    public function Header()
    {
        $this->Image('image/logo.png',10,6,100);
            
    }
    function Footer()
{
    
    $this->SetY(-25);
    $this->SetFont('Arial','I',8);
    $this->Cell(0,10,'Joe Arcade N°Siret : 123 568 941 00056 / www.joearcade.fr / contact@joearcade.fr',0,0,'C');
}
    
}

?>
