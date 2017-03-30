<?php

/**
 * moziloCMS Plugin: TaskGen
 *
 * A generator for various multiple exercises of different subjects.
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   DEVMOUNT <mail@devmount.de>
 * @license  GPL v3+
 * @version  GIT: v0.1.2014-08-25
 * @link     https://github.com/devmount-mozilo/TaskGen/wiki/Dokumentation
 * @see      So do not fear, for I am with you; do not be dismayed, for I am your
 *           God. I will strengthen you and help you; I will uphold you with my
 *           righteous right hand.
 *           – The Bible
 *
 * Plugin created by DEVMOUNT
 * www.devmount.de
 *
 */

// only allow moziloCMS environment
if (!defined('IS_CMS')) {
    die();
}

/**
 * TaskGen Class
 *
 * @category PHP
 * @package  PHP_MoziloPlugins
 * @author   DEVMOUNT <mail@devmount.de>
 * @license  GPL v3+
 * @link     https://github.com/devmount-mozilo/TaskGen
 */
class TaskGen extends Plugin
{
    // language
    private $_admin_lang;
    private $_cms_lang;

    // plugin information
    const PLUGIN_AUTHOR  = 'DEVMOUNT';
    const PLUGIN_TITLE   = 'TaskGen';
    const PLUGIN_VERSION = 'v0.1.2014-08-25';
    const MOZILO_VERSION = '2.0';
    const PLUGIN_DOCU
        = 'https://github.com/devmount-mozilo/TaskGen/wiki/Dokumentation';

    private $_plugin_tags = array(
        'tag1' => '{TaskGen}',
    );

    const LOGO_URL = 'http://media.devmount.de/logo_pluginconf.png';

    /**
     * set configuration elements, their default values and their configuration
     * parameters
     *
     * @var array $_confdefault
     *      text     => default, type, maxlength, size, regex
     *      textarea => default, type, cols, rows, regex
     *      password => default, type, maxlength, size, regex, saveasmd5
     *      check    => default, type
     *      radio    => default, type, descriptions
     *      select   => default, type, descriptions, multiselect
     */
    private $_confdefault = array(
        // 'text' => array(
        //     'string',
        //     'text',
        //     '100',
        //     '5',
        //     "/^[0-9]{1,3}$/",
        // ),
        // 'textarea' => array(
        //     'string',
        //     'textarea',
        //     '10',
        //     '10',
        //     "/^[a-zA-Z0-9]{1,10}$/",
        // ),
        // 'password' => array(
        //     'string',
        //     'password',
        //     '100',
        //     '5',
        //     "/^[a-zA-Z0-9]{8,20}$/",
        //     true,
        // ),
        // 'check' => array(
        //     true,
        //     'check',
        // ),
        // 'radio' => array(
        //     'red',
        //     'radio',
        //     array('red', 'green', 'blue'),
        // ),
        // 'select' => array(
        //     'bike',
        //     'select',
        //     array('car','bike','plane'),
        //     false,
        // ),
    );

