<?php
function formatNumber($n) {
    if ($n >= 1000000) return round($n/1000000, 1) . 'M';
    if ($n >= 1000) return round($n/1000, 1) . 'K';
    return $n;
}
