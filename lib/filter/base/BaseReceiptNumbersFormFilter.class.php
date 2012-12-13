<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/base/BaseFormFilterPropel.class.php');

/**
 * ReceiptNumbers filter form base class.
 *
 * @package    zapnacrm
 * @subpackage filter
 * @author     Your name here
 */
class BaseReceiptNumbersFormFilter extends BaseFormFilterPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'description' => new sfWidgetFormFilterInput(),
      'parent_id'   => new sfWidgetFormFilterInput(),
      'parent'      => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'description' => new sfValidatorPass(array('required' => false)),
      'parent_id'   => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'parent'      => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('receipt_numbers_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'ReceiptNumbers';
  }

  public function getFields()
  {
    return array(
      'id'          => 'Number',
      'description' => 'Text',
      'parent_id'   => 'Number',
      'parent'      => 'Text',
    );
  }
}
