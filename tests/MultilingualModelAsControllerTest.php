<?php
class MultilingualModelAsControllerTest extends FunctionalTest {
    public static $fixture_file='MultilingualTest.yml';
    
    private $origLocale;
    private $origCurrentLocale;
    private $origAllowedLocales;
    private $origi18nLocale;
    private $origCookieLocale;
    private $origAcceptLanguage;
    private $origLocaleRoutingEnabled;
    private $origDashLocaleEnabled;
    private $origCountryOnly;
    private $origFRSubtag;
    
    protected $autoFollowRedirection=false;
    
    public function setUp() {
        parent::setUp();
        
        
        Translatable::disable_locale_filter();
        
        //Publish all english pages
        $pages=Page::get()->filter('Locale', 'en_US');
        foreach($pages as $page) {
            $page->publish('Stage', 'Live');
        }
        
        //Rewrite the french translation groups and publish french pages
        $pagesFR=Page::get()->filter('Locale', 'fr_CA');
        foreach($pagesFR as $index=>$page) {
            $page->addTranslationGroup($pages->offsetGet($index)->ID, true);
            $page->publish('Stage', 'Live');
        }
        
        Translatable::enable_locale_filter();
        
        
        $this->origCountryOnly=MultilingualRootURLController::config()->use_country_only;
        MultilingualRootURLController::config()->use_country_only=false;
        
        $this->origLocaleRoutingEnabled=MultilingualRootURLController::config()->UseLocaleURL;
        MultilingualRootURLController::config()->UseLocaleURL=false;
        
        $this->origDashLocaleEnabled=MultilingualRootURLController::config()->UseDashLocale;
        MultilingualRootURLController::config()->UseDashLocale=false;
        
        $this->origAcceptLanguage=$_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $_SERVER['HTTP_ACCEPT_LANGUAGE']='en-US,en;q=0.5';
        
        $this->origCookieLocale=Cookie::get('language');
        Cookie::force_expiry('language');
        Cookie::set('language', '');
        
        $this->origCurrentLocale=Translatable::get_current_locale();
        Translatable::set_current_locale('en_US');
        
        $this->origLocale=Translatable::default_locale();
        Translatable::set_default_locale('en_US');
        
        $this->origi18nLocale=i18n::get_locale();
        i18n::set_locale('en_US');
        
        //Workaround for setting the likely sub-tag of fr to fr_CA
        $this->origFRSubtag=i18n::config()->likely_subtags['fr'];
        Config::inst()->update('i18n', 'likely_subtags', array('fr'=>'fr_CA'));
        
        $this->origAllowedLocales=Translatable::get_allowed_locales();
        Translatable::set_allowed_locales(array('en_US', 'fr_CA'));
        
        MultilingualRootURLController::reset();
    }
    
    public function tearDown() {
        MultilingualRootURLController::config()->use_country_only=$this->origCountryOnly;
        MultilingualRootURLController::config()->UseLocaleURL=$this->origLocaleRoutingEnabled;
        MultilingualRootURLController::config()->UseDashLocale=$this->origDashLocaleEnabled;
        
        Translatable::set_current_locale($this->origCurrentLocale);
        Translatable::set_default_locale($this->origLocale);
        Translatable::set_allowed_locales($this->origAllowedLocales);
        
        Config::inst()->update('i18n', 'likely_subtags', array('fr'=>$this->origFRSubtag));
        
        i18n::set_locale($this->origi18nLocale);
        
        Cookie::force_expiry('language');
        
        if($this->origCookieLocale) {
            Cookie::set('language', $this->origCookieLocale);
        }
        
        $_SERVER['HTTP_ACCEPT_LANGUAGE']=$this->origAcceptLanguage;
        
        MultilingualRootURLController::reset();
        
        parent::tearDown();
    }
    
