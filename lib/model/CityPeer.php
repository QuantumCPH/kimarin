<?php

class CityPeer extends BaseCityPeer
{


    static public function getSortedSweedishCities() {
        $c = new Criteria();
        $c->add(CityPeer::COUNTRY_ID,sfConfig::get('app_country_code'));
        $c->addAscendingOrderByColumn(CityPeer::NAME);
        $rs = CityPeer::doSelect($c);
        return $rs;
    }
}
