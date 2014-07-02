<?php
   require_once("blowfish.php");
?>


<html>
   <head>
   </head>
   <!--! <body bgcolor="#758B96" >-->
   <body>
    <font color="#ff0000">

      <?php

      // hier die Parameter einsetzen

      $merchant = "MerchantID einsetzen";
      $trans    = "1111111";
      $amount   = $_POST["Betrag"];
      $currency = "EUR";
      $urlSuccess = $sURL."paySuccess.php";
      $urlFailure = $sURL."payError.php";
      $urlNotify = $sURL."payNotify.php";
      $produktDesc = "xxx";


      $plaintext = "MerchantID=".$merchant."&TransID=".$trans."&Amount=".$amount."&Currency=".$currency."&URLSuccess=".$urlSuccess."&URLFailure=".$urlFailure."&URLNotify=".$urlNotify."&OrderDesc=".$produktDesc;
      $len = strlen($plaintext);
      $password = "hier passwort einsetzen";

      $bf = new ctBlowfish();
      $cipher = $bf->ctEncrypt( $plaintext, $len, $password );

      echo "Data=" . $cipher . "<br>";

      echo "<FORM method=\"POST\" action=\"https://txms.gzs.de/payssl.aspx\" >";
      echo "<INPUT type=\"submit\" name=\"Zahlen\" value=\"Bezahlen\">";
      echo "<INPUT type=\"hidden\" name=\"Data\" value=\"" . $cipher . "\">";
      echo "<INPUT type=\"hidden\" name=\"MerchantID\" value=\"MerchantID einsetzen\">";
      echo "<INPUT type=\"hidden\" name=\"Len\" value=\"" . $len . "\">";
      echo "</FORM>";
      ?>
      </font></div>
   </body>
</html>

