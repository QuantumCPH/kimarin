<?php

/**
 * CallHistoryCallsLog form base class.
 *
 * @package    zapnacrm
 * @subpackage form
 * @author     Your name here
 */
class BaseCallHistoryCallsLogForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'customer_id' => new sfWidgetFormInput(),
      'fromdate'    => new sfWidgetFormInput(),
      'todate'      => new sfWidgetFormInput(),
      'status'      => new sfWidgetFormInput(),
      'created_at'  => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorPropelChoice(array('model' => 'CallHistoryCallsLog', 'column' => 'id', 'required' => false)),
      'customer_id' => new sfValidatorInteger(),
      'fromdate'    => new sfValidatorString(array('max_length' => 255)),
      'todate'      => new sfValidatorString(array('max_length' => 255)),
      'status'      => new sfValidatorInteger(),
      'created_at'  => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('call_history_calls_log[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'CallHistoryCallsLog';
  }


}
