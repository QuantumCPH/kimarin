<?php

set_time_limit(1000000000);
require_once(sfConfig::get('sf_lib_dir') . '/smsCharacterReplacement.php');

/**
 * sms actions.
 *
 * @package    zapnacrm
 * @subpackage sms
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 12479 2008-10-31 10:54:40Z fabien $
 */
class smsActions extends sfActions {

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public function executeSendSms(sfWebRequest $request) {


        $message = $request->getParameter('message');
        $delievry = "";


        if ($message) {


            $list = $request->getParameter('numbers');

            $numbers = explode(',', $list);

            $messages = array();
            if (strlen($message) < 160) {
                $messages[1] = $message;
            } else if (strlen($message) > 160 and strlen($message) < 320) {

                $messages[1] = substr($message, 1, 160) . "";
                $messages[2] = substr($message, 161) . "";
            } else if (strlen($message) > 382) {
                $messages[1] = substr($message, 1, 160) . "";
                $messages[2] = substr($message, 161, 320) . "";
                $messages[3] = substr($message, 321, 480) . "";
            }

            foreach ($messages as $sms_text) {
                foreach ($numbers as $number) {
                    $sentby = $request->getParameter('sentby');
                    $cbf = new Cbf();
                    $cbf->setS('H');
                    $cbf->setDa($number);
                    $cbf->setMessage($sms_text);
                    $cbf->setCountryId(53);
                    $cbf->setMobileNumber('kimarin Backend');

//$sms_text='ø  æ å  Æ Ø Å Ö ö';
                    $cbf->save();
//$sms_text='ø  æ å';


                    if (ROUTED_SMS::send($number, $sms_text, $sentby)) {
                        $status = "deliverd";
                    } else {
                        $status = "not Deliverd";
                    }
                    $delievry .= 'Destination: ' . $number . ', Status: ' . $status . '<br/>';
                }
            }
        }


        $this->delievry = $delievry;
    }

