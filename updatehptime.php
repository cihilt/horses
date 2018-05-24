<?php
include('constant.php');
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();
if (!isset($_SESSION['mname'])) {
    $_SESSION['mname'] = $_REQUEST['mname'];
}



//$sql = "SELECT *, MIN(time) minimumtime,AVG(time) avgtime FROM data WHERE `name` IN (";
$sql = "SELECT * , MIN(data.time) minimumtime,MIN(data.time2) minimumtime2 FROM horses LEFT JOIN data ON horses.horse_name = data.name GROUP BY id";


$result = $conn->query($sql);


?>

                        <?php
                        if ($result->num_rows > 0) {
                            // output data of each row
                            while ($row = $result->fetch_assoc()) {
                              
                               
                                $distance = round($row["original_distance"] / 100);
                                $distance = $distance * 100;
                                $newhandicap = newvalue($row["length"], $row["original_distance"], $distance, $row["pos"], number_format($row["minimumtime"], 2));
                                $newhandi = number_format($newhandicap, 3);
                                $id = $row['id'];
                                // $newhandicap = newvalue($row["length"], $row["original_distance"], $row["distance"], $row["pos"], number_format($row["minimumtime"],2));
 $updatehptime = "UPDATE `data` SET `handicap`=$newhandi WHERE id = $id";
echo $updatehptime."<br>";
echo "-------------------";
 $result2 = $conn->query($updatehptime);

                        
                            }
                        } else {
                            echo "0 results";
                        }
                       
                        ?>
                


<?php




?>
    
<script type="text/javascript">
        $(document).ready(function () {
            $('#employee_grid').DataTable({ 
             "pageLength": 25,     
            initComplete: function () {
            this.api().columns().every( function () {
                var column = this;
                var select = $('<select><option value=""></option></select>')
                    .appendTo( $(column.footer()).empty() )
                    .on( 'change', function () {
                        var val = $.fn.dataTable.util.escapeRegex(
                            $(this).val()
                        );
 
                        column
                            .search( val ? '^'+val+'$' : '', true, false )
                            .draw();
                    } );
 
                column.data().unique().sort().each( function ( d, j ) {
                    select.append( '<option value="'+d+'">'+d+'</option>' )
                } );
            } );
        }
            });
            
              $('#employee_grid1').DataTable({

                "responsive": true,
            });
        });
        
        
    </script>


    <?php

    function newvalue($length, $distance, $orgdistance, $pos, $time) {
        $modifier = 0;
        //Getting the postion of the horse
        $pos = explode('/', $pos);
        $position = intval($pos[0]);

        //Getting the value of the modifier      
        /* if ($distance >= 800 AND $distance <= 999)
          {
          $modifier = 1;
          }
          elseif ($distance >= 1000 AND $distance <= 1099)
          {
          $modifier = 0.05;
          }
          elseif ($distance >= 1100 AND $distance <= 4000)
          {
          $modifier = 0.07;
          } */
        $modifier = 0.05;
        $remainder = get_remainder($distance);




        if ($position == 1) {

            if ($distance < $orgdistance) {

                $newtime = win_rounded_up($time, $length, $modifier, $remainder);
            } else {
                $newtime = win_rounded_down($time, $length, $modifier, $remainder);
            }
        } else {
            if ($distance < $orgdistance) {
                $newtime = loses_rounded_up($time, $length, $modifier, $remainder);
            } else {
                $newtime = loses_rounded_down($time, $length, $modifier, $remainder);
            }
        }
        return $newtime;
    }

    function get_remainder($distance) {

        if ($distance % 10 < 5) {
            $distance -= $distance % 10;
        } else {
            $distance += (10 - ($distance % 10));
        }

        if ($distance % 100 < 50) {
            $reminder_distance = $distance % 100;
        } else {
            $reminder_distance = (100 - ($distance % 100));
        }
        $reminder = $reminder_distance;
        return $reminder;
    }

    //if horse wins   
    function win_rounded_up($time, $length, $modifier, $remainder) {

        $newtime = $time + (0.0007 * $remainder);
        return $newtime;
    }

    //if horse wins  
    function win_rounded_down($time, $length, $modifier, $remainder) {

        $newtime = $time - (0.0007 * $remainder);
        return $newtime;
    }

    //if horse loses  
    function loses_rounded_up($time, $length, $modifier, $remainder) {
        //time+(length*modifier)-(0.0007*$remainder);

        $newtime = $time + ($length * $modifier) + (0.0007 * $remainder);
        return $newtime;
    }

    //if horse loses  
    function loses_rounded_down($time, $length, $modifier, $remainder) {

        $newtime = $time + ($length * $modifier) - (0.0007 * $remainder);
        return $newtime;
    }
    ?>
