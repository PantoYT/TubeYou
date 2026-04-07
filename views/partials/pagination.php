<?php
function renderPagination(int $page, int $pages, string $baseUrl): string
{
    if ($pages <= 1) return '';

    $html = '<div class="pagination">';

    if ($page > 1) {
        $html .= '<a href="' . $baseUrl . ($page - 1) . '" class="btn">←</a>';
    }

    for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++) {
        $active = $i === $page ? ' btn-primary' : '';
        $html .= '<a href="' . $baseUrl . $i . '" class="btn' . $active . '">' . $i . '</a>';
    }

    if ($page < $pages) {
        $html .= '<a href="' . $baseUrl . ($page + 1) . '" class="btn">→</a>';
    }

    $html .= '</div>';
    return $html;
}
?>