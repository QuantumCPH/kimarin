<?php

/**
 * ReceiptNumbers form base class.
 *
 * @package    zapnacrm
 * @subpackage form
 * @author     Your name here
 */
class BaseReceiptNumbersForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'          => new sfWidgetFormInputHidden(),
      'description' => new sfWidgetFormInput(),
      'parent_id'   => new sfWidgetFormInput(),
      'parent'      => new sfWidgetFormInput(),
    ));

    $this->setValidators(array(
      'id'          => new sfValidatorPropelChoice(array('model' => 'ReceiptNumbers', 'column' => 'id', 'required' => false)),
      'description' => new sfValidatorString(array('max_length' => 255)),
      'parent_id'   => new sfValidatorInteger(),
      'parent'      => new sfValidatorString(array('max_length' => 255, 'required' => false)),
    ));

    $this->widgetSchema->setNameFormat('receipt_numbers[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ReceiptNumbers';
  }


}
