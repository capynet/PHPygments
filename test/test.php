<?php
require "../PHPygments.php";
$code = file_get_contents("test.js");
$result = PHPygments::render($code, "js");
?>

<!DOCTYPE html>
<html>
<head>
  <title></title>

  <style type="text/css">
    .container {
      width: 50%;
      margin: 0 auto;
    }
  </style>

  <?php
  //Load CSS for this highlighted code
  echo '<link href="../' . $result["styles"] . '" media="all" rel="stylesheet" type="text/css" />';
  ?>
</head>
<body>

<div class="container">
  <?php echo $result["code"]; ?>
</div>

</body>
</html>


