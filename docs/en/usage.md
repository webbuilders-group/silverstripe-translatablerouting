Usage
=================
You must add the following methods to your Page class for this module to function properly.

```php
/**
 * Return the link for this {@link SiteTree} object, with the {@link Director::baseURL()} included.
 * @param {string} $action Optional controller action (method). Note: URI encoding of this parameter is applied automatically through template casting, don't encode the passed parameter. Please use {@link Controller::join_links()} instead to append GET parameters.
 * @return {string}
 */
public function Link($action=null) {
    return Controller::join_links(Director::baseURL(), (Config::inst()->get('MultilingualRootURLController', 'UseLocaleURL') ? (Config::inst()->get('MultilingualRootURLController', 'UseDashLocale') ? str_replace('_', '-', strtolower($this->Locale)):$this->Locale):i18n::get_lang_from_locale($this->Locale)), $this->RelativeLink($action));
}

/**
 * Return the link for this {@link SiteTree} object relative to the SilverStripe root. By default, it this page is the current home page, and there is no action specified then this will return a link to the root of the site. However, if you set the $action parameter to TRUE then the link will not be rewritten
 * and returned in its full form.
 * @param {string} $action See {@link Link()}
 * @return {string}
 *
 * @uses Translatable::get_homepage_link_by_locale()
 */
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

    return Controller::join_links($base, '/', $action);
}
```

As well for your Page_Controller class you must add this for this module to function properly.
```php
public function init() {
    parent::init();


    // If we've accessed the homepage as /home/, then we should redirect to /.
    if($this->dataRecord && $this->dataRecord instanceof SiteTree && MultilingualRootURLController::should_be_on_root($this->dataRecord) && (!isset($this->urlParams['Action']) || !$this->urlParams['Action']) && !$_POST && !$_FILES && !$this->redirectedTo()) {
        $getVars=$_GET;
        unset($getVars['url']);

        if($getVars) {
            $url=(Config::inst()->get('MultilingualRootURLController', 'UseLocaleURL') ? (Config::inst()->get('MultilingualRootURLController', 'UseDashLocale') ? str_replace('_', '-', strtolower($this->Locale)):$this->Locale):i18n::get_lang_from_locale($this->Locale)).'/?'.http_build_query($getVars);
        }else {
            $url=(Config::inst()->get('MultilingualRootURLController', 'UseLocaleURL') ? (Config::inst()->get('MultilingualRootURLController', 'UseDashLocale') ? str_replace('_', '-', strtolower($this->Locale)):$this->Locale):i18n::get_lang_from_locale($this->Locale)).'/';
        }

        $this->redirect($url, 301);
        return;
    }
}
```
