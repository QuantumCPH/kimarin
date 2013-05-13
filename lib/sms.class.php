<?php

require_once(sfConfig::get('sf_lib_dir') . '/smsCharacterReplacement.php');

/**
 * Description of company_employe_activation
 *
 * @author baran
 */
class CARBORDFISH_SMS {

    //put your code here

    private static $S = 'H';
    private static $UN = 'zapna1';
    private static $P = 'Zapna2010';
    private static $SA = 'Kimarin';
    private static $ST = 5;

    /*
     * Description of Send
     *
     * @param $mobilenumber is the mobile number leading with country code;
     * @smsText is for the text that will be sent.
     * @param $Sender will be the sender name of the SMS;
     */

    public static function Send($mobileNumber, $smsText, $senderName=null, $smsType=null,$transaction_id=null) {
        if ($senderName == null)
            $senderName = self::$SA;
            if ($smsType == null)
            $smsType = 1;
        $data = array(
            'S' => self::$S,
            'UN' => self::$UN,
            'P' => self::$P,
            'DA' => $mobileNumber,
            'SA' => $senderName,
            'M' => $smsText,
            'ST' => self::$ST
        );
        $queryString = http_build_query($data, '', '&');
        $queryString = smsCharacter::smsCharacterReplacement($queryString);
     
    
        $res = file_get_contents('http://sms1.cardboardfish.com:9001/HTTPSMS?' . $queryString);
        sleep(0.15);

        $smsLog = new SmsLog();
        $smsLog->setMessage($smsText);
        $smsLog->setStatus($res);
         $smsLog->setSmsType($smsType);
        $smsLog->setSenderName($senderName);
        $smsLog->setMobileNumber($mobileNumber);
        $smsLog->setApiName('Cardbordfish');
        $smsLog->setTransactionId($transaction_id);
        $smsLog->save();
        if (substr($res, 0, 2) == 'OK')
            return true;
        else
            return false;
    }

}

class SMSNU {

    //put your code here

    private static $main = '13rkha84';
    private static $id = 'Kimarin';
    /*
     * Description of Send
     *
     * @param $mobilenumber is the mobile number leading with country code;
     * @smsText is for the text that will be sent.
     * @param $Sender will be the sender name of the SMS;
     */

    public static function Send($mobileNumber, $smsText, $senderName=null, $smsType=null,$transaction_id=null) {
        if ($senderName == null)
            $senderName = self::$id;
        if ($smsType == null)
            $smsType = 1;
        $data = array(
            'main' => self::$main,
            'til' => $mobileNumber,
            'id' => $senderName,
            'msgtxt' => $smsText
        );
        $message = "";
        $queryString = http_build_query($data, '', '&');
        // $queryString = smsCharacter::smsCharacterReplacement($queryString);
        $res = file_get_contents('http://smsnu.dk/sendsms?' . $queryString);
        sleep(0.15);
        if (!$res) {
            return false;
        }
        $smsLog = new SmsLog();
        $smsLog->setMessage($smsText);
        $smsLog->setStatus($res);

        $smsLog->setSmsType($smsType);
        $smsLog->setSenderName($senderName);
        $smsLog->setMobileNumber($mobileNumber);
        $smsLog->save();
        if (substr($res, 10, 2) == 'OK') {
            return true;
        } else {
            $message="SMS not sent to this mobile numberc On Kimarin <br/>Mobile number =" . $mobileNumber . "<br/> Message is =" . $smsText . "<br/> and Time is " . $smsLog->getCreatedAt();
            emailLib::smsNotSentEmail($message);
            return false;
        }
    }

}
class ROUTE_API_Regular {

    private static $id = 'Kimarin';

