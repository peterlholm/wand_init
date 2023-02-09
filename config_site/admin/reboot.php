<!DOCTYPE html>
<html lang="uk">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Danwand Configuration</title>
  <link rel="icon" href="/db_logo_icon.png" type="image/x-icon">
  <link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css" />
  <link rel="stylesheet" type="text/css" href="/css/site.css" />
  <meta http-equiv="refresh" content="20;url=/">
</head>

<body>
  <div class="container">
    <?php
    require('../menu.php');
    require('../func.php');
    require('../dw_func.php');
    ?>
    <h1>Rebooting .....</h1>
  </div>
  <script src="/js/jquery-3.2.1.slim.min.js"></script>
  <script src="/js/popper.min.js"></script>
  <script src="/js/bootstrap.min.js"></script>
</body>

</html>
<?php
ob_flush(); flush();
system_set_mode("restart");
