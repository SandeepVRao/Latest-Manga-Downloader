<?php
require 'Mangareader.php';
$oMangareader       = new Mangareader();
$oMangareader->path = "mangas/";

if (isset($_POST['manga_id'])) {
    $id           = filter_input(INPUT_POST, 'manga_id');    
    $aux = $oMangareader->getManga($id);
    echo "<a href='".$_SERVER['PHP_URI']."'>back</a>";
    exit();
}
$aSavedMangas = $oMangareader->getSavedMangas();
?>

<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Mangareader.net manga saver</title>
    </head>
    <body>
        <header>
            <hgroup>
                <h1>Mangareader.net</h1>
                <h2>Manga Saver</h2>
            </hgroup>
        </header>
        <section>
            <form method="post">
                <p>Example: http://www.mangareader.net/<strong>fairy-tail/300</strong>, where fairy-tail/300 is the Manga ID</p>                
                <label for="manga_id">Manga ID:</label>
                <input type="text" name="manga_id" />
                <input type="submit" value="search & save" />
            </form>           
        </section>
        <section>
            <header><h3>Saved Mangas</h3></header>
            <? if (is_array($aSavedMangas)): ?>                
                <ul>
                    <? foreach ($aSavedMangas as $k => $iSavedMangas): ?>
                        <li>
                            <h4><?= str_replace("_", "&nbsp;", $k) ?></h4>
                            <ul>
                                <? foreach ($iSavedMangas as $iSavedManga): ?>
                                    <li><a href="<?= $iSavedManga['url'] ?>" name="<?= $iSavedManga['name'] ?>"><?= $iSavedManga['name'] ?></a></li>
                                <? endforeach ?>
                            </ul>
                        </li>                
                    <? endforeach ?>
                </ul>
            <? else:?>
            [No saved mangas]
            <? endif ?>
        </section>

    </body>
</html>
