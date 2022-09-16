<?php
    session_start();
    session_unset();
    session_destroy();
    header("Location: https://servicios.cecep.edu.co:8085/abconta");