    public static function Send($mobileNumber, $smsText, $senderName = null, $smsType = null,$transaction_id=null) {

        if ($senderName == null)
            $senderName = self::$id;
        if ($smsType == null)
            $smsType = 1;
        $data1 = array(
            'username' => 'zapna',
            'password' => 'bc366nf',
            'dlr' => '1',
            'destination' => $mobileNumber,
            'source' => $senderName,
            'message' => $smsText,
            'type' => '0'
        );
        $queryString = http_build_query($data1, '', '&');
        $queryString = smsCharacter::smsCharacterReplacementReverse($queryString);
        $res = file_get_contents('http://smsplus3.routesms.com:8080/bulksms/bulksms?' . $queryString);
        $smsLog = new SmsLog();
        $smsLog->setMessage($smsText);
        $smsLog->setStatus($res);
        $smsLog->setSmsType($smsType);
        $smsLog->setSenderName($senderName);
        $smsLog->setMobileNumber($mobileNumber);
        $smsLog->setApiName('Route API Regular');
        $smsLog->setTransactionId($transaction_id);
        $smsLog->save();
        if (substr($res, 0, 4) == 1701) {
            return true;
        } else {
            $message="SMS not sent via ROUTE_API_Regular to this mobile numberc On Kimarin <br/>Mobile number =" . $mobileNumber . "<br/> Message is =" . $smsText . "<br/> Response from API =" . $res;
            emailLib::smsNotSentEmail($message);
            return false;
        }
    }

}

class ROUTE_API_Premium {

    //put your code here

    private static $username = 'zapna1';
    private static $password = 'lghanymb';
    private static $source = 'Kimarin';
    private static $dlr = 1;
    private static $type = 0;

    public static function Send($mobileNumber, $smsText, $senderName = null, $smsType = null,$transaction_id=null) {

        $message = "";
        if ($senderName == null)
            $senderName = self::$source;
        if ($smsType == null)
            $smsType = 1;
        $data = array(
            'username' => self::$username,
            'password' => self::$password,
            'dlr' => self::$dlr,
            'destination' => $mobileNumber,
            'source' => $senderName,
            'message' => $smsText,
            'type' => self::$type
        );
        $queryString = http_build_query($data, '', '&');
        $queryString = smsCharacter::smsCharacterReplacementReverse($queryString);
        $res = file_get_contents('http://smpp5.routesms.com:8080/bulksms/sendsms?' . $queryString);
        // sleep(0.25);

        if (substr($res, 0, 4) == 1701) {

            $smsLog = new SmsLog();
            $smsLog->setMessage($smsText);
            $smsLog->setStatus($res);
            $smsLog->setSmsType($smsType);
            $smsLog->setSenderName($senderName);
            $smsLog->setMobileNumber($mobileNumber);
            $smsLog->setApiName('Route API Premium');
            $smsLog->setTransactionId($transaction_id);
            $smsLog->save();

            return true;
        } else {
            $message="SMS not sent via ROUTE_API_Premium to this mobile numberc On Kimarin <br/>Mobile number =" . $mobileNumber . "<br/> Message is =" . $smsText . "<br/> Response from API =" . $res;
            emailLib::smsNotSentEmail($message);
            return false;
        }
    }

}
class ROUTED_SMS {

    public static function Send($mobileNumber, $smsText, $senderName=null, $smsType=null,$transaction_id=null) {
//         if (!CARBORDFISH_SMS::Send($mobileNumber, $smsText, $senderName, $smsType,$transaction_id)) {
//            if (!SMSNU::Send($mobileNumber, $smsText, $senderName, $smsType,$transaction_id)) {
              if(!ROUTE_API_Regular::Send($mobileNumber, $smsText, $senderName, $smsType, $transaction_id)){    
                 if(!ROUTE_API_Premium::Send($mobileNumber, $smsText, $senderName, $smsType, $transaction_id)){                    
                    if ($senderName == null)
                        $senderName = "Kimarin";
                     if ($smsType == null)
                        $smsType =1;
                    $smsLog = new SmsLog();
                    $smsLog->setMessage($smsText);
                    $smsLog->setSmsType($smsType);
                    $smsLog->setStatus("Unable to send from both");
                    $smsLog->setSenderName($senderName);
                    $smsLog->setMobileNumber($mobileNumber);
                    $smsLog->save();
                     return false;
                }else{
                  return true;  
                }
             }else{
                    return true;
                }                
//           }else{
//                return true; 
//            }
//        }else{ ///// cardboard fish else
//                return true; 
//        }
    }

}

?>
