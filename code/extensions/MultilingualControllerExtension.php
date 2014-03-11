<?php
class MultilingualControllerExtension extends Extension {
    /**
     * Handles enabling translations for controllers that are not pages
     */
    public function onBeforeInit() {
        //Bail for the root url controller and model as controller classes as they handle this internally, also disable for development admin and cms
        if($this->owner instanceof MultilingualRootURLController || $this->owner instanceof MultilingualModelAsController || $this->owner instanceof LeftAndMain || $this->owner instanceof DevelopmentAdmin || $this->owner instanceof TestRunner) {
            return;
        }


        //Bail for pages since this would have been handled by MultilingualModelAsController, we're assuming that data has not been set to a page by other code
        if(method_exists($this->owner, 'data') && $this->owner->data() instanceof SiteTree) {
            return;
        }
        
        
        //Check if the locale is in the url
        $request=$this->owner->getRequest();
        if($request && $request->param('Language')) {
            $language=$request->param('Language');
            
            if(Config::inst()->get('MultilingualRootURLController', 'UseLocaleURL')) {
                $locale=$language;
            }else if(strpos($request->param('Language'), '_')!==false) {
                //Invalid format so redirect to the default
                $url=$request->getURL(true);
                $default=(Config::inst()->get('MultilingualRootURLController', 'UseLocaleURL') ? Translatable::default_locale():Translatable::default_lang());
                
                $this->owner->redirect(preg_replace('/^'.preg_quote($language, '/').'\//', $default.'/', $url), 301);
                return;
            }else {
                $locale=i18n::get_locale_from_lang($language);
            }
            
            if(in_array($locale, Translatable::get_allowed_locales())) {
                //Set the language cookie
                Cookie::set('language', $language);
                
                
                //Set the various locales
                Translatable::set_current_locale($locale);
                i18n::set_locale($locale);
            }else {
                //Unknown language so redirect to the default
                $url=$request->getURL(true);
                $default=(Config::inst()->get('MultilingualRootURLController', 'UseLocaleURL') ? Translatable::default_locale():Translatable::default_lang());
                
                $this->owner->redirect(preg_replace('/^'.preg_quote($language, '/').'\//', $default.'/', $url), 301);
            }
            
            return;
        }
        
        
        //Detect the locale
        if($locale=MultilingualRootURLController::detect_browser_locale()) {
            if(Config::inst()->get('MultilingualRootURLController', 'UseLocaleURL')) {
                $language=$locale;
            }else {
                $language=i18n::get_lang_from_locale($locale);
            }
            
            
            //Set the language cookie
            Cookie::set('language', $language);
            
            
            //Set the various locales
            Translatable::set_current_locale($locale);
            i18n::set_locale($locale);
        }
    }
    
    /**
     * Gets the multilingual link to this controller
     * @return {string} Multilingual link to this controller
     * @see Controller::Link()
     */
    public function MultilingualLink() {
        return Controller::join_links((Config::inst()->get('MultilingualRootURLController', 'UseLocaleURL') ? i18n::get_locale():i18n::get_lang_from_locale(i18n::get_locale())), get_class($this->owner)).'/';
    }
}
?>