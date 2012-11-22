<?php

class Transaction extends BaseTransaction {
    
     public function __toString()
    {
      return __($this->getDescription());
    }
}
