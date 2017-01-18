Generic Controllers
=================
Since direct controller extensions are not routed though ModelAsController you need to apply the translation rules manually, this is able to be done via the MultilingualControllerExtension and a custom url rule. By default this is not applied to any controllers, so you must add the extension yourself to your controller. It can be done to all controllers however only the browser detection will work since the default controller urls do not include the language. To apply the extension add the following to your yaml config or use the php configuration.

Via Yaml:
```yml
MyController:
    extensions:
        - 'MultilingualControllerExtension'
```

Via PHP (on the controller):
```php
private static $extensions=array(
                                'MultilingualControllerExtension'
                            );
```

Via PHP (_config.php):
```php
MyController::add_extension('MultilingualControllerExtension');
```

Lastly you need to apply a custom url rule (route) if you want to have the language/locale in the url like pages. As well remember the default Link method of controllers only returns the class name so you will need to override this and add the language/locale to the url or use the MultilingualLink method provided by the extension which works the same as Controller::Link() but adds the correct language/locale to the url.

```yml
Director:
    rules:
        '$Language/my-controller//$Action/$ID/$OtherID': 'MyController' #Option 1
        '$Language/MyController//$Action/$ID/$OtherID': 'MyController' #Option 2
```
