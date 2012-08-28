<?php

class PreferredLanguages extends BasePreferredLanguages
{
   public function __toString()
    {
      return __($this->getLanguage());
    }
}
