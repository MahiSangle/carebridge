<?php

function getPriority($category)
{
    if ($category == "Blood") {
        return "High";
    }
    elseif ($category == "Food") {
        return "Medium";
    }
    elseif ($category == "Clothes") {
        return "Low";
    }
    else {
        return "Low";
    }
}

?>