<?php

/**
 * CustomerChangeProduct form base class.
 *
 * @package    zapnacrm
 * @subpackage form
 * @author     Your name here
 */
class BaseCustomerChangeProductForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'           => new sfWidgetFormInputHidden(),
      'customer_id'  => new sfWidgetFormInput(),
      'product_id'   => new sfWidgetFormInput(),
      'created_at'   => new sfWidgetFormDateTime(),
      'status'       => new sfWidgetFormInput(),
      'execuated_at' => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'           => new sfValidatorPropelChoice(array('model' => 'CustomerChangeProduct', 'column' => 'id', 'required' => false)),
      'customer_id'  => new sfValidatorInteger(),
      'product_id'   => new sfValidatorInteger(),
      'created_at'   => new sfValidatorDateTime(),
      'status'       => new sfValidatorInteger(),
      'execuated_at' => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('customer_change_product[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'CustomerChangeProduct';
  }


}
