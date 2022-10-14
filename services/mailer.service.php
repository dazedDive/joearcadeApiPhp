<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailerService {
    public function __construct($profil="main")
    {
        $config=$_ENV['config']->mailer->{$profil};
        $mailer = new PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = $config->host;
        $mailer->Port= $config->port;
        $mailer->SMTPAuth= $config->auth;
        $mailer->SMTPSecure=$config->secure;
        $mailer->Username=$config->user;
        $mailer->Password=$config->pass;
        $this->mailer=$mailer;
    }

    public function send($params){
        $destAdresses = $params['destAdresses'];
        if(!isset($destAdresses) || !is_array($destAdresses) || count($destAdresses) ==0){
            return ["result"=>false, "error"=>"Mail manquant"];
        }
        $fromAddress = $params["fromAddress"] ?? [$this->mailer->Username, ""];
        $replyAddress = $params["replyAddress"] ?? [$this->mailer->Username,""];
        $subject = $params["subject"] ?? "Sujet";
        $body = $params["body"] ?? "Message";
        $altBody = $params["altBody"] ?? "Message non HTML";
        try{
            $this->mailer->setFrom($fromAddress[0],$fromAddress[1]);
            foreach($destAdresses as $destAdresse){
                $this->mailer->addAddress($destAdresse);
            }
            $this->mailer->addReplyTo($replyAddress[0],$fromAddress[1]);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $subject;
            $this->mailer->Body = $body;
            $this->mailer->AltBody = $altBody;
            $result = $this->mailer->send();
            if($result){
                return ["result" => true];
            }
        }
        catch(Exception $e){
            $error = $e;
        }
        return ["result" => false, "error"=>$this->mailer->ErrorInfo . "\r\n" . ($error ?? "")];
    }



}
