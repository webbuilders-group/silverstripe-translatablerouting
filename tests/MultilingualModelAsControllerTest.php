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
        $pagesFR=Page::get()->filter('Locale', 'fr_FR');
        foreach($pagesFR as $index=>$page) {
            $page->addTranslationGroup($pages->offsetGet($index)->ID, true);
            $page->publish('Stage', 'Live');
        }
        
        Translatable::enable_locale_filter();
        
        
        $this->origLocaleRoutingEnabled=MultilingualRootURLController::config()->UseLocaleURL;
        MultilingualRootURLController::config()->UseLocaleURL=false;
        
        $this->origDashLocaleEnabled=MultilingualRootURLController::config()->UseDashLocale;
        MultilingualRootURLController::config()->UseDashLocale=false;
        
        $this->origAcceptLanguage=$_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $_SERVER['HTTP_ACCEPT_LANGUAGE']='en-US,en;q=0.5';
        
        $this->origCookieLocale=Cookie::get('language');
        Cookie::force_expiry('language');
        Cookie::set('language', 'en');
        
        $this->origCurrentLocale=Translatable::get_current_locale();
        Translatable::set_current_locale('en_US');
        
        $this->origLocale=Translatable::default_locale();
        Translatable::set_default_locale('en_US');
        
        $this->origi18nLocale=i18n::get_locale();
        i18n::set_locale('en_US');
        
        $this->origAllowedLocales=Translatable::get_allowed_locales();
        Translatable::set_allowed_locales(array('en_US', 'fr_FR'));
        
        MultilingualRootURLController::reset();
    }
    
    public function tearDown() {
        MultilingualRootURLController::config()->UseLocaleURL=$this->origLocaleRoutingEnabled;
        MultilingualRootURLController::config()->UseDashLocale=$this->origDashLocaleEnabled;
        
        Translatable::set_current_locale($this->origCurrentLocale);
        Translatable::set_default_locale($this->origLocale);
        Translatable::set_allowed_locales($this->origAllowedLocales);
        
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
        Translatable::set_default_locale('fr_FR');
        Translatable::set_current_locale('fr_FR');
        i18n::set_locale('fr_FR');
        
        $default=$this->objFromFixture('Page', 'home');
        $defaultFR=$this->objFromFixture('Page', 'home_fr');
        
        $this->assertEquals(false, MultilingualRootURLController::should_be_on_root($default));
        $this->assertEquals(true, MultilingualRootURLController::should_be_on_root($defaultFR));
    }
}
?>