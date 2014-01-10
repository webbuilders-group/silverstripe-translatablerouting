<?php
if (php_sapi_name() != 'cli') {
    header('HTTP/1.0 404 Not Found');
    exit;
}

$opts = getopt('', array(
	'target:', // required
));

// Sanity checks
if(!$opts || !isset($opts['target'])) {
	echo "Invalid arguments specified\n";
	exit(1);
}

$targetPath=$opts['target'];
if(!file_exists("$targetPath/mysite/code/Page.php")) {
    echo "Cannot find Page class in mysite\n";
    exit(1);
}

$pageDOContent='
    public function Link($action=null) {
        return Controller::join_links(Director::baseURL(), (Config::inst()->get(\'MultilingualRootURLController\', \'UseLocaleURL\') ? $this->Locale:i18n::get_lang_from_locale($this->Locale)), $this->RelativeLink($action));
    }
    
    public function RelativeLink($action=null) {
        if($this->ParentID && self::nested_urls()) {
            $base=$this->Parent()->RelativeLink($this->URLSegment);
        } else {
            $base=$this->URLSegment;
        }

        //Unset base for homepage URLSegments in their default language. Homepages with action parameters or in different languages need to retain their URLSegment. We can only do this if the homepage is on the root level.
        if(!$action && $base==Translatable::get_homepage_link_by_locale($this->Locale) && !$this->ParentID) {
            $base = null;
        }

        return Controller::join_links($base, \'/\', $action);
    }
}

class Page_';

$pageControllerContent='
        if($this->dataRecord && $this->dataRecord instanceof SiteTree && MultilingualRootURLController::should_be_on_root($this->dataRecord) && (!isset($this->urlParams[\'Action\']) || !$this->urlParams[\'Action\']) && !$_POST && !$_FILES && !$this->redirectedTo()) {
            $getVars=$_GET;
            unset($getVars[\'url\']);

            if($getVars) {
                $url=(Config::inst()->get(\'MultilingualRootURLController\', \'UseLocaleURL\') ? $this->Locale:i18n::get_lang_from_locale($this->Locale)).\'/?\'.http_build_query($getVars);
            }else {
                $url=(Config::inst()->get(\'MultilingualRootURLController\', \'UseLocaleURL\') ? $this->Locale:i18n::get_lang_from_locale($this->Locale)).\'/\';
            }

            $this->redirect($url, 301);
            return;
        }';

$pageContents=file_get_contents("$targetPath/mysite/code/Page.php");
$pageContents=preg_replace('/\}(.*?)class Page_/s', $pageDOContent, $pageContents);
$pageContents=preg_replace('/public function init\(\) \{(.*?)\}(.*?)\}/s', 'public function init() {$1 '.$pageControllerContent."\n}\n}", $pageContents);

$f=fopen("$targetPath/mysite/code/Page.php", 'w');
fwrite($f, $pageContents);
fclose($f);
?>