    /**
     * creates plugin content
     *
     * @param string $value Parameter divided by '|'
     *
     * @return string HTML output
     */
    function getContent($value)
    {
        global $CMS_CONF;
        global $syntax;

        // initialize cms lang
        $this->_cms_lang = new Language(
            $this->PLUGIN_SELF_DIR
            . 'lang/cms_language_'
            . $CMS_CONF->get('cmslanguage')
            . '.txt'
        );

        // get params
        $mode = $value; // TODO: build modes

        // get conf and set default
        $conf = array();
        foreach ($this->_confdefault as $elem => $default) {
            $conf[$elem] = ($this->settings->get($elem) == '')
                ? $default[0]
                : $this->settings->get($elem);
        }

        // initialize return content, begin plugin content
        $content = '<!-- BEGIN ' . self::PLUGIN_TITLE . ' plugin content --> ';

        $content .= '<div class="taskgen">';

        // build form
        $content .= '
            <h2>Konfiguration</h2>
            <div class="section"><div>
                <form name="taskgen-form" action="#result" method="post">
                    <h3>1. Faktor | Divisor</h3>
                    Zahl zwischen <input type="number" name="min-a" value="'
                        . getRequestValue('min-a')
                    . '" required />
                    und <input type="number" name="max-a" value="'
                        . getRequestValue('max-a')
                    . '" required />
                    <h3>2. Faktor | Quotient</h3>
                    Zahl zwischen <input type="number" name="min-b" value="'
                        . getRequestValue('min-b')
                    . '" required />
                    und <input type="number" name="max-b" value="'
                        . getRequestValue('max-b')
                    . '" required />
                    <h3>Anzahl der Aufgaben</h3>
                    <input type="number" name="tasks" value="'
                        . getRequestValue('tasks')
                    . '" min="1" required />
                    <input type="submit" name="taskgen" value="Start" />
                </form>
            </div></div>
        ';

        // handle input and build exercises
        if (getRequestValue('taskgen') != '') {
            // get configuration data
            $ranges = array(
                'a1' => getRequestValue('min-a'),
                'a2' => getRequestValue('max-a'),
                'b1' => getRequestValue('min-b'),
                'b2' => getRequestValue('max-b'),
            );
            $number = getRequestValue('tasks');
            $message = '';
            // handle too high task number
            $maxcount = (abs($ranges['a1']-$ranges['a2'])+1)*(abs($ranges['a1']-$ranges['a2'])+1);
            if ($number > $maxcount) {
                $number = $maxcount;
                $message = 'Maximale Aufgabenzahl erreicht (' . $maxcount . ' Kombinationen möglich)';
            }
            // initialize task lists
            $multasks = array();
            $divtasks = array();
            // multiply
            $content .= '<br />';
            $content .= '<h2 id="result">Ausgabe</h2>';
            $content .= '<div class="section"><div>';
            $content .= '<table><tr><td>';
            $content .= '<h3>Multiplikation</h3>';
            $content .= '<pre>';
            while (count($multasks) < $number) {
                $a = rand($ranges['a1'], $ranges['a2']);
                $b = rand($ranges['b1'], $ranges['b2']);
                $s = $a*$b;
                $singletask = $a . ' &middot; ' . $b . ' = ' . $s;
                if (!in_array($singletask, $multasks)) {
                    $multasks[] = $singletask;
                }
            }
            // add tasks to content
            foreach ($multasks as $task) {
                $content .= $task . '<br />';
            }
            $content .= '</pre>';
            $content .= '</td>';

            // divide
            $content .= '<td>';
            $content .= '<h3>Division</h3>';
            $content .= '<pre>';
            while (count($divtasks) < $number) {
                $a = rand($ranges['a1'], $ranges['a2']);
                $b = rand($ranges['b1'], $ranges['b2']);
                $s = $a*$b;
                // check division by zero
                if ($b == 0) {
                    $s = $a;
                    $a = 'n.l.';
                }
                $singletask = $s . ' : ' . $b . ' = ' . $a;
                if (!in_array($singletask, $divtasks)) {
                    $divtasks[] = $singletask;
                }
            }
            // add tasks to content
            foreach ($divtasks as $task) {
                $content .= $task . '<br />';
            }
            $content .= '</pre>';
            $content .= '</td></tr></table>';
            $content .= $message;
            $content .= '</div></div>';
            $content .= '<br />Alle Angaben und Ergebnisse ohne Gewähr.';
        }

        $content .= '</div>';

        // end plugin content
        $content .= '<!-- END ' . self::PLUGIN_TITLE . ' plugin content --> ';

        return $content;
    }

    /**
     * sets backend configuration elements and template
     *
     * @return Array configuration
     */
    function getConfig()
    {
        $config = array();

        // read configuration values
        foreach ($this->_confdefault as $key => $value) {
            // handle each form type
            switch ($value[1]) {
            case 'text':
                $config[$key] = $this->confText(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    )
                );
                break;

            case 'textarea':
                $config[$key] = $this->confTextarea(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    )
                );
                break;

            case 'password':
                $config[$key] = $this->confPassword(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $value[2],
                    $value[3],
                    $value[4],
                    $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_error'
                    ),
                    $value[5]
                );
                break;

