<?php
class MultilingualRootURLController extends RootURLController {
    public function handleRequest(SS_HTTPRequest $request, DataModel $model=null) {
        self::$is_at_root=true;
        $this->setDataModel($model);
        
        $this->pushCurrent();
        $this->init();
        
        if($language=$request->param('Language')) {
            $locale=i18n::get_locale_from_lang($language);
            if(in_array($locale, Translatable::get_allowed_locales())) {
                if(Cookie::get('language') && Cookie::get('language')!=$language) {
                    Cookie::set('language', $language);
                }
                
                Translatable::set_current_locale($locale);
                
                
                if(!DB::isActive() || !ClassInfo::hasTable('SiteTree')) {
                    $this->response=new SS_HTTPResponse();
                    $this->response->redirect(Director::absoluteBaseURL().'dev/build?returnURL='.(isset($_GET['url']) ? urlencode($_GET['url']):null));
                    return $this->response;
                }
            	
                $request=new SS_HTTPRequest($request->httpMethod(), $language.'/'.self::get_homepage_link().'/', $request->getVars(), $request->postVars());
                $request->match('$Language/$URLSegment//$Action', true);
                
                
                $controller=new MultilingualModelAsController();
                $result=$controller->handleRequest($request, $model);
                
                $this->popCurrent();
                return $result;
            }else {
                //URL Param Locale is not allowed so redirect to default
                $this->redirect(Controller::join_links(Director::baseURL(), Translatable::default_lang()).'/');
                
                $this->popCurrent();
                return $this->response;
            }
        }
        
        
        //No Locale Param so detect browser language and redirect
        if($locale=self::detect_browser_locale()) {
            $language=i18n::get_lang_from_locale($locale);
            Cookie::set('language', $language);
            
            $this->redirect(Controller::join_links(Director::baseURL(), $language).'/');
            
            $this->popCurrent();
            return $this->response;
        }
        
        
        $this->redirect(Controller::join_links(Director::baseURL(), Translatable::default_lang()).'/');
        
        $this->popCurrent();
        return $this->response;
    }
    
    /**
     * Determines the locale best matching the given list of browser locales
     * @return {string} The matching locale, or null if none could be determined
     */
    public static function detect_browser_locale() {
        if($language=Cookie::get('language')) {
            $locale=i18n::get_locale_from_lang($language);
            if(in_array($locale, Translatable::get_allowed_locales())) {
                return $locale;
            }else {
                Cookie::clear('language');
            }
        }
        
        // Given multiple canditates, narrow down the final result using the client's preferred languages
        $inputLocales=$_SERVER['HTTP_ACCEPT_LANGUAGE'];
        if(empty($inputLocales)) {
            return null;
        }
    
        // Generate mapping of priority => list of languages at this priority
        // break up string into pieces (languages and q factors)
        preg_match_all('/(?<code>[a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(?<priority>1|0\.[0-9]+))?/i', $inputLocales, $parsedLocales);
    
        $prioritisedLocales=array();
        if(count($parsedLocales['code'])) {
            // create a list like "en" => 0.8
            $parsedLocales=array_combine($parsedLocales['code'], $parsedLocales['priority']);
    
            // Generate nested list of priorities => [languages]
            foreach ($parsedLocales as $language => $priority) {
                $priority=(empty($priority) ? 1.0:floatval($priority));
                if(empty($prioritisedLocales[$priority])) {
                    $prioritisedLocales[$priority] = array();
                }
                
                $prioritisedLocales[$priority][]=$language;
            }
            	
            // sort list based on value
            krsort($prioritisedLocales, SORT_NUMERIC);
        }
    
        // Check each requested language against loaded languages
        foreach ($prioritisedLocales as $priority=>$parsedLocales) {
            foreach($parsedLocales as $browserLocale) {
                foreach(Translatable::get_allowed_locales() as $language) {
                    if(stripos(preg_replace('/_/', '-', $language), $browserLocale)===0) {
                        return $language;
                    }
                }
            }
        }
    
        return null;
    }
    
    
    
    /**
     * Returns TRUE if a request to a certain page should be redirected to the site root (i.e. if the page acts as the home page).
     * @param {SiteTree} $page
     * @return {bool}
     */
    public static function should_be_on_root(SiteTree $page) {
        if(!self::$is_at_root && self::get_homepage_link()==trim($page->RelativeLink(true), '/')) {
            return true;
        }
        
        return false;
    }
}
?>