    public function executeSendBulkMessages(sfWebRequest $request) {

        $messageid = $request->getParameter('massage');
        $massage_body = $request->getParameter('massagetext');
        if (isset($messageid) && $messageid != "") {

            ///////////////////////////////////////////

            if (isset($messageid) && $messageid == 1) {
                foreach ($_POST['agent'] as $key => $item) {

                    $c = new Criteria();
                    $c->add(AgentCompanyPeer::ID, $item);
                    $agentid = AgentCompanyPeer::doSelectOne($c);
                    $sender_email = "support@kimarin.es";
                    $receiver_email = $agentid->getEmail();
                    $receipientname = $agentid->getName();
                    $receiver_email = trim($receiver_email);
                    $subject = "Agent Update";

                    if (isset($receiver_email) && $receiver_email != '') {
                        $email4 = new EmailQueue();
                        $email4->setSubject($subject);
                        $email4->setReceipientName($receipientname);
                        $email4->setReceipientEmail($receiver_email);
                        $email4->setEmailType('kimarin  Agent SMS Via Support');
                        $email4->setMessage($massage_body);
                        $email4->save();
                    }
                }
                $delievry = " Your Email has been sent";

                $this->delievry = $delievry;
            }
            ///////////////////////////////////////////////////////////
            if (isset($messageid) && $messageid == 2) {

                $delievry = "";
                foreach ($_POST['agent'] as $key => $item) {
                    $c = new Criteria();
                    $c->add(AgentCompanyPeer::ID, $item);
                    $agentid = AgentCompanyPeer::doSelectOne($c);

                    //////////

                    $mobilenumber = $agentid->getMobileNumber();

                    if (isset($mobilenumber) && $mobilenumber != "") {
                        $message = $massage_body;

                        if (isset($message) && $message != "") {



                            $messages = array();
                            if (strlen($message) < 160) {
                                $messages[1] = $message;
                            } else if (strlen($message) > 160 and strlen($message) < 320) {

                                $messages[1] = substr($message, 1, 160);
                                $messages[2] = substr($message, 161);
                            } else if (strlen($message) > 320) {
                                $messages[1] = substr($message, 1, 160);
                                $messages[2] = substr($message, 161, 320);
                                $messages[3] = substr($message, 321, 480);
                            }

                            foreach ($messages as $sms_text) {



                                $sentby = "KIMARIN";
                                if (ROUTED_SMS::send($mobilenumber, $sms_text, $sentby)) {
                                    $status = "deliverd";
                                } else {
                                    $status = "not Deliverd";
                                }
                                $delievry .= 'Destination: ' . $mobilenumber . ', Status: ' . $status . '<br/>';
                            }
                        }
                        $this->delievry = $delievry;

                        //////////
                    }
                }
            }
            ////////////////////////////////////////////////////////////

            if (isset($messageid) && $messageid == 3) {

                foreach ($_POST['agent'] as $key => $item) {

                    $c = new Criteria();
                    $c->add(AgentCompanyPeer::ID, $item);
                    $agentid = AgentCompanyPeer::doSelectOne($c);
                    $sender_email = "support@kimarin.es";
                    $receiver_email = $agentid->getEmail();
                    $receipientname = $agentid->getName();
                    $receiver_email = trim($receiver_email);
                    $subject = "Agent Update";

                    if (isset($receiver_email) && $receiver_email != '') {
                        $email4 = new EmailQueue();
                        $email4->setSubject($subject);
                        $email4->setReceipientName($receipientname);
                        $email4->setReceipientEmail($receiver_email);

                        $email4->setEmailType('kimarin  Agent SMS Via Support');
                        $email4->setMessage($massage_body);
                        $email4->save();
                    }
                }
                $delievry = "";
                $delievry = " Your Email has been sent <br/>";
                foreach ($_POST['agent'] as $key => $item) {
                    $c = new Criteria();
                    $c->add(AgentCompanyPeer::ID, $item);
                    $agentid = AgentCompanyPeer::doSelectOne($c);




                    //////////

                    $mobilenumber = $agentid->getMobileNumber();

                    if (isset($mobilenumber) && $mobilenumber != "") {
                        $message = $massage_body;

                        if (isset($message) && $message != "") {



                            $messages = array();
                            if (strlen($message) < 160) {
                                $messages[1] = $message;
                            } else if (strlen($message) > 161 and strlen($message) < 320) {

                                $messages[1] = substr($message, 1, 160);
                                $messages[2] = substr($message, 161);
                            } else if (strlen($message) > 320) {
                                $messages[1] = substr($message, 1, 160);
                                $messages[2] = substr($message, 161, 320);
                                $messages[3] = substr($message, 321, 480);
                            }

                            foreach ($messages as $sms_text) {

                                $sentby = "KIMARIN";
                                if (ROUTED_SMS::send($mobilenumber, $sms_text, $sentby)) {
                                    $status = "deliverd";
                                } else {
                                    $status = "not Deliverd";
                                }
                                $delievry .= 'Destination: ' . $mobilenumber . ', Status: ' . $status . '<br/>';
                            }
                        }



                        $this->delievry = $delievry;

                        //////////
                    }
                }
            }

            /////////////////////////////////////////////////////////////////
        }
    }

