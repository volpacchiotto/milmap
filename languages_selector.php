<ul class="languages">
    <? foreach (TEXTS['languages'] as $language_code => $language) {
        if ($language_code == $lang) { ?>
            <li class="current"><?= $language ?>
        <? } else { ?>
            <li><a href="?lang=<?= $language_code ?>"><?= $language ?></a>
        <? }
    } ?>
</ul>