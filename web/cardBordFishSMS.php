 
<?php
 
 $cmobilenumber=923006826451;
     $sms_text =" 
      À    
      É    
      È    
      Í    
      Ï   
      Ó   
      Ò   
      Ú   
      Ü   
     Ç  ";
              $data = array(
            'S' => 'H',
            'UN' => 'zapna1',
            'P' => 'Zapna2010',
            'DA' => $cmobilenumber,
            'SA' => '923006826451',
            'M' => $sms_text,
            'ST' => '5'
        );
              
        
              
              
                $queryString = http_build_query($data, '', '&');
                
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
                        '%C0' => '%C0',
                        '%C9' => '%C9',
                        '%C8' => '%C8',
                        '%CD' => '%CD',
                        '%CF' => '%CF',
                        '%D3' => '%D3',
                        '%D2' => '%D2',
                        '%DA' => '%DA',
                         ' %C7' => ' %C7',
                         '%DC' => '%DC',
                        '%C3%A4' => '%E4'
			
			  );
		     $from_array = array();
		     $to_array = array();

		     foreach ($replace as $k => $v){
		         $from_array[] = $k;
		         $to_array[] = $v;
		     }
  echo $queryString;
		       $queryString=str_replace($from_array,$to_array,$queryString);  
               
                echo "<br/>". $queryString;
                
                
 // echo "<hr/> ".$res = file_get_contents('http://sms1.cardboardfish.com:9001/HTTPSMS?' . $queryString);
  
?>
