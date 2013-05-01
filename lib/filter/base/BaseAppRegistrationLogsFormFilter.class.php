<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/base/BaseFormFilterPropel.class.php');

/**
 * AppRegistrationLogs filter form base class.
 *
 * @package    zapnacrm
 * @subpackage filter
 * @author     Your name here
 */
class BaseAppRegistrationLogsFormFilter extends BaseFormFilterPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'mobile_number'  => new sfWidgetFormFilterInput(),
      'pwd'            => new sfWidgetFormFilterInput(),
      'email'          => new sfWidgetFormFilterInput(),
      'ccode'          => new sfWidgetFormFilterInput(),
      'code'           => new sfWidgetFormFilterInput(),
      'customer_id'    => new sfWidgetFormFilterInput(),
      'status_id'      => new sfWidgetFormFilterInput(),
      'created_at'     => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'url'            => new sfWidgetFormFilterInput(),
      'application_id' => new sfWidgetFormFilterInput(),
      'response'       => new sfWidgetFormFilterInput(),
      'register_from'  => new sfWidgetFormFilterInput(),
      'os'             => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'mobile_number'  => new sfValidatorPass(array('required' => false)),
      'pwd'            => new sfValidatorPass(array('required' => false)),
      'email'          => new sfValidatorPass(array('required' => false)),
      'ccode'          => new sfValidatorPass(array('required' => false)),
      'code'           => new sfValidatorPass(array('required' => false)),
      'customer_id'    => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'status_id'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'created_at'     => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'url'            => new sfValidatorPass(array('required' => false)),
      'application_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'response'       => new sfValidatorPass(array('required' => false)),
      'register_from'  => new sfValidatorPass(array('required' => false)),
      'os'             => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('app_registration_logs_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'AppRegistrationLogs';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'mobile_number'  => 'Text',
      'pwd'            => 'Text',
      'email'          => 'Text',
      'ccode'          => 'Text',
      'code'           => 'Text',
      'customer_id'    => 'Number',
      'status_id'      => 'Number',
      'created_at'     => 'Date',
      'url'            => 'Text',
      'application_id' => 'Number',
      'response'       => 'Text',
      'register_from'  => 'Text',
      'os'             => 'Text',
    );
  }
}
