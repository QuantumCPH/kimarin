<?php

require_once(sfConfig::get('sf_lib_dir').'/filter/base/BaseFormFilterPropel.class.php');

/**
 * AppLoginLogs filter form base class.
 *
 * @package    zapnacrm
 * @subpackage filter
 * @author     Your name here
 */
class BaseAppLoginLogsFormFilter extends BaseFormFilterPropel
{
  public function setup()
  {
    $this->setWidgets(array(
      'mobile_number'  => new sfWidgetFormFilterInput(),
      'pwd'            => new sfWidgetFormFilterInput(),
      'parent_table'   => new sfWidgetFormFilterInput(),
      'parent_id'      => new sfWidgetFormFilterInput(),
      'application_id' => new sfWidgetFormFilterInput(),
      'status_id'      => new sfWidgetFormFilterInput(),
      'created_at'     => new sfWidgetFormFilterDate(array('from_date' => new sfWidgetFormDate(), 'to_date' => new sfWidgetFormDate(), 'with_empty' => false)),
      'url'            => new sfWidgetFormFilterInput(),
      'response'       => new sfWidgetFormFilterInput(),
    ));

    $this->setValidators(array(
      'mobile_number'  => new sfValidatorPass(array('required' => false)),
      'pwd'            => new sfValidatorPass(array('required' => false)),
      'parent_table'   => new sfValidatorPass(array('required' => false)),
      'parent_id'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'application_id' => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'status_id'      => new sfValidatorSchemaFilter('text', new sfValidatorInteger(array('required' => false))),
      'created_at'     => new sfValidatorDateRange(array('required' => false, 'from_date' => new sfValidatorDate(array('required' => false)), 'to_date' => new sfValidatorDate(array('required' => false)))),
      'url'            => new sfValidatorPass(array('required' => false)),
      'response'       => new sfValidatorPass(array('required' => false)),
    ));

    $this->widgetSchema->setNameFormat('app_login_logs_filters[%s]');

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    parent::setup();
  }

  public function getModelName()
  {
    return 'AppLoginLogs';
  }

  public function getFields()
  {
    return array(
      'id'             => 'Number',
      'mobile_number'  => 'Text',
      'pwd'            => 'Text',
      'parent_table'   => 'Text',
      'parent_id'      => 'Number',
      'application_id' => 'Number',
      'status_id'      => 'Number',
      'created_at'     => 'Date',
      'url'            => 'Text',
      'response'       => 'Text',
    );
  }
}
