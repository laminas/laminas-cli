# FAQ

- I got the following error on installation, how to fix it?

```bash
don't install ocramius/package-versions 1.9.0|don't install composer/package-versions-deprecated 1.10.99
```

You need to require `composer/package-versions-deprecated` followed with `laminas/laminas-cli`:

```bash
$ composer require composer/package-versions-deprecated laminas/laminas-cli
```