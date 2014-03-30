<?php

const API = 'http://%s.wikisource.org/w/api.php';
const COMMONS_API = 'http://commons.wikimedia.org/w/api.php';
const WIKIPATH = 'http://%s.wikisource.org/wiki/%s';

const LANG = 'it';
const BASEPATH = 'Divina Commedia/%s/Canto %s';

const IMG_WIDTH = 1600;
const IMG_HEIGHT = 160;

$titles = [
    'ca' => 'La Divina Comèdia',
    'cs' => 'Božská komedie',
    'en' => 'Divine Comedy',
    'es' => 'La Divina Comedia',
    'fi' => 'Jumalaisesta näytelmästä',
    'fr' => 'La Divine Comédie',
    'it' => 'Divina Commedia',
    'la' => 'Divina Comoedia',
    'pl' => 'Boska Komedia',
    'pt' => 'A Divina Comédia',
    'ro' => 'Divina Comedie',
    'ru' => 'Божественная комедия',
    'sl' => 'Božanska komedija'
];

$forms = ['Flag of %s.svg', 'Nuvola %s flag.svg'];
$flags = [
    'ca' => ['Catalonia',          'Catalonia'],
    'cs' => ['the Czech Republic', 'Czech'],
    'de' => ['Germany',            'German'],
    'el' => ['Greece',             'Greek'],
    'en' => ['the United Kingdom', 'English language'],
    'es' => ['Spain',              'Spain'],
    'et' => ['Estonia',            'Estonian'],
    'fi' => ['Finland',            'Finnish'],
    'fr' => ['France',             'France'],
    'it' => ['Italy',              'Italy'],
    'la' => ['the Vatican City',   'Vatican'], // only Vatican?
    'no' => ['Norway',             'Norwegian'],
    'pl' => ['Poland',             'Polish'],
    'pt' => ['Portugal',           'Portuguese'],
    'ro' => ['Romania',            'Romanian'],
    'ru' => ['Russia',             'Russian'],
    'sl' => ['Slovenia',           'Slovenian'],
    'sv' => ['Sweden',             'Swedish'],
];

function getFlag($lang, $nuvola = true)
{
    global $forms, $flags;
    if (array_key_exists($lang, $flags)) {
        $index = ($nuvola == true ? 1 : 0);
        return str_replace(' ', '_', sprintf($forms[$index], $flags[$lang][$index]));
    }
    return null;
}

/**
  * source:
  * http://blog.stevenlevithan.com/archives/javascript-roman-numeral-converter
  * by Steven Levithan, released under the MIT License
  * ported from JavaScript to PHP by Ricordisamoa
  */
