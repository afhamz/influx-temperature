<?php
	$url=$_SERVER['REQUEST_URI'];
	header("Refresh: 20; URL=$url");

	require 'vendor/autoload.php';

    $br_json = file_get_contents('http://api.openweathermap.org/data/2.5/weather?q=Yogyakarta,id&APPID=99174197b1cc56b0e044f0180b76ad92');
    $br_obj = json_decode($br_json, true);

	$host = 'localhost';

	error_reporting(0);

	$client = new \InfluxDB\Client($host);

	$database = $client->selectDB('ws_db2');
    $t_city = $br_obj['name'];
    $t_temp = (int) ($br_obj['main']['temp'] - 273.15);
    
    $points = [];

    $points[] = new \InfluxDB\Point(
        'weather',
        null,
        ['city' => $t_city],
        ['temp' => $t_temp]
    );

    // insert the points
    $database->writePoints($points);

	$results = $database->query("SELECT mean(temp) AS mean_temp FROM weather WHERE time > now() - 6h AND city='Yogyakarta' GROUP BY time(30m) FILL(null)")->getPoints();
    /*$results = $database->query("SELECT mean(temp) AS mean_temp FROM weather WHERE time > now() - 6h AND city='Yogyakarta' GROUP BY time(30m) FILL(null)")->getPoints();*/

	$datas = [];
	foreach ($results as $br_data){ //loop through data
        $x1 = strtotime($br_data['time']);
        $a1 = (int) $x1;
        $a1 = $a1 + 25200;
        $x1 = (string) $a1;
        $x1 = $x1 . "000";

        $x2 = $br_data['mean_temp'];
        if ($x2 == '') $x2 = 0;
        else $x2 = (string) ( (int) $x2 );
        
        $tmp2 = [];
        $tmp2[] = $x1;
        $tmp2[] = $x2;
        $datas[] = $tmp2;
    }
    //print_r($datas);
    /*$datas = [[1493326164493, 100],
              [1593326164493, 150],
              [1693326164493, 60],
              [1793326194018, 120]];*/
    $tmp = "[";
    foreach ($datas as $x){
    	$tmp = $tmp . "[";
    	$tmp = $tmp . (string) $x[0];
    	$tmp = $tmp . ",";
    	$tmp = $tmp . (string) $x[1];
    	$tmp = $tmp . "]";
    	$tmp = $tmp . ",";
    }
    $tmp = substr($tmp, 0, -1);
    $tmp = $tmp . "]";
    $datatext = $tmp;
?>
<html lang="en" class="no-js">

<head>
    <meta http-equiv="refresh" content="20">
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <!--[if IE]>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <![endif]-->
    <title>Yogyakarta's Temperature</title>
    <!-- BOOTSTRAP CORE CSS -->
    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <!-- FANCYBOX POPUP STYLES -->
    <link href="assets/js/source/jquery.fancybox.css" rel="stylesheet" />
    <!-- STYLES FOR VIEWPORT ANIMATION -->
    <link href="assets/css/animations.min.css" rel="stylesheet" />
    <!-- CUSTOM CSS -->
    <link href="assets/css/style-red.css" rel="stylesheet" />
    <!-- HTML5 Shiv and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <script src="assets/js/jquery-1.11.1.js"></script>
    <!-- HIGHCHARTS -->
    <script src="assets/highcharts/highcharts.js"></script>
    <script src="assets/highcharts/modules/data.js"></script>
    <script src="assets/highcharts/modules/exporting.js"></script>
    <!-- themes -->
    <script src="assets/highcharts/themes/grid-light.js"></script>

</head>


<body data-spy="scroll" data-target="#menu-section" onload=”javascript:setTimeout(“location.reload(true);”,20000);”>

    <!--SERVICE SECTION START-->
    <section id="services">
        <div class="container">
            <div class="row text-center header">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 animate-in" data-anim-type="fade-in-up">
                    <h3>Yogyakarta's Temperature</h3>
                    <hr />
                </div>
            </div>
            <div class="row animate-in" data-anim-type="fade-in-up">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <div class="services-wrapper">

                        <script type="text/javascript">
                            $(function () {
                                $('#container').highcharts({
                                    title: {
                                        text: ''
                                    },
                                     xAxis: {
                                        type: 'datetime',
                                        labels: {
                                            format: '{value:%H:%M<br>%Y-%b-%e}'
                                        },
                                    },
                                    yAxis: {
                                        allowDecimals: false,
                                        title: {
                                            text: 'Temperature (C)'
                                        }
                                    },
                                    series: [{
                                        data: 
                                          <?php echo $datatext; ?>,
                                        name: 'Temperature'
                                    }]
                                });
                            });
                        </script>
                        <div id="container"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--SERVICE SECTION END-->
   
    


    <!-- JAVASCRIPT FILES PLACED AT THE BOTTOM TO REDUCE THE LOADING TIME -->
    <!-- CORE JQUERY -->
    <!-- BOOTSTRAP SCRIPTS -->
    <script src="assets/js/bootstrap.js"></script>
    <!-- EASING SCROLL SCRIPTS PLUGIN -->
    <script src="assets/js/vegas/jquery.vegas.min.js"></script>
    <!-- CUSTOM SCRIPTS -->
    <script src="assets/js/custom.js"></script>
</body> 

</html>