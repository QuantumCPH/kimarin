<?php
class smsCharacter{

    public static function smsCharacterReplacement($queryString)
    {  

   $replace = array(
			   '%C3%B8' => '%F8',
			   '%C3%A6' => '%E6',
			   '%C3%A5' => '%E5',
                           '%C3%86' => '%C6',
			   '%C3%98' => '%D8',
			   '%C3%85' => '%C5',
                           '%C3%96' => '%D6',
                           '%C3%B6' => '%F6',
                           '%0D%0A' => '%0D%0A', //////////////
                        '%C3%80' => '%C0',
                        '%C3%89' => '%C9',
                        '%C3%88' => '%C8',
                        '%C3%8D' => '%CD',
                        '%C3%8F' => '%CF',
                        '%C3%93' => '%D3',
                        '%C3%92' => '%D2',
                        '%C3%9A' => '%DA',
       
                         '%C3%94' => '%D4',
                        '%C3%95' => '%D5',
                         '%C3%9C' => '%C7',
                         '%C3%87' => '%DC',
                        '%C3%A4' => '%E4',
                        '%E2%82%AC' => '%80'
			
			  );
		     $from_array = array();
		     $to_array = array();

		     foreach ($replace as $k => $v){
		         $from_array[] = $k;
		         $to_array[] = $v;
		     }

		       $queryString=str_replace($from_array,$to_array,$queryString);

				return $queryString;

	}

        
         public static function smsCharacterReplacementReverse($queryString)
    {  

   $replace = array(
			    '%F8'=> '%C3%B8',
                            '%E6' => '%C3%A6',
			   '%E5' => '%C3%A5',
                           '%C6' => '%C3%86',
			   '%D8' => '%C3%98',
			   '%C5' => '%C3%85',
                           '%D6' => '%C3%96',
                           '%F6' => '%C3%B6',
                           '%0D%0A' => '%0D%0A', //////////////
                         '%C0' => '%C3%80',
                         '%C9' => '%C3%89',
                        '%C8' => '%C3%88',
                        '%CD' => '%C3%8D',
                        '%CF' => '%C3%8F',
                        '%D3' => '%C3%93',
                        '%D2' => '%C3%92',
                        '%DA' => '%C3%9A',
                         '%C7' => '%C3%9C',
                         '%DC' => '%C3%87',
                        '%E4' => '%C3%A4'	
			  );
		     $from_array = array();
		     $to_array = array();

		     foreach ($replace as $k => $v){
		         $from_array[] = $k;
		         $to_array[] = $v;
		     }

		       $queryString=str_replace($from_array,$to_array,$queryString);

				return $queryString;

	}

        
        
        
}


?>