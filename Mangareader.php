<?php

class Mangareader
{
    /*
     * pattern manga: http://http://www.mangareader.net/battle-angel-alita-last-order/108/2 : 
     * Where "battle-angel-alita-last-order" == manga identifier (id)
     * "108" =
     * "2" el num de página		 
     */

    public $path;
    public $file_manga_name;
    protected $id;
    protected $manga_ep;
    protected $manga_name;
    protected $images = array();
    private $_messages = array(
        "searching"     => "\nSearching:",
        "saving"        => "\nSaving:",
        "processing"    => "[]",
        "overwritting"  => "[!]",
        "connect_error" => "\nUnable to connect to:"
    );

    public function getManga($id)
    {
        $this->id = $id;
        $url      = $this->file_get_contents_curl("http://www.mangareader.net/" . $id . "/");

        if (strlen(url)) {
            $this->setMangaNameAndEp($id);
            $this->write("<strong>Manga:</strong>" . $this->manga_name . " #" . $this->manga_ep);


            $dom     = DOMDocument::loadHTML($url);
            $options = $dom->getElementsByTagName('option');
            foreach ($options as $option) {

                $value   = $option->getAttribute('value');
                $links[] = "http://www.mangareader.net" . $value;
            }

            ksort($links);

            $this->write($this->_messages['searching']);
            foreach ($links as $k => $url) {
                /* GETTING IMAGE URLS */
                $url       = $this->file_get_contents_curl($url);
                $imgpatter = "/<img id=\"img\" (.*) name=\"img\"/";
                preg_match_all($imgpatter, $url, $matches);
                $thing     = explode("src", $matches[0][0]);
                $parts     = explode("\"", $thing[1]);
                $imgs[$k]  = $parts[1];
                $this->write($this->_messages['processing']);
            }

            $this->write("[" . count($imgs) . "]");
            $this->write($this->_messages['saving']);
            $this->images = $imgs;
            $this->saveImages();
            $this->write("[" . count($imgs) . "]");
            $this->zipManga();
            $this->renameManga();
            return $this;
        }
        return false;
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
        $files = array_reverse(glob($this->path . "*/*.cbr", GLOB_MARK));

        if (is_array($files) && count($files)) {
            foreach ($files as $k => $file) {
                $manga_name                                   = explode("/", $file);
                $name                                         = array_pop($manga_name);
                $key                                          = explode("_", $name);
                $aMangas[$key[0] . "_" . $key[1]][$k]['name'] = $name;
                $aMangas[$key[0] . "_" . $key[1]][$k]['url']  = $file;
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

        $this->path = $this->path . $this->manga_name . "/";
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
            $actual = $this->file_get_contents_curl($url);
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

    public function file_get_contents_curl($url)
    {

        $ch   = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

}