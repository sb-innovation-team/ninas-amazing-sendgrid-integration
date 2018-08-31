<?php

namespace App;

use Illuminate\Support\Facades\File;

class Mailer
{

    const MAILER_FROM_ADDRESS = "someone@somewhere.nl";
    const MAILER_FROM_NAME = "Someone special";



    /**
     * @param $subject
     * @param $body: The content of the e-mail.
     * @param $attachments : Accepts an array of attachment(s)
     * @param $addresses:  Accepts an associative array with elements in the following format: ( "example@email.com" => "name" )
     * @throws \Exception
     */
    public function send($subject, $body, $addresses, $attachments = null)
    {

        //Set up e-mail header
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom(self::MAILER_FROM_ADDRESS);
        $email->setSubject($subject);

        if(is_array($addresses)){
            $email->addTos($addresses);
        }else{
            $email->addTo($addresses);
        }

        $email->addContent("text/html", $body);

        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));

        //Add attachment
        if ($attachments) {
            if (is_array($attachments)) {

                foreach ($attachments as $file) {

                    $allowedExt = array("txt", "pdf", "docx", "jpg", "png");

                    if ($file) {

                        //Check if allowed extension
                        $isValidExtension = true;

                        foreach ($allowedExt as $ext) {
                            if ($ext !== $file->getClientOriginalExtension()) {
                                $isValidExtension = false;
                            }
                        }

                        if ($isValidExtension !== true) {
                            return false;
                        }
                        $email = $this->addAttachment($file, $email);
                    }

                }
            } else {
                $email = $this->addAttachment($attachments, $email);
            }
        }

        try {
            $sendgrid->send($email);
            return true;
        } catch (Exception $e) {
            $errorMessage = $e;
            return false;
        }

    }

    private function addAttachment($file, $email)
    {

        $file_type = $file->getClientOriginalExtension();
        $file_encoded = file_get_contents($file);
        $file_mimeType = $file->getClientMimeType();
        $Rndm_fileName = $this->generateRandomString();

        $email->addAttachment(
            $file_encoded,
            $file_mimeType,
            $Rndm_fileName . "." . $file_type,
            "attachment"
        );
        return $email;
    }

    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function loadTemplate($template, $emailVars)
    {

        $body = File::get(storage_path("app/email_templates/" . $template));

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
