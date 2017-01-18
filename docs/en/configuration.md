Configuration
=================
## Locale URL's
If you want to use locale's (for example en_US) as your url pattern instead of just languages you need to add this to your ``mysite/_config/config.yml``, make sure you set the rule to be after ``translatablerouting/*``

```yml
MultilingualRootURLController:
    UseLocaleURL: true
```


You may also choose to use a dashed locale instead of the underscored locale (i.e en-us instead of en_US). As with the above you need to add this to your ``mysite/_config/config.yml``, making sure you set the rule to be after ``translatablerouting/*``.

```yml
MultilingualRootURLController:
    UseLocaleURL: true
    UseDashLocale: true
```
