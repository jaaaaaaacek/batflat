<?php

namespace Inc\Core\Lib;

class Translation
{
    public $currentLang;
    public $languages = [];
    public $messages = [];
    protected $core;
    private $dev = false;

    function __construct($core)
    {
        $this->core = $core;
        if(!isset($_GET['lang'])) {
            if (!isset($_SESSION['lang'])) {
                $this->currentLang = $core->settings->get('settings.lang_site');
            } else {
                $this->currentLang = $_SESSION['lang'];
            }
        }
        else {
            $this->currentLang = $_GET['lang'];
        }


        $langs = $this->core->db('tr_languages')->toArray();

        foreach ($langs as $key => $lang) {

            $langs[$key]['url'] = '?lang='.$lang['name'];

            if ($lang['name'] === $this->currentLang) {
                $langs[$key]['active'] = true;
            }
            else {
                $langs[$key]['active'] = false;
            }

        }

        $this->languages = $langs;
    }

    public function createFields($element_id,$table,$field,$lang = null) {
        if ($lang == null) {
            foreach ($this->languages as $language) {
                $data = [
                    'element_id' => $element_id,
                    '"table"' => $table,
                    'field' => $field,
                    'lang' => $language['name']
                ];

                $this->core->db('tr_translation')->save($data);
            }
        }
        else {
            $data = [
                'element_id' => $element_id,
                '"table"' => $table,
                'field' => $field,
                'lang' => $lang
            ];

            $this->core->db('tr_translation')->save($data);
        }
    }

    public function removeFields($element_id,$table) {
        foreach ($this->languages as $language) {
            $this->core->db('tr_translation')
                ->where('element_id',$element_id)
                ->where('"table"',$table)
                ->delete();
        }
    }

    public function saveTranslation($element_id,$table,$field,$lang,$content) {
        return $this->core->db('tr_translation')
            ->where('element_id',$element_id)
            ->where('"table"',$table)
            ->where('field',$field)
            ->where('lang',$lang)
            ->save(['content' => $content]);
    }

    public function getElementBySlug($table, $slug, $lang = null) {
        if ($lang == null) {
            $lang = $this->currentLang;
        }

        $translation = $this->core->db('tr_translation')
            ->where('"table"',$table)
            ->where('field','slug')
            ->where('lang',$lang)
            ->where('content',$slug)
            ->oneArray();
        if ($translation) {
            return $translation['element_id'];
        }
        else {
            $element = $this->core->db($table)->where('slug', $slug)->oneArray();
            return $element['id'];
        }
    }

    public function translate($table, $item, $lang = null) {
        if ($lang == null) {
            $lang = $this->currentLang;
        }
        if ($item) {
            foreach ($item as $key => $fields) {
                $translation = $this->core->db('tr_translation')
                    ->where('element_id', $item['id'])
                    ->where('"table"', $table)
                    ->where('field', $key)
                    ->where('lang', $lang)
                    ->oneArray();

                if ($key == $translation['field']) {
                    $item[$key] = $translation['content'];
                }
                else {
                    $this->messages[] = 'This element does not have a translation '.$key;
                }

            }
            return $item;
        }
        $this->messages[] = 'Element does not exist';
    }

    function __destruct() {
        if (!empty($this->messages)) {
            echo $this->notify();
        }
    }

    public function notify() {
        if ($this->dev) {
            return '<div style="position: absolute; z-index: 1000; height: 100px; overflow: auto; top: 0; width: 100%;"><div class="container"><div class="alert alert-danger">Translation:<br>'.implode(',<br>',$this->messages).'</div></div></div>';
        }
    }
}
