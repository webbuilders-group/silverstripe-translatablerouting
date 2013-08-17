<?php
class MultilingualRootURLControllerTest extends FunctionalTest {
	public static $fixture_file = 'MultilingualTest.yml';
    
	private $origLocale;
    private $origCurrentLocale;
    private $origAllowedLocales;
    private $origi18nLocale;
    private $origCookieLocale;
    private $origAcceptLanguage;
    private $origLocaleRoutingEnabled;
    
    protected $autoFollowRedirection = false;
    
    function setUp() {
		parent::setUp();
        
        
        //Remap translation group for home pages
        Translatable::disable_locale_filter();
        
        $default=$this->objFromFixture('Page', 'home');
        $defaultFR=$this->objFromFixture('Page', 'home_fr');
        $defaultFR->addTranslationGroup($default->ID, true);
        
        Translatable::enable_locale_filter();
        
        
        $this->origLocaleRoutingEnabled=MultilingualRootURLController::get_use_locale_url();
        MultilingualRootURLController::set_use_locale_url(false);
        
        $this->origAcceptLanguage=$_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $_SERVER['HTTP_ACCEPT_LANGUAGE']='en-US,en;q=0.5';
        
        $this->origCookieLocale=Cookie::get('language');
        Cookie::forceExpiry('language');
        
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
	
	function tearDown() {
        MultilingualRootURLController::set_use_locale_url($this->origLocaleRoutingEnabled);
        
		Translatable::set_current_locale($this->origCurrentLocale);
		Translatable::set_default_locale($this->origLocale);
        Translatable::set_allowed_locales($this->origAllowedLocales);
        
        i18n::set_locale($this->origi18nLocale);
        
        Cookie::forceExpiry('language');
        
        if($this->origCookieLocale) {
            Cookie::set('language', $this->origCookieLocale);
        }
        
        $_SERVER['HTTP_ACCEPT_LANGUAGE']=$this->origAcceptLanguage;
        
        MultilingualRootURLController::reset();
        
		parent::tearDown();
	}
    
	public function testEnglishLangRootRouting() {
        //Get the root url
        $response=$this->get('');
        
        //Check the status make sure its a 301
        $this->assertEquals(301, $response->getStatusCode());
        
        //Check location make sure its {siteroot}/en/
        $this->assertEquals(
                            Controller::join_links(Director::baseURL().'en/'),
                            $response->getHeader('Location')
                        );
	}
    
	public function testFrenchLangRootRouting() {
        //Set accept language to french
        $_SERVER['HTTP_ACCEPT_LANGUAGE']='fr-FR,fr;q=0.5';
		
        //Get the root url
        $response=$this->get('');
        
        //Check the status make sure its a 301
        $this->assertEquals(301, $response->getStatusCode());
        
        //Check location make sure its {siteroot}/fr/
        $this->assertEquals(
                            Controller::join_links(Director::baseURL().'fr/'),
                            $response->getHeader('Location')
                        );
	}
    
	public function testEnglishLocaleRootRouting() {
        //Enable locale urls
        MultilingualRootURLController::set_use_locale_url(true);
        
        //Get the root url
        $response=$this->get('');
        
        //Check the status make sure its a 301
        $this->assertEquals(301, $response->getStatusCode());
        
        //Check location make sure its {siteroot}/en_US/
        $this->assertEquals(
                            Controller::join_links(Director::baseURL().'en_US/'),
                            $response->getHeader('Location')
                        );
	}
    
	public function testFrenchLocaleRootRouting() {
        //Enable locale urls
        MultilingualRootURLController::set_use_locale_url(true);
        
        //Set accept language to french
        $_SERVER['HTTP_ACCEPT_LANGUAGE']='fr-FR,fr;q=0.5';
		
        //Get the root url
        $response=$this->get('');
        
        //Check the status make sure its a 301
        $this->assertEquals(301, $response->getStatusCode());
        
        //Check location make sure its {siteroot}/fr_FR/
        $this->assertEquals(
                            Controller::join_links(Director::baseURL().'fr_FR/'),
                            $response->getHeader('Location')
                        );
	}
    
    public function testEnglishGetHomepageLink() {
        $this->assertEquals('home', MultilingualRootURLController::get_homepage_link());
    }
    
    public function testFrenchGetHomepageLink() {
        //Set accept language to french
        $_SERVER['HTTP_ACCEPT_LANGUAGE']='fr-FR,fr;q=0.5';
		Translatable::set_default_locale('fr_FR');
        Translatable::set_current_locale('fr_FR');
        i18n::set_locale('fr_FR');
        
        $this->assertEquals('maison', MultilingualRootURLController::get_homepage_link());
    }
}
?>