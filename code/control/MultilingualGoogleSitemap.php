<?php
class MultilingualGoogleSitemapController extends GoogleSitemapController {
    private static $allowed_actions=array(
                                            'sitemap'
                                        );
    
    /**
     * Specific controller action for displaying a particular list of links
     * for a class
     *
     * @return mixed
     */
    public function sitemap() {
        if($this->request->param('ID')=='SiteTree') {
            //Disable the locale filter
    	    Translatable::disable_locale_filter();
            
    	    
            $items=parent::sitemap();
            
            
    		//Re-enable the locale filter
    	    Translatable::enable_locale_filter();
    	    
    	    
    	    return $items;
        }else {
            return parent::sitemap();
        }
    }
}
?>