<?php
class TestmailController {
    public function __construct()
    {
        $this->action=null;
        if($_SERVER['REQUEST_METHOD']=="GET"){
            $this->action=$this->testMail();
        }

    }
    function testMail(){
        require_once('services/mailler.service.php');
        $ms = new MailerService();
        $mailParams = [
            "fromAddress"=>["confirmation@joe-arcade.fr", "confirmation@joe-arcade.fr"],
            "destAdresses"=>["dazed_dive@hotmail.fr"],
            "replyAdress"=>["confirmation@joe-arcade.fr", "confirmation@joe-arcade.fr"],
            "subject"=>"Mail de Test Joe Arcade",
            "body"=>"this is the html message",
            "altBody"=>"this is the no HTML message"
        ];
        return $ms->send($mailParams);
    }
}
