<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/base/BaseFormFilterPropel.class.php');

/**
 * CustomerChangeProduct filter form base class.
 *
 * @package    zapnacrm
 * @subpackage filter
 * @author     Your name here
 */
class BaseCustomerChangeProductFormFilter extends BaseFormFilterPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'customer_id'    => new sfWidgetFormFilterInput(),
      'product_id'     => new sfWidgetFormPropelChoice(array('model' => 'Product', 'add_empty' => true)),
      'created_at'     => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'status'         => new sfWidgetFormFilterInput(),
      'execuated_at'   => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'order_id'       => new sfWidgetFormFilterInput(),
      'transaction_id' => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'customer_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'product_id'     => new sfValidatorPropelChoice(array('required' => false, 'model' => 'Product', 'column' => 'id')),
      'created_at'     => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'status'         => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'execuated_at'   => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'order_id'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'transaction_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
    ));

    $this->widgetSchema->setNameFormat('customer_change_product_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'CustomerChangeProduct';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'customer_id'    => 'Number',
      'product_id'     => 'ForeignKey',
      'created_at'     => 'Date',
      'status'         => 'Number',
      'execuated_at'   => 'Date',
      'order_id'       => 'Number',
      'transaction_id' => 'Number',
    );
  }
}
