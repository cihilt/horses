<?php
$distance = newvalue();
echo $distance;

function newvalue($length,$distance,$orgdistance,$pos,$time){
    $modifier = 0;
     //Getting the postion of the horse
$pos =  explode('/', $pos);
    		$position =  intval($pos[0]);
               
          //Getting the value of the modifier      
if ($distance >= 800 AND $distance <= 999)
	    {
	    	$modifier = 1;
	    }
	    elseif ($distance >= 1000 AND $distance <= 1099)
	    {
	    	$modifier = 0.5;
	    }
	    elseif ($distance >= 1100 AND $distance <= 4000)
	    {
	    	$modifier = 0.7;
	    }
            $remainder = get_remainder($distance);
    
        if($distance!=$orgdistance){
            if($position==1){
                
                if($distance>$orgdistance){
                    
                    $newtime =   win_rounded_up($time, $length, $modifier, $remainder);
                }else{ 
                $newtime =      win_rounded_down($time, $length, $modifier, $remainder);
                }
            }else{
                 if($distance>$orgdistance){
               $newtime =       loses_rounded_up($time, $length, $modifier, $remainder);
                }else{ 
              $newtime =        loses_rounded_down($time, $length, $modifier, $remainder);
                }
            }
        return $newtime;
        
                }else{
                    $newtime = $time;
                    return $newtime;
                }
            
            
}
function get_remainder($distance){
    if ($distance % 10 < 5)
		{
			$distance -= $distance % 10;
                       
		}
		else
		{
			$distance += (10 - ($distance % 10));
                       
		}
	    
		if ($distance % 100 < 50)
		{
			$reminder_distance = $distance % 100;
			
                       
		}
		else
		{
			$reminder_distance = (100 - ($distance % 100));
			
                        
		}
                $reminder = $reminder_distance*0.01;
                return $reminder;
}

 //if horse wins   
function win_rounded_up($time,$length,$modifier,$remainder){
    
    $newtime =  ($length * $modifier*$remainder)+$time;
    return $newtime;
}
 //if horse wins  
function win_rounded_down($time,$length,$modifier,$remainder){
       $newtime =  ($length * $modifier*$remainder)-$time;
    return $newtime;
    
}
 //if horse loses  
function loses_rounded_up($time,$length,$modifier,$remainder){
     $newtime =  ($length * $modifier*$remainder)+$time;
    return $newtime;
}
 //if horse loses  
function loses_rounded_down($time,$length,$modifier,$remainder){
     $newtime =  ($length * $modifier*$remainder)-$time;
    return $newtime;
}
?>