<?php

class Province extends BaseProvince
{
      public function __toString()
    {
      return $this->getProvince();
    }
}
