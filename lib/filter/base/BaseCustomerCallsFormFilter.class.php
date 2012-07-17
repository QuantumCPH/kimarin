<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/base/BaseFormFilterPropel.class.php');

/**
 * CustomerCalls filter form base class.
 *
 * @package    zapnacrm
 * @subpackage filter
 * @author     Your name here
 */
class BaseCustomerCallsFormFilter extends BaseFormFilterPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'i_xdr'             => new sfWidgetFormFilterInput(),
      'account_id'        => new sfWidgetFormFilterInput(),
      'CLI'               => new sfWidgetFormFilterInput(),
      'CLD'               => new sfWidgetFormFilterInput(),
      'charged_amount'    => new sfWidgetFormFilterInput(),
      'charged_quantity'  => new sfWidgetFormFilterInput(),
      'country'           => new sfWidgetFormFilterInput(),
      'subdivision'       => new sfWidgetFormFilterInput(),
      'description'       => new sfWidgetFormFilterInput(),
      'disconnect_cause'  => new sfWidgetFormFilterInput(),
      'bill_status'       => new sfWidgetFormFilterInput(),
      'connect_time'      => new sfWidgetFormFilterInput(),
      'unix_connect_time' => new sfWidgetFormFilterInput(),
      'disconnect_time'   => new sfWidgetFormFilterInput(),
      'bill_time'         => new sfWidgetFormFilterInput(),
      'i_customer'        => new sfWidgetFormFilterInput(),
      'customer_id'       => new sfWidgetFormFilterInput(),
      'status'            => new sfWidgetFormFilterInput(),
      'created_at'        => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
    ));

    $this->setValidators(array(
      'i_xdr'             => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'account_id'        => new sfValidatorPass(array('required' => false)),
      'CLI'               => new sfValidatorPass(array('required' => false)),
      'CLD'               => new sfValidatorPass(array('required' => false)),
      'charged_amount'    => new sfValidatorPass(array('required' => false)),
      'charged_quantity'  => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'country'           => new sfValidatorPass(array('required' => false)),
      'subdivision'       => new sfValidatorPass(array('required' => false)),
      'description'       => new sfValidatorPass(array('required' => false)),
      'disconnect_cause'  => new sfValidatorPass(array('required' => false)),
      'bill_status'       => new sfValidatorPass(array('required' => false)),
      'connect_time'      => new sfValidatorPass(array('required' => false)),
      'unix_connect_time' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'disconnect_time'   => new sfValidatorPass(array('required' => false)),
      'bill_time'         => new sfValidatorPass(array('required' => false)),
      'i_customer'        => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'customer_id'       => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'status'            => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'created_at'        => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
    ));

    $this->widgetSchema->setNameFormat('customer_calls_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'CustomerCalls';
  }

  public function getFields()
  {
    return array(
      'id'                => 'Number',
      'i_xdr'             => 'Number',
      'account_id'        => 'Text',
      'CLI'               => 'Text',
      'CLD'               => 'Text',
      'charged_amount'    => 'Text',
      'charged_quantity'  => 'Number',
      'country'           => 'Text',
      'subdivision'       => 'Text',
      'description'       => 'Text',
      'disconnect_cause'  => 'Text',
      'bill_status'       => 'Text',
      'connect_time'      => 'Text',
      'unix_connect_time' => 'Number',
      'disconnect_time'   => 'Text',
      'bill_time'         => 'Text',
      'i_customer'        => 'Number',
      'customer_id'       => 'Number',
      'status'            => 'Number',
      'created_at'        => 'Date',
    );
  }
}
