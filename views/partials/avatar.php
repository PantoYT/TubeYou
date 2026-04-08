<?php
function renderAvatar(?string $avatar, string $size = '36px', ?string $href = null): string
{
    $img = $avatar
        ? '<img src="' . htmlspecialchars($avatar) . '" alt="avatar" 
               style="width:' . $size . ';height:' . $size . ';border-radius:50%;object-fit:cover;">'
        : '<span class="avatar-placeholder" style="width:' . $size . ';height:' . $size . ';
               border-radius:50%;background:var(--avatar-bg);display:inline-flex;
               align-items:center;justify-content:center;flex-shrink:0;">
               <img src="/images/icons/user-circle.svg" style="width:60%;height:60%;opacity:0.5;">
           </span>';

    if ($href) {
        return '<a href="' . htmlspecialchars($href) . '" style="display:inline-flex;border-radius:50%;">' . $img . '</a>';
    }
    return $img;
}
?>