    /**
     * Verifies that the language/locale is required on the url
     */
    public function testMultilingualRequired() {
        $page=$this->objFromFixture('Page', 'page1');
        
        $response=$this->get($page->URLSegment);
        $this->assertEquals(404, $response->getStatusCode());
        
        $response=$this->get(Director::makeRelative($page->Link()));
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    /**
     * Tests to ensure that loading a page not on the current language returns a page not found when accessing it via the wrong url
     */
    public function testCrossLangNotFound() {
        $page=$this->objFromFixture('Page', 'page1_fr');
        
        $response=$this->get('en/'.$page->URLSegment.'/');
        $this->assertEquals(404, $response->getStatusCode());
    }
    
    /**
     * Tests to see if the english home page is the root url and the french home page is not for english browsers
     */
    public function testEnglishShouldBeRoot() {
        $default=$this->objFromFixture('Page', 'home');
        $defaultFR=$this->objFromFixture('Page', 'home_fr');
        
        $this->assertEquals(true, MultilingualRootURLController::should_be_on_root($default));
        $this->assertEquals(false, MultilingualRootURLController::should_be_on_root($defaultFR));
    }
    
    /**
     * Tests to see if the french home page is the root url and the english home page is not for french browsers
     */
    public function testFrenchShouldBeRoot() {
        //Set accept language to french
        $_SERVER['HTTP_ACCEPT_LANGUAGE']='fr-FR,fr;q=0.5';
        Translatable::set_default_locale('fr_CA');
        Translatable::set_current_locale('fr_CA');
        i18n::set_locale('fr_CA');
        
        $default=$this->objFromFixture('Page', 'home');
        $defaultFR=$this->objFromFixture('Page', 'home_fr');
        
        $this->assertEquals(false, MultilingualRootURLController::should_be_on_root($default));
        $this->assertEquals(true, MultilingualRootURLController::should_be_on_root($defaultFR));
    }
    
    public function testEnglishRouting() {
        //Get the top level page
        $page=$this->objFromFixture('Page', 'page1');
        
        $response=$this->get('en/'.$page->URLSegment);
        
        //Check response code
        $this->assertEquals(200, $response->getStatusCode());
        
        
        //Get the nested level page
        $page=$this->objFromFixture('Page', 'nested');
        
        $response=$this->get('en/'.$page->RelativeLink());
        
        //Check response code
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testFrenchRouting() {
        //Get the top level page
        $page=$this->objFromFixture('Page', 'page1_fr');
        
        $response=$this->get('fr/'.$page->URLSegment);
        
        //Check response code
        $this->assertEquals(200, $response->getStatusCode());
        
        //Get the nested level page
        $page=$this->objFromFixture('Page', 'nested_fr');
        
        $response=$this->get('fr/'.$page->RelativeLink());
        
        //Check response code
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testEnglishLocaleRouting() {
        //Enable locale urls
        MultilingualRootURLController::config()->UseLocaleURL=true;
        
        //Get the top level page
        $page=$this->objFromFixture('Page', 'page1');
        
        $response=$this->get('en_US/'.$page->URLSegment);
        
        //Check response code
        $this->assertEquals(200, $response->getStatusCode());
        
        
        //Get the nested level page
        $page=$this->objFromFixture('Page', 'nested');
        
        $response=$this->get('en_US/'.$page->RelativeLink());
        
        //Check response code
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testFrenchLocaleRouting() {
        //Enable locale urls
        MultilingualRootURLController::config()->UseLocaleURL=true;
        
        //Get the top level page
        $page=$this->objFromFixture('Page', 'page1_fr');
        
        $response=$this->get('fr_CA/'.$page->URLSegment);
        
        //Check response code
        $this->assertEquals(200, $response->getStatusCode());
        
        
        //Get the nested level page
        $page=$this->objFromFixture('Page', 'nested_fr');
        
        $response=$this->get('fr_CA/'.$page->RelativeLink());
        
        //Check response code
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testEnglishDashLocaleRouting() {
        //Enable dashed locale urls
        MultilingualRootURLController::config()->UseLocaleURL=true;
        MultilingualRootURLController::config()->UseDashLocale=true;
        
        //Get the top level page
        $page=$this->objFromFixture('Page', 'page1');
        
        $response=$this->get('en-us/'.$page->URLSegment);
        
        //Check response code
        $this->assertEquals(200, $response->getStatusCode());
        
        
        //Get the nested level page
        $page=$this->objFromFixture('Page', 'nested');
        
        $response=$this->get('en-us/'.$page->RelativeLink());
        
        //Check response code
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testFrenchDashLocaleRouting() {
        //Enable dashed locale urls
        MultilingualRootURLController::config()->UseLocaleURL=true;
        MultilingualRootURLController::config()->UseDashLocale=true;
        
        //Get the top level page
        $page=$this->objFromFixture('Page', 'page1_fr');
        
        $response=$this->get('fr-ca/'.$page->URLSegment);
        
        //Check response code
        $this->assertEquals(200, $response->getStatusCode());
        
        
        //Get the nested level page
        $page=$this->objFromFixture('Page', 'nested_fr');
        
        $response=$this->get('fr-ca/'.$page->RelativeLink());
        
        //Check response code
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testCountryRouting() {
        //Enable country only urls
        MultilingualRootURLController::config()->use_country_only=true;
        
        //Get the top level page
        $page=$this->objFromFixture('Page', 'page1');
        
        $response=$this->get('us/'.$page->URLSegment);
        
        //Check response code
        $this->assertEquals(200, $response->getStatusCode());
        
        
        //Get the nested level page
        $page=$this->objFromFixture('Page', 'nested');
        
        $response=$this->get('us/'.$page->RelativeLink());
        
        //Check response code
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testCanadaRouting() {
        //Enable country only urls
        MultilingualRootURLController::config()->use_country_only=true;
        
        //Get the top level page
        $page=$this->objFromFixture('Page', 'page1_fr');
        
        $response=$this->get('ca/'.$page->URLSegment);
        
        //Check response code
        $this->assertEquals(200, $response->getStatusCode());
        
        
        //Get the nested level page
        $page=$this->objFromFixture('Page', 'nested_fr');
        
        $response=$this->get('ca/'.$page->RelativeLink());
        
        //Check response code
        $this->assertEquals(200, $response->getStatusCode());
    }
}
?>