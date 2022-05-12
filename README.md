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

Normally the api config is derived from .env variables. You can also publish it:
```
php artisan vendor:publish --provider="BlackLabelBytes\Translations\DeepLTranslationServiceProvider"
```


## Usage 

- Run the ```php artisan deepl-translate``` command
- For the first time setup, follow the insctructions given by the command to set your the DeepL configuration


