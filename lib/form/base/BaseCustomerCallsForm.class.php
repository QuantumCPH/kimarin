<?php

/**
 * CustomerCalls form base class.
 *
 * @package    zapnacrm
 * @subpackage form
 * @author     Your name here
 */
class BaseCustomerCallsForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'                => new sfWidgetFormInputHidden(),
      'i_xdr'             => new sfWidgetFormInput(),
      'account_id'        => new sfWidgetFormInput(),
      'CLI'               => new sfWidgetFormInput(),
      'CLD'               => new sfWidgetFormInput(),
      'charged_amount'    => new sfWidgetFormInput(),
      'charged_quantity'  => new sfWidgetFormInput(),
      'country'           => new sfWidgetFormInput(),
      'subdivision'       => new sfWidgetFormInput(),
      'description'       => new sfWidgetFormInput(),
      'disconnect_cause'  => new sfWidgetFormInput(),
      'bill_status'       => new sfWidgetFormInput(),
      'connect_time'      => new sfWidgetFormInput(),
      'unix_connect_time' => new sfWidgetFormInput(),
      'disconnect_time'   => new sfWidgetFormInput(),
      'bill_time'         => new sfWidgetFormInput(),
      'i_customer'        => new sfWidgetFormInput(),
      'customer_id'       => new sfWidgetFormInput(),
      'status'            => new sfWidgetFormInput(),
      'created_at'        => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'                => new sfValidatorPropelChoice(array('model' => 'CustomerCalls', 'column' => 'id', 'required' => false)),
      'i_xdr'             => new sfValidatorInteger(),
      'account_id'        => new sfValidatorString(array('max_length' => 255)),
      'CLI'               => new sfValidatorString(array('max_length' => 255)),
      'CLD'               => new sfValidatorString(array('max_length' => 255)),
      'charged_amount'    => new sfValidatorString(array('max_length' => 255)),
      'charged_quantity'  => new sfValidatorInteger(),
      'country'           => new sfValidatorString(array('max_length' => 255)),
      'subdivision'       => new sfValidatorString(array('max_length' => 255)),
      'description'       => new sfValidatorString(array('max_length' => 255)),
      'disconnect_cause'  => new sfValidatorString(array('max_length' => 255)),
      'bill_status'       => new sfValidatorString(array('max_length' => 255)),
      'connect_time'      => new sfValidatorString(array('max_length' => 255)),
      'unix_connect_time' => new sfValidatorInteger(),
      'disconnect_time'   => new sfValidatorString(array('max_length' => 255)),
      'bill_time'         => new sfValidatorString(array('max_length' => 255)),
      'i_customer'        => new sfValidatorInteger(),
      'customer_id'       => new sfValidatorInteger(),
      'status'            => new sfValidatorInteger(),
      'created_at'        => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('customer_calls[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'CustomerCalls';
  }


}
