<?php

class agentConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
      sfValidatorBase::setRequiredMessage(('You must fill in this field'));
  }
}