            case 'check':
                $config[$key] = $this->confCheck(
                    $this->_admin_lang->getLanguageValue('config_' . $key)
                );
                break;

            case 'radio':
                $descriptions = array();
                foreach ($value[2] as $label) {
                    $descriptions[$label] = $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_' . $label
                    );
                }
                $config[$key] = $this->confRadio(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $descriptions
                );
                break;

            case 'select':
                $descriptions = array();
                foreach ($value[2] as $label) {
                    $descriptions[$label] = $this->_admin_lang->getLanguageValue(
                        'config_' . $key . '_' . $label
                    );
                }
                $config[$key] = $this->confSelect(
                    $this->_admin_lang->getLanguageValue('config_' . $key),
                    $descriptions,
                    $value[3]
                );
                break;

            default:
                break;
            }
        }

        // read admin.css
        $admin_css = '';
        $lines = file('../plugins/' . self::PLUGIN_TITLE. '/admin.css');
        foreach ($lines as $line_num => $line) {
            $admin_css .= trim($line);
        }

        // add template CSS
        $template = '<style>' . $admin_css . '</style>';

        // build Template
        // $template .= '
        //     <div class="taskgen-admin-header">
        //     <span>'
        //         . $this->_admin_lang->getLanguageValue(
        //             'admin_header',
        //             self::PLUGIN_TITLE
        //         )
        //     . '</span>
        //     <a href="' . self::PLUGIN_DOCU . '" target="_blank">
        //     <img style="float:right;" src="' . self::LOGO_URL . '" />
        //     </a>
        //     </div>
        // </li>
        // <li class="mo-in-ul-li ui-widget-content taskgen-admin-li">
        //     <div class="taskgen-admin-subheader">'
        //     . $this->_admin_lang->getLanguageValue('admin_test')
        //     . '</div>
        //     <div class="taskgen-single-conf">
        //         {test1_text}
        //         {test1_description}
        //         <span class="taskgen-admin-default">
        //             [' . /*$this->_confdefault['test1'][0] .*/']
        //         </span>
        //     </div>
        //     <div class="taskgen-single-conf">
        //         {test2_text}
        //         {test2_description}
        //         <span class="taskgen-admin-default">
        //             [' . /*$this->_confdefault['test2'][0] .*/']
        //         </span>
        // ';

        // $config['--template~~'] = $template;

        return $config;
    }

    /**
     * sets default backend configuration elements, if no plugin.conf.php is
     * created yet
     *
     * @return Array configuration
     */
    function getDefaultSettings()
    {
        $config = array('active' => 'true');
        foreach ($this->_confdefault as $elem => $default) {
            $config[$elem] = $default[0];
        }
        return $config;
    }

    /**
     * sets backend plugin information
     *
     * @return Array information
     */
    function getInfo()
    {
        global $ADMIN_CONF;

        $this->_admin_lang = new Language(
            $this->PLUGIN_SELF_DIR
            . 'lang/admin_language_'
            . $ADMIN_CONF->get('language')
            . '.txt'
        );

        // build plugin tags
        $tags = array();
        foreach ($this->_plugin_tags as $key => $tag) {
            $tags[$tag] = $this->_admin_lang->getLanguageValue('tag_' . $key);
        }

        $info = array(
            '<b>' . self::PLUGIN_TITLE . '</b> ' . self::PLUGIN_VERSION,
            self::MOZILO_VERSION,
            $this->_admin_lang->getLanguageValue(
                'description',
                htmlspecialchars($this->_plugin_tags['tag1'], ENT_COMPAT, 'UTF-8')
            ),
            self::PLUGIN_AUTHOR,
            array(
                self::PLUGIN_DOCU,
                self::PLUGIN_TITLE . ' '
                . $this->_admin_lang->getLanguageValue('on_devmount')
            ),
            $tags
        );

        return $info;
    }

    /**
     * creates configuration for text fields
     *
     * @param string $description Label
     * @param string $maxlength   Maximum number of characters
     * @param string $size        Size
     * @param string $regex       Regular expression for allowed input
     * @param string $regex_error Wrong input error message
     *
     * @return Array  Configuration
     */
    protected function confText(
        $description,
        $maxlength = '',
        $size = '',
        $regex = '',
        $regex_error = ''
    ) {
        // required properties
        $conftext = array(
            'type' => 'text',
            'description' => $description,
        );
        // optional properties
        if ($maxlength != '') {
            $conftext['maxlength'] = $maxlength;
        }
        if ($size != '') {
            $conftext['size'] = $size;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        if ($regex_error != '') {
            $conftext['regex_error'] = $regex_error;
        }
        return $conftext;
    }

    /**
     * creates configuration for textareas
     *
     * @param string $description Label
     * @param string $cols        Number of columns
     * @param string $rows        Number of rows
     * @param string $regex       Regular expression for allowed input
     * @param string $regex_error Wrong input error message
     *
     * @return Array  Configuration
     */
    protected function confTextarea(
        $description,
        $cols = '',
        $rows = '',
        $regex = '',
        $regex_error = ''
    ) {
        // required properties
        $conftext = array(
            'type' => 'textarea',
            'description' => $description,
        );
        // optional properties
        if ($cols != '') {
            $conftext['cols'] = $cols;
        }
        if ($rows != '') {
            $conftext['rows'] = $rows;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        if ($regex_error != '') {
            $conftext['regex_error'] = $regex_error;
        }
        return $conftext;
    }

    /**
     * creates configuration for password fields
     *
     * @param string  $description Label
     * @param string  $maxlength   Maximum number of characters
     * @param string  $size        Size
     * @param string  $regex       Regular expression for allowed input
     * @param string  $regex_error Wrong input error message
     * @param boolean $saveasmd5   Safe password as md5 (recommended!)
     *
     * @return Array   Configuration
     */
    protected function confPassword(
        $description,
        $maxlength = '',
        $size = '',
        $regex = '',
        $regex_error = '',
        $saveasmd5 = true
    ) {
        // required properties
        $conftext = array(
            'type' => 'text',
            'description' => $description,
        );
        // optional properties
        if ($maxlength != '') {
            $conftext['maxlength'] = $maxlength;
        }
        if ($size != '') {
            $conftext['size'] = $size;
        }
        if ($regex != '') {
            $conftext['regex'] = $regex;
        }
        $conftext['saveasmd5'] = $saveasmd5;
        return $conftext;
    }

    /**
     * creates configuration for checkboxes
     *
     * @param string $description Label
     *
     * @return Array  Configuration
     */
    protected function confCheck($description)
    {
        // required properties
        return array(
            'type' => 'checkbox',
            'description' => $description,
        );
    }

    /**
     * creates configuration for radio buttons
     *
     * @param string $description  Label
     * @param string $descriptions Array Single item labels
     *
     * @return Array Configuration
     */
    protected function confRadio($description, $descriptions)
    {
        // required properties
        return array(
            'type' => 'select',
            'description' => $description,
            'descriptions' => $descriptions,
        );
    }

    /**
     * creates configuration for select fields
     *
     * @param string  $description  Label
     * @param string  $descriptions Array Single item labels
     * @param boolean $multiple     Enable multiple item selection
     *
     * @return Array   Configuration
     */
    protected function confSelect($description, $descriptions, $multiple = false)
    {
        // required properties
        return array(
            'type' => 'select',
            'description' => $description,
            'descriptions' => $descriptions,
            'multiple' => $multiple,
        );
    }

    /**
     * throws styled message
     *
     * @param string $type Type of message ('ERROR', 'SUCCESS')
     * @param string $text Content of message
     *
     * @return string HTML content
     */
    protected function throwMessage($text, $type)
    {
        return '<div class="'
                . strtolower(self::PLUGIN_TITLE . '-' . $type)
            . '">'
            . '<div>'
                . $this->_cms_lang->getLanguageValue(strtolower($type))
            . '</div>'
            . '<span>' . $text. '</span>'
            . '</div>';
    }

}

?>
