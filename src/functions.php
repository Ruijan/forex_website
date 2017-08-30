<?php
function getNumeric($val) {
    if (is_numeric($val)) {
        return $val + 0;
    }
    return $val;
} 