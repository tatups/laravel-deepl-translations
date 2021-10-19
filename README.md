# Translation generation package

Script for autogenerating missing translations from the files of a defined source language to the other languages through the DeepL api
## Installation

Register the package repository:

```
"repositories":[
        {
        "type": "git",
        "url": "https://bitbucket.org/blacklabelbytes/laravel-deepl-translations.git"
    }
]
```

Require the package:

```
"require-dev":[
    "blacklabelbytes/laravel-deepl-translations": "^1.0"
]
```

Then Publish the api config
```
php artisan vendor:publish --provider="BlackLabelBytes\Translations\DeepLTranslationServiceProvider"
```


## Usage 

- Input your api key and other options to the published config file deepl-translations.php
- Run the ```php artisan deepl-translate``` command

