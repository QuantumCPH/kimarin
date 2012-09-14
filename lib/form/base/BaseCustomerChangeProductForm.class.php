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
      'id'             => new sfWidgetFormInputHidden(),
      'customer_id'    => new sfWidgetFormInput(),
      'product_id'     => new sfWidgetFormPropelChoice(array('model' => 'Product', 'add_empty' => false)),
      'created_at'     => new sfWidgetFormDateTime(),
      'status'         => new sfWidgetFormInput(),
      'execuated_at'   => new sfWidgetFormDateTime(),
      'order_id'       => new sfWidgetFormInput(),
      'transaction_id' => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      'id'             => new sfValidatorPropelChoice(array('model' => 'CustomerChangeProduct', 'column' => 'id', 'required' => false)),
      'customer_id'    => new sfValidatorInteger(),
      'product_id'     => new sfValidatorPropelChoice(array('model' => 'Product', 'column' => 'id')),
      'created_at'     => new sfValidatorDateTime(),
      'status'         => new sfValidatorInteger(),
      'execuated_at'   => new sfValidatorDateTime(),
      'order_id'       => new sfValidatorInteger(array('required' => false)),
      'transaction_id' => new sfValidatorInteger(array('required' => false)),
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
