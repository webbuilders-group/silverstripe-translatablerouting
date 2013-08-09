<?php
class MultilingualGoogleSitemap extends GoogleSitemap {
	/**
	 * Returns a list containing each viewable {@link SiteTree} instance. If you wish to exclude a particular class from the sitemap, simply set the priority of the class to -1.
	 * @return {ArrayList}
	 */
	protected function getPages() {
		if(!class_exists('SiteTree')) {
		    return new ArrayList();
		}
		
        
		//Disable the locale filter
	    Translatable::disable_locale_filter();
	    
		
		$filter=(self::$use_show_in_search ? '"ShowInSearch"=1':'');
		$pages=Versioned::get_by_stage('SiteTree', 'Live', $filter);
		$output=new ArrayList();
		
		if($pages) {
			foreach($pages as $page) {
				$pageHttp=parse_url($page->AbsoluteLink(), PHP_URL_HOST);
				$hostHttp=parse_url('http://'.$_SERVER['HTTP_HOST'], PHP_URL_HOST);
				
				if(($pageHttp==$hostHttp) && !($page instanceof ErrorPage)) {
					if($page->canView() && (!isset($page->Priority) || $page->Priority>0)) { 
						$output->push($page);
					}
				}
			}
		}
		
        
		//Re-enable the locale filter
	    Translatable::enable_locale_filter();
		
		
		return $output;
	}
}
?>