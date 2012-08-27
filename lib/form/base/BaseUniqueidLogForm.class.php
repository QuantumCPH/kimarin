<?php

/**
 * UniqueidLog form base class.
 *
 * @package    zapnacrm
 * @subpackage form
 * @author     Your name here
 */
class BaseUniqueidLogForm extends BaseFormPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'id'            => new sfWidgetFormInputHidden(),
      'customer_id'   => new sfWidgetFormInput(),
      'unique_number' => new sfWidgetFormInput(),
      'created_at'    => new sfWidgetFormDateTime(),
    ));

    $this->setValidators(array(
      'id'            => new sfValidatorPropelChoice(array('model' => 'UniqueidLog', 'column' => 'id', 'required' => false)),
      'customer_id'   => new sfValidatorInteger(),
      'unique_number' => new sfValidatorString(array('max_length' => 50)),
      'created_at'    => new sfValidatorDateTime(),
    ));

    $this->widgetSchema->setNameFormat('uniqueid_log[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'UniqueidLog';
  }


}
