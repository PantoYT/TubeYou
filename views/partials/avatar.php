<?php
function renderAvatar(?string $avatar, string $size = '36px'): string
{
    if ($avatar) {
        return '<img src="' . htmlspecialchars($avatar) . '" 
                     alt="avatar" 
                     style="width:' . $size . ';height:' . $size . ';border-radius:50%;object-fit:cover;">';
    }

    return '<span class="avatar-placeholder" style="
        width:' . $size . ';
        height:' . $size . ';
        border-radius:50%;
        background:var(--avatar-bg);
        display:inline-flex;
        align-items:center;
        justify-content:center;
        flex-shrink:0;">
        <img src="/images/icons/user-circle.svg" 
             style="width:60%;height:60%;opacity:0.5;">
    </span>';
}
?>