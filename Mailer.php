<?php

namespace App;

class Mailer
{

    
    const MAILER_FROM_ADDRESS = "";
    const MAILER_FROM_NAME = "";

    private $errorMessage = "";
   
     /**
     * @param $subject
     * @param $body: The content of the e-mail.
     * @param $addresses:  Accepts an associative array with elements in the following format: ( "example@email.com" => "name" )
     * @throws \Exception
     */
    public function send($subject, $body, $addresses)
    {
        
        //Set up e-mail header
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom(self::MAILER_FROM_ADDRESS);
        $email->setSubject($subject);
        $email->addTos($addresses);
        
        $email->addContent("text/html", $body);

        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));

        //Try sending e-mail
        try {
            $sendgrid->send($email);
            return true;
        } catch (Exception $e) {
            $errorMessage = $e;
            return false;
        }
    }

    //When e-mail fails sending, this function returns the exact error
    public function getErrorMessage(){
        return $errorMessage;
    }

    //Loads E-mail template and returns the body
    public function loadTemplate($template, $emailVars)
    {
        $body = File::get(storage_path("PATH_TO_TEMPLATE" . $template));

        if (isset($emailVars)) {

            foreach ($emailVars as $key => $value) {

                $body = preg_replace
                    (
                    "/{{" . $key . "}}/"
                    , $value
                    , $body
                );

            }

        }

        return $body;
    }
}
