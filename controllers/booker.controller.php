<?php
require_once 'fpdf/fpdf.php';
require_once 'vendor/autoload.php';

class BookerController {
    public function __construct($params)
  {
    $id = array_shift($params);
    $this->action = null;
   
    $request_body = file_get_contents('php://input');
    $this->body = $request_body ? json_decode($request_body, true) : null;
    $this->table = lcfirst(str_replace("Controller","",get_called_class()));
    
    if ($_SERVER['REQUEST_METHOD'] == "POST" && ($id == "pay")){
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
      ////////////////////recupération du body de la requete et destructurisation///////////
            $headers = apache_request_headers();

            if(isset($headers["Authorization"])){
            $token = $headers["Authorization"];}
            else{
              return ["result"=>false,"message"=>"erreur lors de votre reservation..Veuillez réessayer"];
              die;
            }

            $Id_booking=$this->body['Id_booking'];
            $Id_flipper=$this->body['Id_flipper'];
            $Id_customer=$this->body['Id_customer'];
            $firstName=$this->body['customerFirstName'];
            $lastName=$this->body['customerLastName'];
            $tel=$this->body['customerTel'];
            $mail=$this->body['customerMail'];
            $factureAdress=$this->body['adressFacture'];
            $flipperName = $this->body['flipperName'];
            $weekEnd=$this->body['weekend_location'];
            $month = $this->body['month_location'];
            $year = $this->body['year_location'];
            $weekEnd=$this->body['weekend_location'];
            $deliveryAddress=$this->body['adress_delivery'];
            $cpAdresse = $this->body['cp_delivery'];
            $cityAdresse = $this->body['city_delivery'];
            $flipperPrice = $this->body['flipper_price'];
            $deliveryPrice = $this->body['transport_price'];
            $timeOfRent = $this->body['time_of_rent'];
            $total = $this->body['total_price'];
            $tva = 20;
            ////////////////////////////////inscription de la commande en db/////////////////////
            $dbs = new DatabaseService('booking');
            $findBook = $dbs->selectWhere("Id_booking = ? AND is_deleted = ? AND is_reserved= ?", [(int)$Id_booking,0,0]);

            if(count($findBook) == 0){
              return ["result"=>false,"message"=>"erreur lors de votre reservation..Veuillez réessayer"];
              die;
            }

            $body = ['Id_booking'=>$Id_booking,
                    'Id_flipper'=>$Id_flipper,
                    'Id_customer'=>$Id_customer,
                    'time_of_rent'=>$timeOfRent,
                    'flipper_price'=>$flipperPrice,
                    'transport_price'=>$deliveryPrice,
                    'total_price'=>$total,
                    'adresse_delivery'=>$deliveryAddress,
                    'cp_delivery'=>$cpAdresse,
                    'city_of_delivery'=>$cityAdresse,
                    'is_reserved'=>1,
                    'is_payed'=>1
                  ];
            $writeBook = $dbs->updateOne($body);

            if(!isset($writeBook)){
              return ["result"=>false,"message"=>"erreur lors de votre reservation..Veuillez réessayer"];
              die;
            }
          ////////////////////CREATION DE LA FACTURE PDF/////////////////
            require_once('services/pdf.service.php');
            $pdf = new Pdf();
            define('EURO', chr(128));
            $pdf->AddPage();
            $pdf->SetFont('Arial','B',9);
            $pdf->Ln(30);
            $pdf->Line(10,40,200,40);
            $pdf->MultiCell(0,10,
            ' facture pour la commande numero : 000'.$Id_booking."\n".
            "Client : ".$firstName." ".$lastName."\n"
            .utf8_decode("téléphone : ").$tel." email : ".$mail."\n".
            "Adresse de facturation : ".$factureAdress."\n"            
            ,2,"C");
            $pdf->Ln(10);
            $pdf->Line(10,80,200,80);
            $pdf->MultiCell(0,10,
            "VOTRE COMMANDE :"."\n".utf8_decode("modèle de flipper : ").$flipperName."\n"
            ."pour la date du ".$weekEnd."/".$month."/".$year."\n".
            "Adresse de livraison : ".utf8_decode($deliveryAddress).",".$cpAdresse." ".$cityAdresse             
            ,2,"C");
            $pdf->Ln(10);
            $pdf->Line(10,140,200,140);
            $pdf->MultiCell(0,12,
            'Prix de la location : '.$flipperPrice." EUROS / TTC\n"
            .utf8_decode("Durée de Location : ").utf8_decode($timeOfRent)."\n".
            "Prix de la livraison : ".$deliveryPrice." EUROS / TTC\n".
            "Total : ".$total." EUROS / TTC, tva a : " .$tva."%"            
            ,2,"C");
            $pdf->Output('F','invoice/commande_numero'.$Id_booking.'.pdf',true);
///////////////////////////////////////////////////////////////////////////////////////////
            require_once('services/mailer.service.php');
            $ms = new MailerService();
            $mailParams = [
              "fromAddress"=>["monCompte@joe-arcade.fr", "monCompte joe-arcade.fr"],
              "destAdresses"=>[$mail],
              "replyAdress"=>["monCompte@joe-arcade.fr", "monCompte joe-arcade.fr"],
              "subject"=>"Merci pour votre commande !",
              "body"=>"Votre commande à bien été enregistré, vous trouverez votre facture PDF en pièce jointe.",
              "altBody"=>"Joe Arcade ! La location de Flipper facile et fun ! ",
              "attachement"=>'invoice/commande_numero'.$Id_booking.'.pdf'
            ];
            $ms->send($mailParams);
            return ["result"=>true,"message"=>"Jackpot !Merci votre commande à bien été enregistré, votre facture
            a était envoyé sur votre boite mail."];
            }
}