<?php
use PHPMailer\PHPMailer\PHPMailer;
define("RECAPTCHA_V3_SECRET_KEY", '6Lf8CdgfAAAAAMDz_RrC_fktLswnar2RUTmLtUlX');
  
if (isset($_POST['email']) && $_POST['email']) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
} else if ($_POST['name'] == ''  OR $_POST['phone'] == ''  OR $_POST['email'] == ''   OR $_POST['message'] == '' ) {
    // set error message and redirect back to form...    
    
    $output = json_encode(['response'=>'error', 'msg' => 'Favor de llenar todos los campos!']);
    die($output);
    exit;
}
else {
    // set error message and redirect back to form...    
    // echo "error";
    $output = json_encode(['response'=>'error', 'msg' => 'Error al enviar!']);
    die($output);
    exit;
}
  
$google_response = $_POST['google_response'];
// $action = $_POST['action'];
  
// call curl to POST request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL,"https://www.google.com/recaptcha/api/siteverify");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('secret' => RECAPTCHA_V3_SECRET_KEY, 'response' => $google_response)));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$arrResponse = json_decode($response, true);
  
// verify the response
if($arrResponse["success"] == '1'  && $arrResponse["score"] >= 0.5) {
    // valid submission
    // go ahead and do necessary stuff 

    if($_POST)
        {

            require_once "PHPMailer/Exception.php";
            require_once "PHPMailer/PHPMailer.php";
            require_once "PHPMailer/SMTP.php";

            $mail = new PHPMailer();


            $your_email = "aguila08ruso@gmail.com";  //Replace with recipient email address

            $to_Email   	= $your_email;

            //check if its an ajax request, exit if not
            if(!isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {

                //exit script outputting json data
                $output = json_encode(
                array(
                    'type'=>'error',
                    'text' => 'Request must come from Ajax'
                ));

                die($output);
            }

            //check $_POST vars are set, exit if any missing
            if(!isset($_POST["name"]) || !isset($_POST["email"]) || !isset($_POST["phone"]) || !isset($_POST["message"]))
            {
                $output = json_encode(['response'=>'error', 'msg' => 'Nombre muy corto o esta vacio!']);
                die($output);
            }

            //Sanitize input data using PHP filter_var().
            $user_Name        = filter_var($_POST["name"], FILTER_SANITIZE_STRING);
            $user_Email       = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
            $user_Phone       = filter_var($_POST["phone"], FILTER_SANITIZE_STRING);    
            $user_Message     = filter_var($_POST["message"], FILTER_SANITIZE_STRING);

            //additional php validation
            if(strlen($user_Name)<2) // If length is less than 2 it will throw an HTTP error.
            {
                $output = json_encode(['response'=>'error', 'msg' => 'Nombre muy corto o esta vacio!']);
                die($output);
            }
            if(!filter_var($user_Email, FILTER_VALIDATE_EMAIL)) //email validation
            {
                $output = json_encode(['response'=>'error', 'msg' => 'Por favor ingrese un correo valido!']);
                die($output);
            }

            if(strlen($user_Message)<5) //check emtpy message
            {
                $output = json_encode(['response'=>'error', 'msg' => 'Su mensaje es muy corto, favor de ingresar más comentarios.']);
                die($output);
            }


            //Server settings
            $mail->isSMTP();                                            // Send using SMTP
            $mail->Host       = 'vmasideas.agency';                    // Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $mail->Username   = 'lachula.noreplay@vmasideas.agency';//     'contact@hotelquintaeden.com';                // SMTP username
            $mail->Password   = '8ul2!CVljGc^'; //    '##,FPy74a3VU';                     // SMTP password
            $mail->SMTPSecure = 'ssl';         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
            $mail->Port       = 465;                                    // TCP port to connect to
            //    $mail->SMTPDebug  = 4;
            $mail->protocol = 'protocol';
            $mail->smtp_port = 587;
        
            //Recipients
            $mail->setFrom($user_Email,$user_Name);
            $mail->addAddress($your_email, 'Corazon de Jaguar');     // Add a recipient
            $mail->addReplyTo($your_email, 'Contacto');


            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = "Contacto de Usuario";
            $mail->Body  = "<h4 style='text-align: center;padding: 25px 15px;background-color: #0c6c9e;color: #FFFFFF;font-size:16px;width:90%;border-radius: 10px;'>Existe un nuevo mensaje de contacto en el sitio.</h4><br><br>";


            $mail->Body .= utf8_decode("<strong>Nombre: </strong>". $user_Name ."<br>");
            $mail->Body .= utf8_decode("<strong>Correo: </strong>". $user_Email ."<br>");
            $mail->Body .= utf8_decode("<strong>Teléfono: </strong>". $user_Phone ."<br>");    
            $mail->Body .= utf8_decode("<strong>Mensaje: </strong>". $user_Message ."<br>");

            $mail->AltBody = utf8_decode('Existe un nuevo mensaje del sitio Corazon de Jaguar; nombre: '.$user_Name.'correo: '.$user_Email.'Teléfono: '.$user_Phone.'Mensaje: '.$user_Message);

            if(!$mail->send())
            {
                $output = json_encode(['response'=>'error', 'msg' => 'No se puede enviar el correo! Por favor intente más tarde.']);
                die($output);
            }else{
                $output = json_encode(['response'=>'success', 'msg' => 'Hola '.$user_Name .' Gracias por tus comentarios.']);
                die($output);
            }

        }


    echo json_encode(['response' => 'success','msg'=>'Exitoso']);
    exit();

} else {
    // spam submission
    // show error message

    echo json_encode(['response'=>'error','msg'=>'Google reCaptcha Error']);
    exit();
}