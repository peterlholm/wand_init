<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, xheight=device-height, initial-scale=1.0">
  <title>Danwand Debug</title>
  <!-- <link rel="manifest" href="/manifest.json" crossorigin="use-credentials"> -->
  <link rel="icon" href="db_logo_icon.png" type="image/png">
  <link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" href="/css/site.css" />
</head>

<body>
    <div class="container">
        <?php
        $windows = 0;
        $config_file = "/etc/danwand.conf";

        if (PHP_OS == 'WINNT') {
            $windows = 1;
            $config_dir = "config/danwand.conf";
        }

        function read_file($file)
        {
            $resud = exec( "sudo cat $file", $output, $res);
            //echo "Result $res $resud"; 
            //print_r($output);
            $out = implode("\n", $output);
            return $out;
        }

        if (file_exists($config_file))
            $config = file_get_contents($config_file);

        if (isset(($_REQUEST['function']))) {
            switch ($_REQUEST['function']) {
                case 'log':
                    $file = $_REQUEST['file'];
                   
                    echo "<h2>File content: $file</h2>\n";
                    echo "<pre>\n";
                    echo read_file($file);
                    echo "</pre>\n";
                    break;
            }
        }
            
        ?>
	<br>
        <a href="/debug/index.php" class="btn btn-lg btn-primary">Return</a>
    </div>
</body>

</html>
