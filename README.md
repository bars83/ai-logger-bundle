# AiAdminBundle

## 1. Install

### Add to file composer.json

```json
"require": {
    ...
    "ai/logger-bundle": "dev-master"
    ...
},
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/ruslana-net/ai-logger-bundle"
    }
],
```

### Bash command
```sh
cd /path/to/project/
php composer.phar update
```

### Add to file app/AppKernel.php
```php
public function registerBundles()
{
    $bundles = array(
        ...,
        new Ai\AdminBundle\AiAdminBundle(),
        ...
    );
    ...
}
```

## 2. Run
```bash
php app/console doctrine:schema:update --force
php app/console assets:install web
php app/console cache:clear 
```
