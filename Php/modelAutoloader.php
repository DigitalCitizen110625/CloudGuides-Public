<?php
    function modelAutoLoader($modelName)
    {
        include '../models' . "/$modelName.model.php";
    }

    spl_autoload_register('modelAutoLoader');
?>