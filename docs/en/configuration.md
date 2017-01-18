Configuration
=================
## Locale URLs
If you want to use locale's (for example en_US) as your url pattern instead of just languages you need to add this to your ``mysite/_config/config.yml``, make sure you set the rule to be after ``translatablerouting/*``.

```yml
MultilingualRootURLController:
    UseLocaleURL: true
```

Using this option will generate a url that looks like ``http://example.com/en_US`` for example.


#### Dashed Locale URLs
You may also choose to use a dashed locale instead of the underscored locale (i.e en-us instead of en_US). As with the above you need to add this to your ``mysite/_config/config.yml``, making sure you set the rule to be after ``translatablerouting/*``.

```yml
MultilingualRootURLController:
    UseLocaleURL: true
    UseDashLocale: true
```

Using this option will generate a url that looks like ``http://example.com/en-us/`` for example instead of what you would see by simply enabling the locale urls.

## Country URLs
If you want just the country code (for example just ``us``) pattern instead of having the locale or country code you need to add this to your ``mysite/_config/config.yml``, making sure you set the rule to be after ``translatablerouting/*``.

```yml
MultilingualRootURLController:
    use_country_only: true
```

There is one big limitation here, you cannot have multiple locales that end in the same country code. For example if you have en_US, fr_US and es_ES as allowed locales it will not work because there are two locales with the US country code. If you have en_US, fr_CA, and es_ES as allowed locales then you will be fine. You could also potentially have en_US, en_CA, and en_UK which is also acceptable since the county codes are unique.
