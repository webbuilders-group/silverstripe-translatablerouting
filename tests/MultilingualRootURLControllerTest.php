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
    private $origDashLocaleEnabled;
    private $origCountryOnly;
    
    protected $autoFollowRedirection=false;
    
    public function setUp() {
        parent::setUp();
        
        
        //Remap translation group for home pages
        Translatable::disable_locale_filter();
        
        $default=$this->objFromFixture('Page', 'home');
        $defaultFR=$this->objFromFixture('Page', 'home_fr');
        $defaultFR->addTranslationGroup($default->ID, true);
        
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
     * Tests to ensure that the site redirects to the default language root url when the user hits the site root
     */
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
    
    /**
     * Tests to ensure that the site redirects to the french language root url when the user hits the site root and the users accept language is french first
     */
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
    
    /**
     * Tests to ensure that the site redirects to the default locale root url when the user hits the site root and the MultilingualRootURLController.UseLocaleURL is set to true
     */
    public function testEnglishLocaleRootRouting() {
        //Enable locale urls
        MultilingualRootURLController::config()->UseLocaleURL=true;
        
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
    
    /**
     * Tests to ensure that the site redirects to the french locale root url when the user hits the site root and the users accept language is french first with the MultilingualRootURLController.UseLocaleURL is set to true
     */
    public function testFrenchLocaleRootRouting() {
        //Enable locale urls
        MultilingualRootURLController::config()->UseLocaleURL=true;
        
        //Set accept language to french
        $_SERVER['HTTP_ACCEPT_LANGUAGE']='fr-FR,fr;q=0.5';
        
        //Get the root url
        $response=$this->get('');
        
        //Check the status make sure its a 301
        $this->assertEquals(301, $response->getStatusCode());
        
        //Check location make sure its {siteroot}/fr_CA/
        $this->assertEquals(
                            Controller::join_links(Director::baseURL().'fr_CA/'),
                            $response->getHeader('Location')
                        );
    }
    
    /**
     * Tests to ensure that the site redirects to the default locale root url when the user hits the site root and the MultilingualRootURLController.UseLocaleURL is set to true and MultilingualRootURLController.UseDashLocale is set to true
     */
    public function testEnglishDashLocaleRootRouting() {
        //Enable locale urls
        MultilingualRootURLController::config()->UseLocaleURL=true;
        MultilingualRootURLController::config()->UseDashLocale=true;
        
        //Get the root url
        $response=$this->get('');
        
        //Check the status make sure its a 301
        $this->assertEquals(301, $response->getStatusCode());
        
        //Check location make sure its {siteroot}/en_US/
        $this->assertEquals(
                            Controller::join_links(Director::baseURL().'en-us/'),
                            $response->getHeader('Location')
                        );
        
        
        
        //Get the root url with the incorrect case
        $response=$this->get('en-US/');
        
        //Check the status make sure its a 404
        $this->assertEquals(404, $response->getStatusCode());
    }
    
    /**
     * Tests to ensure that the site redirects to the french locale root url when the user hits the site root and the users accept language is french first with the MultilingualRootURLController.UseLocaleURL is set to true and MultilingualRootURLController.UseDashLocale is set to true
     */
    public function testFrenchDashLocaleRootRouting() {
        //Enable locale urls
        MultilingualRootURLController::config()->UseLocaleURL=true;
        MultilingualRootURLController::config()->UseDashLocale=true;
        
        //Set accept language to french
        $_SERVER['HTTP_ACCEPT_LANGUAGE']='fr-FR,fr;q=0.5';
        
        //Get the root url
        $response=$this->get('');
        
        //Check the status make sure its a 301
        $this->assertEquals(301, $response->getStatusCode());
        
        //Check location make sure its {siteroot}/fr-ca/
        $this->assertEquals(
                            Controller::join_links(Director::baseURL().'fr-ca/'),
                            $response->getHeader('Location')
                        );
        
        
        
        //Get the root url with the incorrect case
        $response=$this->get('fr-FR/');
        
        //Check the status make sure its a 404
        $this->assertEquals(404, $response->getStatusCode());
    }
    
    /**
     * Verifies the correct home page is detected
     */
    public function testEnglishGetHomepageLink() {
        $this->assertEquals('home', MultilingualRootURLController::get_homepage_link());
    }
    
    /**
     * Verifies the correct home page is detected for the french locale
     */
    public function testFrenchGetHomepageLink() {
        //Set accept language to french
        $_SERVER['HTTP_ACCEPT_LANGUAGE']='fr-FR,fr;q=0.5';
        Translatable::set_default_locale('fr_CA');
        Translatable::set_current_locale('fr_CA');
        i18n::set_locale('fr_CA');
        
        $this->assertEquals('maison', MultilingualRootURLController::get_homepage_link());
    }
    
    public function testGetCountryHomePage() {
        //Enable country urls
        MultilingualRootURLController::config()->use_country_only=true;
        
        //Get the root url
        $response=$this->get('');
        
        //Check the status make sure its a 301
        $this->assertEquals(301, $response->getStatusCode());
        
        //Check location make sure its {siteroot}/us/
        $this->assertEquals(
                            Controller::join_links(Director::baseURL().'us/'),
                            $response->getHeader('Location')
                        );
        
        
        
        //Get the root url with the incorrect case
        $response=$this->get('US/');
        
        //Check the status make sure its a 404
        $this->assertEquals(404, $response->getStatusCode());
    }
    
    public function testGetCanadaHomePage() {
        //Enable country urls
        MultilingualRootURLController::config()->use_country_only=true;
        
        //Set accept language to french
        $_SERVER['HTTP_ACCEPT_LANGUAGE']='fr-FR,fr;q=0.5';
        
        //Get the root url
        $response=$this->get('');
        
        //Check the status make sure its a 301
        $this->assertEquals(301, $response->getStatusCode());
        
        //Check location make sure its {siteroot}/ca/
        $this->assertEquals(
                            Controller::join_links(Director::baseURL().'ca/'),
                            $response->getHeader('Location')
                        );
    }
}
?>