function romanize($num)
{
    $digits = str_split(strval($num));
    $key = array('', 'C', 'CC', 'CCC', 'CD', 'D', 'DC', 'DCC', 'DCCC', 'CM',
        '', 'X', 'XX', 'XXX', 'XL', 'L', 'LX', 'LXX', 'LXXX', 'XC',
        '', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX');
    $roman = '';
    $i = 3;
    while ($i--) {
        $f = intval(array_pop($digits)) + ($i*10);
        $roman = (array_key_exists($f, $key) ? $key[$f] : '').$roman;
    }
    return implode(array_fill(0, intval(implode($digits, ''))+1, ''), 'M').$roman;
}

function getApi($arg1, $arg2 = null)
{
    if ($arg2 != null) {
        $api = $arg1;
        $params = $arg2;
    } else {
        $api = sprintf(API, LANG);
        $params = $arg1;
    }

    $params = http_build_query($params);

    $res = file_get_contents($api . '?' . $params);
    return json_decode($res, true);
}

function compare_langlinks($l1, $l2)
{
    return strcmp($l1['lang'], $l2['lang']);
}

$languages = [];
$languages_query = getApi(
    [
        'action' => 'query',
        'meta'   => 'siteinfo',
        'siprop' => 'languages',
        'format' => 'json'
    ]
);
$languages_query = $languages_query['query']['languages'];
foreach ($languages_query as $language) {
    $languages[$language['code']] = $language['*'];
}

class Orig
{

    public function __construct($orig)
    {
        $this->orig = $orig;
    }
    public function getLanglinks()
    {
        $res = getApi(
            [
                'action'  => 'query',
                'prop'    => 'langlinks',
                'format'  => 'json',
                'lllimit' => 'max',
                'titles'  => $this->orig
            ]
        );
        $res = $res['query']['pages'];
        $res = $res[array_keys($res)[0]]['langlinks'];
        array_push($res, ['lang' => LANG, '*' => $this->orig]);
        $res = array_values($res);        // re-index array
        usort($res, 'compare_langlinks'); // sort by language code
        return $res;
    }

}

class Cantica extends Orig
{
    public $name = false;
    public $lang = false;
    public function __construct($name, $lang = LANG)
    {
        $this->name = $name;
        $this->lang = $lang;
    }

    public function numberOfCantos()
    {
        return in_array($this->name, ['Purgatorio', 'Paradiso']) ? 33 : 34;
    }

    public function getCanto($num)
    {
        return new Canto($this->name, $num, $this->lang);
    }

}

class Canto extends Orig
{

    public function __construct($cantica, $num, $lang = LANG)
    {
        $this->cantica = $cantica;
        $this->num = $num;
        $this->lang = $lang;
        
        $this->commonsCat = 'Category:'.$this->cantica.' Canto '.str_pad($this->num, 2, '0', STR_PAD_LEFT);

        $this->orig = sprintf(BASEPATH, $this->cantica, romanize($num));
        $this->api  = sprintf(API, $this->lang);

        if ($this->lang === LANG) {
            $this->title = $this->orig;
        } else {
            $o   = new Orig($this->orig);
            $lls = $o->getLanglinks();
            foreach ($lls as $i => $ll) {
                if ($ll['lang'] === $this->lang) {
                    $this->title = $ll['*'];
                    break;
                }
            }
        }
        $this->url = sprintf(WIKIPATH, $this->lang, implode('/', array_map('rawurlencode', explode('/', str_replace(' ', '_', $this->title)))));
    }

    public function getContent()
    {
        $query = getApi(
            $this->api,
            [
                'action'  => 'query',
                'format'  => 'json',
                'titles'  => $this->title,
                'prop'    => 'revisions',
                'rvprop'  => 'content',
                'rvlimit' => 1
            ]
        );
        $query = $query['query'];
        if (array_key_exists('pages', $query)) {
            foreach ($query['pages'] as $pageid => $page) {
                if (array_key_exists('revisions', $page)) {
                    return $page['revisions'][0]['*'];
                } else {
                    return;
                }
            }
        }
    }

    public function getCleanContent()
    {

        $content = $this->getContent();

        // get only text in "<poem>" tags
        $content = preg_replace('/(^[\s\S]*<poem>[\s\n\r]*|[\s\n\r]*<\/poem>[\s\S]*$)/i', '', $content);

        // remove images (TODO: expect any possible ns-6 alias)
        $content = preg_replace('/\[\[\:?([Ff]ile|[Ii]mat?ge|[Ii]mmagine)\:[^\[\]]+(\[\[[^\[\]]+\]\][^\[\]]+)*\]\]\n/', '', $content);

        // other languages
        $content = preg_replace('/^[\s\S]*<div class="verse"><pre>\s+/i', '', $content);
        $content = preg_replace('/\s+<\/pre><\/div>[\s\S]*$/i', '', $content);

        // strip <ref> tags
        $content = preg_replace('/<ref[\s\w]*(\/|>[^<>]*<\/ref)>/i', '', $content);

        // remove indentations at line beginning
        $content = preg_replace('/^[:\d\s\']*/m', '', $content);

        // remove final italic marks from Latin text
        if ($this->lang === 'la') {
            $content = preg_replace('/\'+\n/', '\n', $content);
        }

        // $templates='§|R|r|[Cc]ommentItem|[Aa]utoreCitato'; CURRENTLY IN TESTING
        $templates = '[\w\§]+';

        // remove unprintable templates
        $content = preg_replace('/\{\{([Oo]tsikko|[Ee]ncabezado|[Tt]itulus2)\n*\|[^\|\{\}]+(\|([^\|\{\}]+))*\}\}/', '', $content);
        $content = preg_replace('/\{\{('.$templates.')\n*\|[^\|\{\}]+\}\}/', '', $content);

        // replace some templates with printable parts
        $content = preg_replace('/\{\{('.$templates.')\n*\|[^\|\{\}]+\|([^\|\{\}]+)\}\}/', '$2', $content);

        // remove initial and final spaces
        $content = preg_replace('/(^[\s\n\r]+|[\s\n\r]+$)/', '', $content);

        // remove superfluous line-breaks
        $content = preg_replace('/\s*(<br\s?\/?>\s*)*\n+/', '\n', $content);

        return $content;
    }

    public function getLines($begin = null, $end = null)
    {
        // split the text into lines
        $content = $this->getCleanContent();
        $lines = explode('\n', $content);
        if ($begin != null and $end != null) {
            // select desired lines only
            if ($begin > $end) {
                die('Error: $begin cannot be greater than $end');
            }
            if ($end > count($lines)) {
                die(sprintf('Error: exceeded number of lines in this canto: %d', count($content)));
            }
            $lines = array_slice($lines, $begin-1, $end-$begin+1);
        }
        return $lines;
    }

    public function numberOfLines()
    {
        return count($this->getLines());
    }

    public function getImages()
    {
        $images = getApi(
            COMMONS_API,
            [
                'action'      => 'query',
                'format'      => 'json',
                'prop'        => 'imageinfo',
                'iiprop'      => 'url',
                'iiurlwidth'  => IMG_WIDTH,
                'iiurlheight' => IMG_HEIGHT,
                'generator'   => 'categorymembers',
                'gcmtitle'    => $this->commonsCat,
                'gcmtype'     => 'file'
            ]
        );
        $images = $images['query'];
        $res = [];
        if (array_key_exists('pages', $images)) {
            foreach ($images['pages'] as $pageid => $page) {
                $k = $page['imageinfo'][0];
                $k['title'] = $page['title'];
                array_push($res, $k);
            }
        }
        return $res;
    }

}

?>
