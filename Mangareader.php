<?php

class Model_Mangareader
{
    /*
     * pattern manga: http://http://www.mangareader.net/battle-angel-alita-last-order/108/2 : 
     * Where "battle-angel-alita-last-order" == manga identifier (id)
     * "108" =
     * "2" el num de página		 
     */

    protected $_fields = array(
        'id_mangareader',
        'date_created',
        'date_modified',
        'active',
        'cod_lang',
        'mangareader',
    );
    protected $id;
    protected $manga_ep;
    protected $manga_name;
    protected $file_manga_name;
    protected $path;
    protected $images = array();
    protected $_tables = array(
        'main'     => 'mangareader',
        'i18n'     => 'mangareader_i18n',
    );
    private $_messages = array(
        "searching"     => "<br/><strong>C:</strong>",
        "saving"        => "<br/><strong>S:</strong>",
        "processing"    => "[]",
        "overwritting"  => "[!]",
        "connect_error" => "No se ha podido conectar buscando el url: "
    );

    public function getManga($id)
    {
        $this->id = $id;
        $url      = file_get_contents_curl("http://www.mangareader.net/" . $id . "/");

        /* SACAR URLS */
        $urlsPattern = "/" . str_replace("/", "\/", $id) . "\/(\d+)?/";
        preg_match_all($urlsPattern, $url, $matchesURL);
        $urls        = $matchesURL[0];
        $urls        = array_unique($urls);
        foreach ($urls as $url) {
            $forPage = explode("/", $url);
            if (isset($forPage[2])) {
                $auxUrls[$forPage[2]] = $url;
                $forNameAndEp         = $url;
            }
        }
        ksort($auxUrls);
        $urls                 = $auxUrls;

        $this->setMangaNameAndEp($forNameAndEp);
        $this->write("<strong>Manga:</strong>" . $this->manga_name . " #" . $this->manga_ep);
        $this->write($this->_messages['searching']);


        foreach ($urls as $k => $url) {
            // CONSEGUIR URLS DE IMAGENES						
            $url          = file_get_contents_curl("http://www.mangareader.net/" . $url);
            $imgpatter    = "/<img id=\"img\" (.*) name=\"img\"/";
            preg_match_all($imgpatter, $url, $matches);
            $thing        = explode("src", $matches[0][0]);
            $parts        = explode("\"", $thing[1]);
            $imgs[$k]     = $parts[1];
            $this->write($this->_messages['processing']);
        }
        $this->write("[" . count($imgs) . "]");
        $this->write($this->_messages['saving']);
        $this->images = $imgs;
        $this->saveImages();

        $this->zipManga();
        $this->renameManga();
        return $this;
    }

    private function setMangaNameAndEp($url)
    {

        if (strlen(trim($url))) {
            $aux              = explode("/", $url);
            $name             = trim($aux[0]);
            $name             = str_replace(" ", "_", ucwords(strtolower(str_replace("-", " ", $name))));
            $this->manga_name = $name;


            $this->manga_ep = $aux[1];

            if ($this->manga_ep < 10) {
                $this->manga_ep = "00" . $this->manga_ep;
            } else if ($this->manga_ep < 100) {
                $this->manga_ep        = "0" . $this->manga_ep;
            }
            $this->file_manga_name = $this->manga_name . "_" . $this->manga_ep . ".cbr";

            return true;
        }
        return false;
    }

    public function getSavedMangas()
    {
        $files = array_reverse(glob("_files/mangareader/*/*.cbr", GLOB_MARK));

        if (is_array($files) && count($files)) {
            foreach ($files as $k => $file) {
                $manga_name                          = explode("/", $file);
                $aMangas[$manga_name[2]][$k]['name'] = $manga_name[3];
                $aMangas[$manga_name[2]][$k]['url']  = PATH_ABS . $file;
            }
            ksort($aMangas);
        }
        return (isset($aMangas)) ? $aMangas : false;
    }

    private function zipManga()
    {
        $dest_zip_file = $this->path . $this->manga_ep . ".zip";
        file_put_contents($dest_zip_file, "");
        $zip           = new ZipArchive;
        if ($zip->open($dest_zip_file) === TRUE) {
            foreach (glob($this->path . "*.jpg") as $filename) {
                $destfile = array_pop(explode("/", $filename));
                $zip->addFile($filename, $destfile);
            }

            $result = $zip->close();
            if ($result) {
                foreach (glob($this->path . "*.jpg") as $filename) {
                    unlink($filename);
                }
                $this->write("<br/>Ok<br/>");
                return true;
            } else {
                $this->write("<br/>error en compresion - no se han borrado los ficheros");
                return false;
            }
        } else {
            $this->write("<br/>Fallo");
            return false;
        }
    }

    public function renameManga()
    {
        return rename($this->path . $this->manga_ep . ".zip", $this->path . $this->file_manga_name);
    }

    private function saveImages()
    {

        $this->path = "_files/mangareader/" . $this->manga_name . "/";
        if (!is_dir($this->path)) {
            mkdir($this->path, 0777);
        }

        if (is_array($this->images)) {
            foreach ($this->images as $k => $imagen) {
                set_time_limit(0);

                $page    = ($k < 10) ? "0" . $k : $k;
                $destino = $this->path . $page . ".jpg";
                if (!$this->saveImage($imagen, $destino)) {
                    $this->write("<br/>Petada al guardar la imagen: " . $imagen);
                    return false;
                }
                set_time_limit(0);
                if (($k) == count($this->images)) {
                    $this->write("[" . ($k) . "]");
                }
            }
            return true;
        } else {
            $this->write("<br/>No se han encontrado imágenes o mangareader.net ha tardado demasiado en contestar");
            return false;
        }
    }

    private function saveImage($url, $destino)
    {
        if (!file_exists($destino)) {
            set_time_limit(0);
            $actual = file_get_contents_curl($url);
            if (strlen(trim($actual))) {
                file_put_contents($destino, $actual);
                $this->write($this->_messages['processing']);
                return true;
            }
            return false;
        }
        $this->write($this->_messages['overwritting']);
        return true;
    }

    private function write($text)
    {
        echo $text;
        flush();
    }

}