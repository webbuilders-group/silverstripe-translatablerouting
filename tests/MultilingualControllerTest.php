<?php
class MultilingualControllerTest extends FunctionalTest {
    public static $fixture_file='MultilingualTest.yml';
    
    private $origLocale;
    private $origCurrentLocale;
    private $origAllowedLocales;
    private $origi18nLocale;
    private $origCookieLocale;
    private $origAcceptLanguage;
    private $origLocaleRoutingEnabled;
    
    protected $autoFollowRedirection=false;
    
    public function setUp() {
        parent::setUp();
        
        $this->origLocaleRoutingEnabled=MultilingualRootURLController::config()->UseLocaleURL;
        MultilingualRootURLController::config()->UseLocaleURL=false;
        
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
        
        
        MultilingualTestController::add_extension('MultilingualControllerExtension');
        
        Config::inst()->update('Director', 'rules', array(
                                                        '$Language/multilingual-test-controller//$Action/$ID/$OtherID'=>'MultilingualTestController'
                                                    ));
        
        MultilingualRootURLController::reset();
    }
    
    public function tearDown() {
        MultilingualRootURLController::config()->UseLocaleURL=$this->origLocaleRoutingEnabled;
        
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
     * Tests to see if the controller responds correctly if the language is in the url
     */
    public function testLanguageInURL() {
        //Perform Request
        $response=$this->get('fr/multilingual-test-controller/');
        
        
        //Ensure a 200 response
        $this->assertEquals(200, $response->getStatusCode());
        
        
        //Verify the response matches what is expected
        $this->assertEquals('i18n: fr_FR|Translatable: fr_FR', $response->getBody());
    }
    
    /**
     * Tests to see if the controller responds correctly if the locale is in the url
     */
    public function testLocaleInURL() {
        //Enable locale urls
        MultilingualRootURLController::config()->UseLocaleURL=true;
    
        //Set accept language to french
        $_SERVER['HTTP_ACCEPT_LANGUAGE']='fr-FR,fr;q=0.5';
    
        //Get the root url
        $response=$this->get('fr_FR/multilingual-test-controller/');
        
        
        //Ensure a 200 response
        $this->assertEquals(200, $response->getStatusCode());
        
        
        //Verify the response matches what is expected
        $this->assertEquals('i18n: fr_FR|Translatable: fr_FR', $response->getBody());
    }
    
    /**
     * Tests to see if the controller responds correctly if the language is in the url
     */
    public function testAutoDetectLanguage() {
        //Set accept language to french
        $_SERVER['HTTP_ACCEPT_LANGUAGE']='fr-FR,fr;q=0.5';
        Translatable::set_default_locale('fr_FR');
        Translatable::set_current_locale('fr_FR');
        i18n::set_locale('fr_FR');
        
        
        //Perform Request
        $response=$this->get('MultilingualTestController');
        
        
        //Ensure a 200 response
        $this->assertEquals(200, $response->getStatusCode());
        
        
        //Verify the response matches what is expected
        $this->assertEquals('i18n: fr_FR|Translatable: fr_FR', $response->getBody());
    }
    
    /**
     * Tests to see if the controller responds correctly if the language is in the url
     */
    public function testInvalidLanguageURL() {
        //Perform Request
        $response=$this->get('es/multilingual-test-controller/');
        
        
        //Ensure a 301 response
        $this->assertEquals(301, $response->getStatusCode());
        
        
        //Verify the redirect url matches the default
        $this->assertEquals(Director::baseURL().'en/multilingual-test-controller', $response->getHeader('Location'));
    }
}

class MultilingualTestController extends Controller implements TestOnly {
    private static $extensions=array(
                                    'MultilingualControllerExtension'
                                );
    
    public function index() {
        return 'i18n: '.i18n::get_locale().'|Translatable: '.Translatable::get_current_locale();
    }
}
?>