<?php
require_once 'fpdf/fpdf.php';


require_once 'vendor/autoload.php';

class BookerController {
    public function __construct($params)
  {
    $id = array_shift($params);
    $this->action=null;
   
    $request_body = file_get_contents('php://input');
    $this->body = $request_body ? json_decode($request_body, true) : null;
    $this->table = lcfirst(str_replace("Controller","",get_called_class()));
    
    if ($_SERVER['REQUEST_METHOD'] == "POST" && ($id=="pay")){
        $this->action = $this->pay();
      }
    if ($_SERVER['REQUEST_METHOD'] == "POST" && (isset ($id))){
        $this->action = $this->complete();
    }
}

    function pay(){
        return "coder le paiement";
    }

    function complete(){
            $headers = apache_request_headers();
            if(isset($headers["Authorization"])){
            $token=$headers["Authorization"];}
            $Id_booking=$this->body['IdBooking'];
            $firstName=$this->body['customerFirstName'];
            $lastName=$this->body['customerLastName'];
            $tel=$this->body['customerTel'];
            $mail=$this->body['customerMail'];
            $factureAdress=$this->body['adressFacture'];
            $destAdresse = $this->body['adressFacture'];

            require_once('services/pdf.service.php');
            $pdf= new Pdf();
            $pdf->AddPage();
            $pdf->SetFont('Arial','B',10);
            $pdf->Header();
            $pdf->Ln(30);
            $pdf->MultiCell(0,10,
            'commande numero : '.$Id_booking."\n".$firstName." ".$lastName."\n"
            ."telephone : ".$tel." email : ".$mail."\n".
            "Adresse de facturation : ".$factureAdress."\n"            
            ,2,"C");
            $pdf->Ln(15);
            $pdf->Output('F','invoice/commande_numero'.$Id_booking.'.pdf',true);
            require_once('services/mailer.service.php');
            $ms = new MailerService();
            $mailParams = [
              "fromAddress"=>["monCompte@joe-arcade.fr", "monCompte joe-arcade.fr"],
              "destAdresses"=>[$destAdresse],
              "replyAdress"=>["monCompte@joe-arcade.fr", "monCompte joe-arcade.fr"],
              "subject"=>"Merci pour votre commande",
              "body"=>"Votre commande à bien été enregistré",
              "altBody"=>"Joe Arcade ! La location de Flipper facile et fun ! ",
              "attachement"=>'invoice/commande_numero'.$Id_booking.'.pdf'
            ];
            $ms->send($mailParams);
            }
}