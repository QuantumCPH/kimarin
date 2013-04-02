<?php

/**
 * AppLoginLogs form base class.
 *
 * @package    zapnacrm
 * @subpackage form
 * @author     Your name here
 */
class BaseAppLoginLogsForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'             => new sfWidgetFormInputHidden(),
      'mobile_number'  => new sfWidgetFormInput(),
      'pwd'            => new sfWidgetFormInput(),
      'customer_id'    => new sfWidgetFormInput(),
      'application_id' => new sfWidgetFormInput(),
      'status_id'      => new sfWidgetFormInput(),
      'created_at'     => new sfWidgetFormDateTime(),
      'url'            => new sfWidgetFormTextarea(),
      'response'       => new sfWidgetFormTextarea(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorPropelChoice(array('model' => 'AppLoginLogs', 'column' => 'id', 'required' => false)),
      'mobile_number'  => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'pwd'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'customer_id'    => new sfValidatorInteger(array('required' => false)),
      'application_id' => new sfValidatorInteger(array('required' => false)),
      'status_id'      => new sfValidatorInteger(array('required' => false)),
      'created_at'     => new sfValidatorDateTime(),
      'url'            => new sfValidatorString(array('required' => false)),
      'response'       => new sfValidatorString(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('app_login_logs[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'AppLoginLogs';
  }


}
