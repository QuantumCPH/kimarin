<?php

class Nationality extends BaseNationality
{
   public function __toString()
    {
      return $this->getTitle();
    }
}
