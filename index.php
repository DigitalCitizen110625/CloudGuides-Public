<?php
$homePage = './CloudGuides/Php/views/index.view.php';
echo "<script type='text/javascript'>document.location.href='{$homePage}';</script>";
echo '<META HTTP-EQUIV="refresh" content="0;URL=' . $homePage . '">';
?>