<?php


function CalculateMaxPlanetFields ($planet) {
    global $ge_resource;

    return $planet["field_max"] + ($planet[ $ge_resource[33] ] * 5);
}

?>