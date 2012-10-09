<?php
require 'classes/Savemanga.php';
require 'classes/Savemanga_Factory.php';
require 'classes/Savemanga_Mangareader.php';

$oMangareader       = new Savemanga_Mangareader();
$oMangareader->path = "mangas/";

if (isset($_POST['url'])) {
    $url = filter_input(INPUT_POST, 'url');    
    $object = Savemanga_Factory::getInstanceOf($url);
    var_dump($object);

    /*
      $aux          = $oMangareader->getManga($id);
      echo "<a href='" . $_SERVER['PHP_URI'] . "'>back</a>";     
    */
    exit();
}
$aSavedMangas = $oMangareader->getSavedMangas();
?>

<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Savemanga - manga saver</title>
    </head>
    <body>
        <header>
            <hgroup>
                <h1>Savemanga</h1>
                <h2>Manga Saver</h2>
            </hgroup>
        </header>
        <section>
            <form method="post">
                <p>Example: <strong>http://www.mangareader.net/fairy-tail/300</strong></p>
                <label for="url">Manga Url:</label>
                <input type="text" name="url" />
                <input type="submit" value="search & save" />
            </form>           
        </section>
        <? /*
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
          <? else: ?>
          [No saved mangas]
          <? endif ?>
          </section>
         */ ?>
    </body>
</html>
