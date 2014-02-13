<?php
class MultilingualModelAsController extends ModelAsController {
    private static $extensions=array('MultilingualOldPageRedirector');
    
    /**
     * @uses ModelAsController::getNestedController()
     * @return SS_HTTPResponse
     */
    public function handleRequest(SS_HTTPRequest $request, DataModel $model) {
        $this->request=$request;
        $this->setDataModel($model);
        
        $this->pushCurrent();
        
        //Get the local from the language param
        if(Config::inst()->get('MultilingualRootURLController', 'UseLocaleURL')) {
            $locale=$request->param('Language');
        }else if(strpos($request->param('Language'), '_')!==false) {
            //Locale not found 404
            if($response=ErrorPage::response_for(404)) {
                return $response;
            }else {
                $this->httpError(404, 'The requested page could not be found.');
            }
            
            return $this->response;
        }else {
            $locale=i18n::get_locale_from_lang($request->param('Language'));
        }
        
        if(in_array($locale, Translatable::get_allowed_locales())) {
            //Set the current locale and remember it
            Cookie::set('language', $request->param('Language'));
            
            Translatable::set_current_locale($locale);
            i18n::set_locale($locale);
        }else {
            //Locale not found 404
            if($response=ErrorPage::response_for(404)) {
                return $response;
            }else {
                $this->httpError(404, 'The requested page could not be found.');
            }
            
            return $this->response;
        }
        
        
        //Handle the home page for the language
        $urlSegment=$request->param('URLSegment');
        if(empty($urlSegment)) {
            $controller=new MultilingualRootURLController();
            
            $response=$controller->handleRequest($request, $model);
            
            $this->popCurrent();
            return $response;
        }
        
        
        //Normal page request so handle that
        $response=parent::handleRequest($request, $model);
        
        $this->popCurrent();
        return $response;
    }
    
    /**
     * Overrides the default getNestedController() to maintain the language restrictions
     * @return ContentController
     */
    public function getNestedController() {
        $request = $this->request;
    
        if(!$URLSegment = $request->param('URLSegment')) {
            throw new Exception('ModelAsController->getNestedController(): was not passed a URLSegment value.');
        }
    
        // Find page by link
        $sitetree = DataObject::get_one(
                'SiteTree',
                sprintf(
                    '"URLSegment" = \'%s\' %s',
                    Convert::raw2sql(rawurlencode($URLSegment)),
                    (SiteTree::nested_urls() ? 'AND "ParentID" = 0' : null)
                )
        );
    
        if(!$sitetree) {
            $response = ErrorPage::response_for(404);
			$this->httpError(404, $response ? $response : 'The requested page could not be found.');
        }
    
        // Enforce current language setting to the loaded SiteTree object
        if(class_exists('Translatable') && $sitetree->Locale) {
            if(Config::inst()->get('MultilingualRootURLController', 'UseLocaleURL')) {
                Cookie::set('language', $sitetree->Locale);
            }else {
                Cookie::set('language', i18n::get_lang_from_locale($sitetree->Locale));
            }
            
            Translatable::set_current_locale($sitetree->Locale);
        }
    
        if(isset($_REQUEST['debug'])) {
            Debug::message("Using record #$sitetree->ID of type $sitetree->class with link {$sitetree->Link()}");
        }
    
        return self::controller_for($sitetree, $this->request->param('Action'));
    }

	/**
	 * @deprecated 3.2 Use MultilingualOldPageRedirector::find_old_page instead
	 *
	 * @param string $URLSegment A subset of the url. i.e in /home/contact/ home and contact are URLSegment.
	 * @param int $parentID The ID of the parent of the page the URLSegment belongs to.
	 * @return SiteTree
	 */
	public static function find_old_page($URLSegment, $parent = null, $ignoreNestedURLs = false) {
		Deprecation::notice('3.2', 'Use MultilingualOldPageRedirector::find_old_page instead');
		if ($parent) {
			$parent = SiteTree::get()->byId($parent);	
		}
		$url = MultilingualOldPageRedirector::find_old_page(array($URLSegment), $parent);
		return SiteTree::get_by_link($url);
	}
}
?>