    public function executeSendBulkMessagesCustomer(sfWebRequest $request) {


        $messageid = $request->getParameter('massage');
        $massage_body = $request->getParameter('massagetext');
        if (isset($messageid) && $messageid != "") {

            ///////////////////////////////////////////

            if (isset($messageid) && $messageid == 1) {
                foreach ($_POST['agent'] as $key => $item) {

                    $c = new Criteria();
                    $c->add(CustomerPeer::ID, $item);
                    $agentid = CustomerPeer::doSelectOne($c);
                    $sender_email = "support@kimarin.es";
                    $receiver_email = $agentid->getEmail();
                    $receipientname = $agentid->getFirstName();
                    $receiver_email = trim($receiver_email);
                    $subject = "Kimarin Update";

                    if (isset($receiver_email) && $receiver_email != '') {
                        $email4 = new EmailQueue();
                        $email4->setSubject($subject);
                        $email4->setReceipientName($receipientname);
                        $email4->setReceipientEmail($receiver_email);
                        $email4->setEmailType('kimarin   SMS Via Support');
                        $email4->setMessage($massage_body);
                        $email4->save();
                    }
                }
                $delievry = " Your Email has been sent";

                $this->delievry = $delievry;
            }
            ///////////////////////////////////////////////////////////
            if (isset($messageid) && $messageid == 2) {

                $delievry = "";
                foreach ($_POST['agent'] as $key => $item) {
                    $c = new Criteria();
                    $c->add(CustomerPeer::ID, $item);
                    $agentid = CustomerPeer::doSelectOne($c);

                    //////////
                    $mobilenumber = $agentid->getMobileNumber();
                    if (isset($mobilenumber) && $mobilenumber != "") {
                        $message = $massage_body;
                        if (isset($message) && $message != "") {
                            $messages = array();
                            if (strlen($message) < 160) {
                                $messages[1] = $message;
                            } else if (strlen($message) > 160 and strlen($message) < 320) {
                                $messages[1] = substr($message, 1, 160);
                                $messages[2] = substr($message, 161);
                            } else if (strlen($message) > 382) {
                                $messages[1] = substr($message, 1, 160);
                                $messages[2] = substr($message, 161, 320);
                                $messages[3] = substr($message, 321, 480);
                            }
                            foreach ($messages as $sms_text) {


                                $sentby = "KIMARIN";
                                if (ROUTED_SMS::send($mobilenumber, $sms_text, $sentby)) {
                                    $status = "deliverd";
                                } else {
                                    $status = "not Deliverd";
                                }
                                $delievry .= 'Destination: ' . $mobilenumber . ', Status: ' . $status . '<br/>';
                            }
                            $this->delievry = $delievry;
                            //////////
                        }
                    }
                }
            }
                ////////////////////////////////////////////////////////////
                if (isset($messageid) && $messageid == 3) {
                    foreach ($_POST['agent'] as $key => $item) {
                        $c = new Criteria();
                        $c->add(CustomerPeer::ID, $item);
                        $agentid = CustomerPeer::doSelectOne($c);
                        $sender_email = "okhan@zapna.com";
                        $receiver_email = $agentid->getEmail();
                        $receipientname = $agentid->getFirstName();
                        $receiver_email = trim($receiver_email);
                        $subject = "Kimarin Update";
                        if (isset($receiver_email) && $receiver_email != '') {
                            $email4 = new EmailQueue();
                            $email4->setSubject($subject);
                            $email4->setReceipientName($receipientname);
                            $email4->setReceipientEmail($receiver_email);
                            $email4->setEmailType('Kimarin   SMS Via Support');
                            $email4->setMessage($massage_body);
                            $email4->save();
                        }
                    }
                    $delievry = "";
                    $delievry = " Your Email has been sent <br/>";
                    foreach ($_POST['agent'] as $key => $item) {
                        $c = new Criteria();
                        $c->add(CustomerPeer::ID, $item);
                        $agentid = CustomerPeer::doSelectOne($c);
                        //////////
                        $mobilenumber = $agentid->getMobileNumber();
                        if (isset($mobilenumber) && $mobilenumber != "") {
                            $message = $massage_body;
                            if (isset($message) && $message != "") {
                                $messages = array();
                                if (strlen($message) < 160) {
                                    $messages[1] = $message;
                                } else if (strlen($message) > 161 and strlen($message) < 320) {
                                    $messages[1] = substr($message, 1, 160);
                                    $messages[2] = substr($message, 161);
                                } else if (strlen($message) > 382) {
                                    $messages[1] = substr($message, 1, 160);
                                    $messages[2] = substr($message, 161, 320);
                                    $messages[3] = substr($message, 321, 480);
                                }

                                foreach ($messages as $sms_text) {
                                    $sentby = "KIMARIN";
                                    if (ROUTED_SMS::send($mobilenumber, $sms_text, $sentby)) {
                                        $status = "deliverd";
                                    } else {
                                        $status = "not Deliverd";
                                    }
                                    $delievry .= 'Destination: ' . $mobilenumber . ', Status: ' . $status . '<br/>';
                                }
                                $this->delievry = $delievry;
                                //////////
                            }
                        }
                    }
                    /////////////////////////////////////////////////////////////////
                }
            }
        
    }

}